<?php
/**
 * Fernet CLI.
 *
 * @package fernet-encryption
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Fernet_CLI' ) && class_exists( 'WP_CLI_Command' ) ) {
	/**
	 * Fernet CLI.
	 */
	class Fernet_CLI extends WP_CLI_Command {

		/**
		 * Encrypt your message.
		 *
		 * @param array $args Arguments.
		 * @param array $assoc_args Associative arguments. // @codingStandardsIgnoreLine
		 *
		 * ## OPTIONS
		 *
		 * <message>
		 * : The message you wish to encrypt.
		 *
		 * [--key=<key>]
		 * : (Optional) The custom fernet key you wish to use.
		 * ---
		 * default: success
		 * options:
		 *   - success
		 *   - error
		 * ---
		 *
		 * ## EXAMPLES
		 *
		 *     wp fernet encrypt hello
		 *
		 * @when after_wp_load
		 */
		public function encrypt( $args, $assoc_args ) {
			list( $message ) = $args;

			// Print the message with type.
			$key   = $assoc_args['key'];
			$token = fernet_encrypt( $message );
			WP_CLI::success( $token );
		}

		/**
		 * Decrypt your message.
		 *
		 * @param array $args Arguments.
		 * @param array $assoc_args Associative arguments. // @codingStandardsIgnoreLine
		 *
		 * ## OPTIONS
		 *
		 * <token>
		 * : The token you wish to decrypt.
		 *
		 * [--key=<key>]
		 * : (Optional) The custom fernet key you wish to use.
		 *
		 * [--ttl=<ttl>]
		 * : (Optional) The ttl used for encryption.
		 * ---
		 * default: success
		 * options:
		 *   - success
		 *   - error
		 * ---
		 *
		 * ## EXAMPLES
		 *
		 *     wp fernet decrypt <token>
		 *
		 * @when after_wp_load
		 */
		public function decrypt( $args, $assoc_args ) {
			list( $token ) = $args;

			// Print the message with type.
			$key     = $assoc_args['key'];
			$ttl     = $assoc_args['ttl'];
			$message = fernet_decrypt( $token );
			WP_CLI::success( $message );
		}

	}
	WP_CLI::add_command( 'fernet', 'Fernet_CLI' );
}

