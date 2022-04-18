=== Fernet Encryption ===
Contributors: bhubbard, accessnetworks
Donate link: https://accessnetworks.com
Tags: fernet, encryption, security
Requires at least: 4.5
Tested up to: 5.8
Requires PHP: 7.0
Stable tag: 1.0.7
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Fernet Encryption is a plugin that can be used to encrypt and decrypt data in WordPress using fernet.

== Description ==

Fernet Encryption is a plugin that can be used to encrypt and decrypt data in WordPress using fernet.

=== Setup your Key ===

To setup your key, you need to add the following line to your wp-config file.

```
define( 'FERNET_KEY', 'YOUR_FERNET_KEY' );
```

If you choose not to setup your key, you will need to save a copy of the one provided upon activation of the plugin.

*IMPORTANT: Changing your WordPress salts will invalidate the default Fernet key provided.*

=== How to Use ===

To encrypt data simply use `$token = fernet_encrypt( 'YOUR MESSAGE' )` in your code.

To decrypt the data simply use `fernet_decrypt( $token )` in your code.

You can use the following shortcode to encrypt:

`[fernet-encrypt]YOUR MESSAGE[/fernet-encrypt]`

You can use the following shortcode to decrypt:

`[fernet-decrypt]YOUR-FERNET-TOKEN[/fernet-decrypt]`

We have also added useful helper functions:

 * fernet_get_post_meta
 * fernet_add_post_meta
 * fernet_update_post_meta
 * fernet_get_user_meta
 * fernet_add_user_meta
 * fernet_update_user_meta
 * fernet_add_option
 * fernet_get_option
 * fernet_update_option

=== Credit ===

- Illustrations provided by undraw.co
- Fernent PHP modified from Kelvin Mo - Fernet-PHP

== Changelog ==

= 1.0.7 =
* Readme improvements

= 1.0.6 =
* Add support for WordPress Rest API.
* Add support for WordPress CLI.

= 1.0.5 =
* Added Helper Functions.

= 1.0.4 =
* Updated Readme

= 1.0.3 =
* Updated Readme.
* Provided shortcodes for encrypting and decrypting.

= 1.0.0 =
* First release.
