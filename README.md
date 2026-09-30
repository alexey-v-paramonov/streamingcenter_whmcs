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
removing them would shift every option after them. They are no longer sent to the
server. Existing accounts keep the limit the server gave them on upgrade until you
set a value.

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
