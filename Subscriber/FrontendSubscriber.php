<?php

namespace NdxInstantPage\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs;
use Shopware\Components\Plugin\ConfigReader;

class FrontendSubscriber implements SubscriberInterface
{
    private string $pluginDir;
    private ConfigReader $configReader;

    public function __construct(string $pluginDir, ConfigReader $configReader)
    {
        $this->pluginDir = $pluginDir;
        $this->configReader = $configReader;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatchSecure',
            'Theme_Inheritance_Template_Directories_Collected' => 'onCollectTemplateDir',
        ];
    }

    public function onCollectTemplateDir(\Enlight_Event_EventArgs $args)
    {
        $dirs = $args->getReturn();
        $dirs[] = $this->pluginDir . '/Resources/views';
        $args->setReturn($dirs);
    }

    public function onPostDispatchSecure(Enlight_Controller_ActionEventArgs $args)
    {
        $controller = $args->getSubject();
        $request = $controller->Request();
        $response = $controller->Response();

        if (!$request->isDispatched() || $response->isException()) {
            return;
        }

        $config = $this->configReader->getByPluginName('NdxInstantPage');
        if (!$config['ndxInstantPageActive']) {
            return;
        }

        $version = $config['ndxInstantPageVersion'] ?? '5.2.0';

        if (!$this->ensureScriptFile($version)) {
            return;
        }

        $bodyAttrs = $this->getBodyAttrs($config);
        $selectors = $this->getBlacklistSelectors($config);

        $inlineInit = $this->buildInlineInit($bodyAttrs, $selectors);
        $scriptTag = '<script src="' . $this->scriptUrl($version) . '" type="module"></script>';

        $script = $inlineInit ? $inlineInit . "\n" . $scriptTag : $scriptTag;

        $view = $controller->View();
        $view->addTemplateDir($this->pluginDir . '/Resources/views');
        $view->assign('ndxInstantPageScript', $script);
        $view->extendsTemplate('frontend/plugins/ndx_instant_page/footer.tpl');
    }

    private function getBodyAttrs(array $config): array
    {
        $attrs = [];

        if (!empty($config['ndxInstantPageAllowQueryString'])) {
            $attrs[] = 'data-instant-allow-query-string';
        }
        if (!empty($config['ndxInstantPageWhitelist'])) {
            $attrs[] = 'data-instant-whitelist';
        }
        if (!empty($config['ndxInstantPageAllowExternalLinks'])) {
            $attrs[] = 'data-instant-allow-external-links';
        }
        if (!empty($config['ndxInstantPageVaryAccept'])) {
            $attrs[] = 'data-instant-vary-accept';
        }

        return $attrs;
    }

    private function getBlacklistSelectors(array $config): array
    {
        if (empty($config['ndxInstantPageBlacklistSelectors'])) {
            return [];
        }

        $selectors = explode(',', $config['ndxInstantPageBlacklistSelectors']);
        $selectors = array_map('trim', $selectors);
        $selectors = array_filter($selectors, fn($v) => $v !== '');

        return array_values($selectors);
    }

    private function buildInlineInit(array $bodyAttrs, array $selectors): string
    {
        if (empty($bodyAttrs) && empty($selectors)) {
            return '';
        }

        $parts = [];

        foreach ($bodyAttrs as $attr) {
            $parts[] = 'document.body.setAttribute("' . $attr . '","")';
        }

        if (!empty($selectors)) {
            $encoded = array_map('json_encode', $selectors);
            $parts[] = '[' . implode(',', $encoded) . '].forEach(function(s){document.querySelectorAll(s).forEach(function(e){e.setAttribute("data-no-instant","")})})';
        }

        return '<script>' . implode(';', $parts) . ';</script>';
    }

    private function scriptUrl(string $version): string
    {
        $docPath = Shopware()->Container()->getParameter('shopware.app.rootDir');
        $path = $this->scriptFilePath($version);
        if (str_starts_with($path, $docPath)) {
            $path = substr($path, strlen($docPath));
        }
        return Shopware()->Front()->Request()->getBasePath() . '/' . ltrim($path, '/');
    }

    private function scriptFilePath(string $version): string
    {
        return $this->pluginDir . '/Resources/views/frontend/_public/src/js/instantpage-' . $version . '.min.js';
    }

    private function ensureScriptFile(string $version): bool
    {
        $path = $this->scriptFilePath($version);

        if (file_exists($path)) {
            return true;
        }

        if (!preg_match('/^\d+\.\d+(\.\d+)?$/', $version)) {
            error_log('NdxInstantPage: Ungültiges Versionsformat: ' . $version);
            return false;
        }

        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            error_log('NdxInstantPage: Verzeichnis konnte nicht erstellt werden: ' . $dir);
            return false;
        }

        if (!is_writable($dir)) {
            error_log('NdxInstantPage: Verzeichnis nicht beschreibbar: ' . $dir);
            return false;
        }

        $url = 'https://instant.page/' . $version;
        $content = null;

        if (ini_get('allow_url_fopen')) {
            $ctx = stream_context_create([
                'http' => ['timeout' => 15, 'user_agent' => 'NdxInstantPage'],
                'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
            ]);
            $content = @file_get_contents($url, false, $ctx);
        }

        if ($content === null && function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_USERAGENT => 'NdxInstantPage',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode !== 200) {
                $content = null;
            }
        }

        if ($content === null) {
            error_log('NdxInstantPage: Konnte Skript nicht herunterladen von ' . $url);
            return false;
        }

        $written = @file_put_contents($path, $content);
        if ($written === false) {
            error_log('NdxInstantPage: Konnte Skript nicht schreiben: ' . $path);
            return false;
        }

        return true;
    }
}
