# PHPCS toolchain

The sniffer toolchain lives here, not in the plugin's root `composer.json`.

**Why it is separate.** The root `composer.json` holds only what the plugin
needs at runtime (`vlucas/phpdotenv`, `composer/installers`), because its
`vendor/` is what ships to WordPress.org. Sniffers are dev tooling and must not
ship. The root file used to list them too, and that made it uninstallable:
`automattic/vipwpcs ^2.4` has no release, and `woocommerce/woocommerce-sniffs
^0.1.0` needs `wpcs ^2.3`, which conflicts with `wpcs ^3.0`. They now live only
here.

## Usage

```bash
composer install --working-dir=tools/phpcs   # once per clone
npm run format:check                         # report
npm run format                               # fix
```
