<?php
/**
 * Fernet Encryption for WordPress
 *
 * @package fernet-encryption
 */

/**
 * Plugin Name: Fernet Encryption
 * Plugin URI: https://accessnetworks.github.io/fernet-encryption/
 * Description: Secure WordPress data with Fernet Encryption.
 * Version: 1.0.7
 * Author: Access Networks
 * Author URI: https://www.accessnetworks.com
 * Text Domain: fernet-encryption
 * Domain Path: /i18n/languages/
 * Requires at least: 5.5
 * Requires PHP: 7.0
 */

// Include Fernet Encryption Class.
require_once 'includes/class-fernet.php';
require_once 'includes/helpers.php';
require_once 'includes/class-fernet-shortcodes.php';
require_once 'includes/class-fernet-rest-api.php';
require_once 'includes/class-fernet-cli.php';

if ( ! function_exists( 'fernet' ) ) {
	/**
	 * Init Fernet.
	 */
	function fernet() {
		$fernet = new Fernet( fernet_key() );
		return $fernet;
	}
}

if ( ! function_exists( 'fernet_generate_key' ) ) {
	/**
	 * Feneret Generate Key.
	 */
	function fernet_generate_key() {
		$fernet = new Fernet( fernet_key() );
		return $fernet->generate_key();
	}
}

if ( ! function_exists( 'fernet_validate_key_length' ) ) {
	/**
	 * Feneret Validate Key Length.
	 *
	 * @param string $key Key.
	 * @return bool Is Key a valid length.
	 */
	function fernet_validate_key_length( string $key ) {
		if ( 32 === strlen( $key ) ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'fernet_key' ) ) {
	/**
	 * Fernet Key
	 *
	 * @return string $key Key used for encryption.
	 */
	function fernet_key() {

		if ( ! defined( 'FERNET_KEY' ) ) {
			$key = substr( NONCE_SALT, 0, 32 );
			define( 'FERNET_KEY', $key );
		} else {
			$key = FERNET_KEY;
		}

		return $key;
	}
}

if ( ! function_exists( 'fernet_key_exists' ) ) {

	/**
	 * Fernet Key Exists.
	 *
	 * @return bool Return if key exists.
	 */
	function fernet_key_exists() {

		if ( defined( 'FERNET_KEY' ) ) {
			return true;
		}

		return false;

	}
}

if ( ! function_exists( 'fernet_encrypt' ) ) {
	/**
	 * Fernet Encode.
	 *
	 * @param [type] $data Data to be encoded.
	 * @param array  $args Arguments.
	 * @return string $data Encoded data.
	 */
	function fernet_encrypt( $data, $args = array() ) {
		$key    = isset( $args['key'] ) ? $args['key'] : fernet_key();
		$fernet = fernet( $key );
		return $fernet->encode( $data );
	}
}

if ( ! function_exists( 'fernet_decrypt' ) ) {
	/**
	 * Fernet Decode.
	 *
	 * @param string $token Token to decode.
	 * @param array  $args Arguments.
	 * @return string $data Decoded data.
	 */
	function fernet_decrypt( string $token, $args = array() ) {
		$key    = isset( $args['key'] ) ? $args['key'] : fernet_key();
		$ttl    = isset( $args['ttl'] ) ? abs( $args['ttl'] ) : null;
		$fernet = fernet( $key );
		return $fernet->decode( $token, $ttl );
	}
}

if ( ! function_exists( 'fernet_admin_notice' ) ) {
	/**
	 * Fernet Admin Notice.
	 */
	function fernet_admin_notice() {
		if ( ! fernet_key_exists() ) {
			?>
		<div class="notice notice-warning is-dismissible">
			<p><strong><?php esc_html_e( 'Warning: You have not defined a Fernet Key.', 'fernet-encryption' ); ?></strong></p>
			<p><?php esc_html_e( 'We have auto-generated one for you based off the WordPress Salts, please setup a new key in your wp-config.php file.', 'fernet-encryption' ); ?></p>
			<pre><code>define( FERNET_KEY, 'YOUR-KEY' );</code></pre>
			<p><strong><?php esc_html_e( 'Temp Fernet Key:', 'fernet-encryption' ); ?></strong> <?php echo esc_html( fernet_key() ); ?></p>
		</div>
			<?php
		}
	}
	add_action( 'admin_notices', 'fernet_admin_notice' );
}
