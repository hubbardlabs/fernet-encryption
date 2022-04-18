<?php
/**
 * Fernet Encryption Shortcodes.
 *
 * @package fernet-encryption
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Fernet_Shortcodes' ) ) {

	/**
	 * Fernet Shortcodes.
	 */
	class Fernet_Shortcodes {
		/**
		 * Constructor.
		 */
		public function __construct() {
			add_shortcode( 'fernet-encrypt', array( $this, 'fernet_encrypt_shortcode' ) );
			add_shortcode( 'fernet-decrypt', array( $this, 'fernet_decrypt_shortcode' ) );
		}

		/**
		 * Fernet Encrypt Shortcode.
		 *
		 * @param  [type] $atts Attributes for Shortcode.
		 * @param  [type] $content Content.
		 * @return $content.
		 */
		public function fernet_encrypt_shortcode( $atts, $content = null ) {
			return fernet_encrypt( $content );
		}

		/**
		 * Fernet Encrypt Shortcode.
		 *
		 * @param  [type] $atts Attributes for Shortcode.
		 * @param  [type] $content Content.
		 * @return $content.
		 */
		public function fernet_decrypt_shortcode( $atts, $content = null ) {
			return fernet_decrypt( $content );
		}

	}

	new Fernet_Shortcodes();

}
