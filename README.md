# instant.page Integration für Shopware 5

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
![Shopware 5](https://img.shields.io/badge/Shopware-5.0–5.99-green)
![instant.page](https://img.shields.io/badge/instant.page-5.2.0-blue)

---

## 🇩🇪 Deutsch

Ein Shopware 5 Plugin, das [instant.page](https://instant.page/) integriert – eine Bibliothek, die Seiten bereits beim Überfahren eines Links mit der Maus vorlädt und die Navigation dadurch gefühlt sofort macht.

### Funktionen

- **Null Konfiguration** – läuft sofort nach der Installation
- **Voller Funktionsumfang** – nutzt den vollständigen instant.page-Build mit allen nativen Features
- **Konfigurierbare Blacklist** – Links per CSS-Selektor ausschließen (z.B. `a[href*="logout"]`, `a[href*="checkout"]`)
- **Auto-Download** – wird eine neuere instant.page-Version in den Einstellungen eingetragen, wird sie automatisch heruntergeladen und gecached
- **Multishop-fähig** – alle Einstellungen sind shop-spezifisch

### Installation

**Via ZIP-Upload:**

1. [Latest Release](https://github.com/ndxmedia/NdxInstantPage/releases/latest) herunterladen
2. Im Shopware-Backend unter **Konfiguration → Plugin-Manager**
3. Auf **Plugin hinzufügen** klicken und die ZIP-Datei hochladen
4. Plugin installieren und aktivieren

**Manuelle Installation:**

```bash
cp -r NdxInstantPage custom/plugins/
```

Danach über den Plugin-Manager oder die CLI installieren und aktivieren:

```bash
bin/console sw:plugin:install NdxInstantPage
bin/console sw:plugin:activate NdxInstantPage
```

### Konfiguration

Unter **Konfiguration → Plugin-Manager → instant.page Integration**

| Einstellung | Standard | Beschreibung |
|-------------|----------|-------------|
| Aktiv | `true` | instant.page ein-/ausschalten |
| Version | `5.2.0` | instant.page-Version – wird bei Bedarf automatisch heruntergeladen |
| URLs mit Query-Parametern vorladen | `false` | Erlaubt das Vorladen von URLs mit `?` |
| Blacklist CSS-Selektoren | `a[href*="logout"], a[href*="checkout"]` | Kommagetrennte CSS-Selektoren für ausgeschlossene Links |
| Whitelist-Modus | `false` | Nur Links mit `data-instant`-Attribut vorladen |
| Externe Links vorladen | `false` | Erlaubt das Vorladen von Links auf andere Domains |
| Vary Accept Workaround | `false` | Aktivieren für Chromium < 110 bei `Vary: Accept`-Headern |

### Blacklist-Beispiele

```css
/* Logout-Links ausschließen */
a[href*="logout"]

/* Checkout-Links ausschließen */
a[href*="checkout"]

/* Löschen-Aktionen ausschließen */
a[href*="delete"]

/* Submit-Buttons ausschließen */
button[type="submit"]

/* Mehrere Selektoren kombinieren */
a[href*="logout"], a[href*="checkout"], a[href*="delete"]
```

### Funktionsweise

Das Plugin fügt ein kleines Inline-Script im Footer der Seite ein, das:

1. Body-Attribute setzt (`data-instant-allow-query-string`, `data-instant-whitelist` usw.) basierend auf der Konfiguration
2. `data-no-instant` an alle Links anfügt, die auf die Blacklist-Selektoren passen

Anschließend lädt die vollständige instant.page-Bibliothek und kümmert sich um das Preloading – unter Berücksichtigung aller gesetzten Attribute.

---

## 🇬🇧 English

A Shopware 5 plugin that integrates [instant.page](https://instant.page/) – a library that preloads pages when a user hovers over a link, making subsequent page loads feel instant.

### Features

- **Zero configuration** – works out of the box
- **Full instant.page build** – supports all native features (query string exclusion, whitelist mode, external link control, Vary Accept workaround)
- **Configurable blacklist** – exclude links by CSS selector (e.g. `a[href*="logout"]`, `a[href*="checkout"]`)
- **Auto-download** – entering a newer instant.page version in the settings automatically downloads and caches it
- **Multi-shop ready** – all settings are shop-specific

### Installation

**Via ZIP upload:**

1. Download the [latest release](https://github.com/ndxmedia/NdxInstantPage/releases/latest)
2. In the Shopware backend, go to **Configuration → Plugin Manager**
3. Click **Add Plugin** and upload the ZIP file
4. Install and activate the plugin

**Manual installation:**

```bash
cp -r NdxInstantPage custom/plugins/
```

Then install and activate via Plugin Manager or CLI:

```bash
bin/console sw:plugin:install NdxInstantPage
bin/console sw:plugin:activate NdxInstantPage
```

### Configuration

Navigate to **Configuration → Plugin Manager → instant.page Integration**

| Setting | Default | Description |
|---------|---------|-------------|
| Active | `true` | Enable/disable instant.page |
| Version | `5.2.0` | instant.page version – auto-downloads if not cached |
| Preload URLs with query string | `false` | Allow preloading of URLs containing `?` |
| Blacklist CSS selectors | `a[href*="logout"], a[href*="checkout"]` | Comma-separated CSS selectors excluded from preloading |
| Whitelist mode | `false` | Only preload links with `data-instant` attribute |
| Preload external links | `false` | Allow preloading of cross-origin links |
| Vary Accept workaround | `false` | Enable for Chromium < 110 with `Vary: Accept` headers |

### Blacklist examples

```css
/* Exclude logout links */
a[href*="logout"]

/* Exclude checkout links */
a[href*="checkout"]

/* Exclude delete actions */
a[href*="delete"]

/* Exclude form submit buttons */
button[type="submit"]

/* Combine multiple selectors */
a[href*="logout"], a[href*="checkout"], a[href*="delete"]
```

### How it works

The plugin injects a small inline script into the page footer that:

1. Sets body attributes (`data-instant-allow-query-string`, `data-instant-whitelist`, etc.) based on your configuration
2. Adds `data-no-instant` to every link matching your blacklist selectors

Then the full instant.page library loads and handles the preloading, respecting all attributes.

---

## Changelog

### 1.2.0
- Blacklist-Konfiguration mit CSS-Selektor-Unterstützung hinzugefügt
- Einstellungen für Query-Strings, Whitelist, externe Links und Vary Accept hinzugefügt
- Wechsel von Minimal- auf Full-Build von instant.page
- Auto-Download für neue instant.page-Versionen

### 1.0.0
- Erstveröffentlichung mit grundlegender instant.page-Integration

## License / Lizenz

MIT License – see [LICENSE](LICENSE)

## Author

[NDX Media](https://ndxmedia.de)
