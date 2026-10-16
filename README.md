# WHMCS Provisioning Module for Streaming.Center Internet-Radio Control panel

This is an **open-source WHMCS provisioning module** that integrates with the commercial, closed-source software [**Streaming.Center**](https://streaming.center).

It allows WHMCS to automatically provision, manage, and terminate services within your Streaming.Center platform.

---

## 🚀 Features

- Automated account provisioning
- Service management (suspend, unsuspend, terminate)
- Configurable module settings
- Integration with WHMCS billing cycles
- Supports latest WHMCS versions
- Custom packages through WHMCS configurable options (also on top of templates)
- Station name and username taken from the order form
- Client area overview in English, Czech and Russian
- Welcome email templates with stream URLs and plan details
- Disk and traffic usage sync into WHMCS

---

## 📦 Requirements

- WHMCS version 8+
- PHP 7+
- Valid Streaming.center license
- Admin credentials for Streaming.Center admin area

---

## 🔧 Installation

1. **Download** or clone this repository:
   ```bash
   git clone https://github.com/yourusername/whmcs-yoursoftware-module.git
   ```
2. Upload the module folder to:
   ```
   /modules/servers/
   ```
   so that the final path to the module would be:
   ```
   /modules/servers/streamingcenter
   ```

3. **Configure** the module in WHMCS Admin:
- Go to **System Settings > Products/Services > Servers**  
- Add a new server using the module: "Streaming.Center"
- Enter your Streaming.Center admin credentials (it is available after Streaming.Center installation)

4. **Assign** the module to your WHMCS products.

---

## ⚙️ Configuration

The module settings allow you to:
- Define admin credentials  
- Set default provisioning parameters  
- Map WHMCS products to Streaming.Center plans by choosing broadcaster templates that you create in Streaming.Center admin area or setting individual options for each radio account.  

For detailed setup instructions, refer to the [documentation](https://streaming.center/docs/system/whmcs/)

### Social streaming limit (upgrading)

The four per-platform options — *Allow Youtube / Facebook / VK.com / Telegram
streaming* — are replaced by one option, **Social streams limit**: how many streams
to YouTube, Telegram and other platforms an account may have across all its
stations. `0` turns social streaming off; leaving it blank uses the template's value
(or, with no template, off), and on a package change leaves the account's current
value alone.

The old options are kept in the product's module settings, marked *(Deprecated)*
and moved out of Simple Mode, because WHMCS stores module options by position:
removing them would shift every option after them. Existing accounts keep the limit
the server gave them on upgrade until you set a value.

They are still **sent**, with the values they always had, so this module works with
Streaming.Center servers of either version — upgrade the module and the servers in
any order:

- a server that predates the limit uses them exactly as before (it rejects a package
  change that leaves them out);
- a current server stores them and acts only on *Social streams limit* — except when
  an account is created without a template and the limit is blank, where any of the
  four switched on means a limit of 1.

The admin services tab no longer shows them; it carries the account's current values
through a save unchanged.

### HLS

*Allow HLS streaming* applies to products **without** a template. With a template, the
template's HLS setting decides — the option is disabled on the product screen when a
template is selected, and the module sends no override.

### Custom packages (configurable options)

Customers can build their own package on the order form. On the product's **Module Settings**
tab click **Create configurable options (custom packages)**: the module creates a configurable
option group for the product (all prices 0) that you then adjust under
**System Settings > Configurable Options**. You can also create the options by hand: the option
name (part before `|`) selects the account field and the selected value (part before `|`) is used.

| Option name | Type | Example choices | Account field |
|---|---|---|---|
| `server_type\|Streaming server` | Dropdown | `icecast-kh\|Icecast-KH`, `icecast\|Icecast`, `shoutcast2\|Shoutcast 2`, `shoutcast2.6\|Shoutcast 2.6` | server_type |
| `listeners\|Listeners` | Dropdown | `100\|100 listeners`, `0\|Unlimited` | limit_listeners |
| `bitrate\|Max. bitrate` | Dropdown | `64\|64 kbps`, `128\|128 kbps`, `320\|320 kbps` | limit_bitrate |
| `storage\|Disk space` | Dropdown / Quantity | `5 GB\|5 GB`, `20 GB\|20 GB` | limit_du |
| `traffic\|Monthly traffic` | Dropdown | `500 GB\|500 GB`, `1 TB\|1 TB`, `0\|Unlimited` | limit_traffic |
| `stations\|Radios` | Dropdown / Quantity | `1`, `2`, `5` | limit_stations |
| `channels\|Channels` | Dropdown / Quantity | `1`, `2`, `3` | limit_streams |
| `social\|Social streams` | Dropdown / Quantity | `0\|None`, `1\|1 stream` | limit_social_streams |
| `podcasts`, `recordings`, `hls`, `website`, `url_branding`, `geoblocking`, `stereotool`, `tts` | Yes/No | – | the matching `*_enabled` switch |

- `0` means unlimited. For disk and traffic a bare number is GB (`20` = 20 GB); `500 MB`, `5 GB`, `1 TB` also work.
- Bitrates are rounded down to a value Streaming.Center supports (24, 32, 64, 96, 128, 192, 256, 320).
- With a template the account is created from the template and the customer's choices are applied right after;
  without a template they are sent in the create request. Upgrades/downgrades are applied on Change Package.
- Options a product does not have keep the product settings / template values.

### Station name and username

Add a product custom field named **Station name** (or *Radio name*; any field whose name contains
"radio" or "station" works, also in Czech and Russian) and tick *Show on Order Form*. The value becomes
the station name in Streaming.Center and the base of the generated username. Without it the username is
generated as before (company name, then client name).

### Client area

The service Overview tab shows on-air status, listeners, now playing (with a play button), stream URLs
with copy buttons, live broadcasting instructions, listener/disk/traffic usage bars, the plan and its
features, and a button to open the broadcaster interface. Streams and "now playing" come from the
broadcaster panel's public API (`/api/v2/channels/`, `/api/v2/history/`). Data is cached for 45 seconds.

Texts are in `streamingcenter/lang/`: `english.php` (default), `czech.php` (also Slovak) and
`russian.php` (also Ukrainian). To add a language, copy `english.php` to `lang/<WHMCS language name>.php`
and translate it; missing keys fall back to English.

### Welcome email

`streamingcenter/email/` has welcome templates in English, Czech and Russian. Create a **Product/Service**
email template, paste the HTML in source mode (add the other languages as translations of the same
template) and select it as the product's Welcome Email. Merge fields: `{$radio_name}`, `{$radio_username}`,
`{$radio_panel_url}`, `{$radio_server_host}`, `{$radio_listen_url}`, `{$radio_streams_html}`,
`{$radio_max_listeners}`, `{$radio_max_bitrate}`, `{$radio_storage}`, `{$radio_traffic}`, `{$radio_features}`.

### Admin

The services tab shows current usage. Extra buttons: **Login as client** (opens the broadcaster interface)
and **Re-apply package options**. Disk and traffic usage is synced into WHMCS by the daily cron (Usage Update).
A failed Create can be run again: an account with the same username and e-mail is completed instead of
failing with "username exists".

---

## 🛡 License

This WHMCS module is open-source, licensed under the [MIT License](LICENSE).

**Note:** This module is provided **as-is**, without warranty. It is designed to integrate with **Streaming.Center**, which is a separate commercial, closed-source product requiring a valid license.

---

## 📫 Support
For support with the module, please open an issue on this repository.

For commercial support or questions about Streaming.Center Internet-radio platform, visit [https://streaming.center](https://streaming.center)

## 🌟 Acknowledgments
This module was developed by Streaming.Center to help WHMCS users integrate smoothly with our streaming platform.

Thank you for using and supporting open-source!
