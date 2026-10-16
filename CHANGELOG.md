# Changelog

## Unreleased

### Added
- Custom packages through WHMCS configurable options (listeners, bitrate, disk, traffic, radios, channels,
  social streams, server type and feature switches), applied on Create and Change Package, also on top of templates.
- "Create configurable options" button on the product's Module Settings tab.
- Station name from the order form ("Station name" / "Radio name" custom field); the generated username is based on it.
- Client area overview (status, listeners, now playing, stream URLs, usage, plan) with English, Czech and Russian texts.
- Welcome email templates (EN/CS/RU) and `{$radio_*}` merge fields.
- Usage Update: disk and traffic usage synced into WHMCS.
- Admin buttons "Login as client" and "Re-apply package options"; current usage on the services tab.

### Changed
- Create can be retried: an existing account with the same username and e-mail is completed instead of failing.

All existing module settings and their positions are unchanged. For products without configurable options
and without a station name field, Create and Change Package send the same requests as before (Create only adds
one remote_get_details call first, to detect a retried Create).
