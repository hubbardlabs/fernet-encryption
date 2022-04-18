<?php
/**
 * Fernet Rest API.
 *
 * @package fernet-encryption
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Fernet_Rest_API' ) ) {
	/**
	 * Fernet_Rest_API.
	 */
	class Fernet_Rest_API {


		/**
		 * Namespace.
		 *
		 * (default value: 'api/v1')
		 *
		 * @var    string
		 * @access private
		 */
		private $namespace = 'fernet/v1';

		/**
		 * Create the rest API routes.
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}

		/**
		 * Register_routes function.
		 *
		 * @access public
		 */
		public function register_routes() {

			register_rest_route(
				$this->namespace,
				'/encrypt',
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'encrypt' ),
					'permission_callback' => array( $this, 'permission_check' ),
					'args'                => array(
						'message' => array(
							'required'    => true,
							'default'     => null,
							'description' => 'The message you wish to encrypt.',
						),
						'key'     => array(
							'required'    => false,
							'default'     => null,
							'description' => 'The fernet key you wish to use for encryption.',
						),
					),
				)
			);

			register_rest_route(
				$this->namespace,
				'/decrypt',
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'decrypt' ),
					'permission_callback' => array( $this, 'permission_check' ),
					'args'                => array(
						'token' => array(
							'required'    => true,
							'default'     => null,
							'description' => 'The token you wish to decrypt.',
						),
						'key'   => array(
							'required'    => false,
							'default'     => fernet_key(),
							'description' => 'The fernet key you wish to use for decryption.',
						),
						'ttl'   => array(
							'required'    => false,
							'default'     => '',
							'description' => 'The TTL for used in decryption.',
						),
					),
				)
			);

		}

		/**
		 * Encrypt function.
		 *
		 * @access
		 * @param  WP_REST_Request $request Request.
		 * @return WP_REST_Response Response.
		 **/
		public function encrypt( WP_REST_Request $request ) {

			$params = $request->get_body_params();

			$message    = $params['message'] ?? null;
			$fernet_key = $params['key'] ?? fernet_key();

			$args       = array();
			$encryption = fernet_encrypt( $message, $args );
			return rest_ensure_response( $encryption );

		}

		/**
		 * Decrypt function.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response Response.
		 */
		public function decrypt( WP_REST_Request $request ) {
			$params = $request->get_body_params();

			$token = $params['token'];
			$key   = $params['key'];
			$ttl   = $params['ttl'];

			$args = array();

			$message = fernet_decrypt( $token, $args );
			return rest_ensure_response( $message );

		}


		/**
		 * Permission Check.
		 *
		 * @access public
		 * @param  mixed $data Data.
		 */
		public function permission_check( $data ) {

			if ( ! current_user_can( 'administrator' ) ) {
				return new WP_Error( 'forbidden', 'You are not allowed to do that.', array( 'status' => 403 ) );
			}

			return true;

		}

	}

	new Fernet_Rest_API();

}
