<?php
/**
 * Fernet Encryption Helper Functions.
 *
 * @package fernet-encryption
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'fernet_add_post_meta' ) ) {
	/**
	 * Fernet Add Post Meta.
	 *
	 * @param [type]  $post_id     Post ID.
	 * @param [type]  $meta_key    Meta Key.
	 * @param [type]  $meta_value  Meta Value.
	 * @param boolean $unique      Unique.
	 */
	function fernet_add_post_meta( $post_id, $meta_key, $meta_value, $unique = false ) {
		$meta_value = fernet_encrypt( $meta_value );
		return add_post_meta( $post_id, $meta_key, $meta_value, $unique );
	}
}

if ( ! function_exists( 'fernet_update_post_meta' ) ) {
	/**
	 * Fernet Update Post Meta.
	 *
	 * @param  [type] $post_id                  Post ID.
	 * @param  [type] $meta_key                 Meta Key.
	 * @param  [type] $meta_value               Meta Value.
	 * @param  string $prev_value               Previous Value.
	 */
	function fernet_update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = '' ) {
		$meta_value = fernet_encrypt( $meta_value );
		return update_post_meta( $post_id, $meta_key, $meta_value, $prev_value );
	}
}

if ( ! function_exists( 'fernet_get_post_meta' ) ) {
	/**
	 * Fernet Get Post Meta.
	 *
	 * @param  [type]  $post_id               Post ID.
	 * @param  string  $key                   Key.
	 * @param  boolean $single                Single.
	 */
	function fernet_get_post_meta( $post_id, $key = '', $single = false ) {
		if ( true === $single ) {
			return fernet_decrypt( get_post_meta( $post_id, $key, $single ) );
		}
		if ( false === $single ) {
			$meta = get_post_meta( $post_id, $key, $single );
			foreach ( $meta as $meta_value ) {
				$meta[] = fernet_decrypt( $meta_value );
			}
			return $meta;
		}
	}
}

if ( ! function_exists( 'fernet_add_user_meta' ) ) {
	/**
	 * Fernet Add User Meta.
	 *
	 * @param [type]  $user_id    User ID.
	 * @param [type]  $meta_key   Meta Key.
	 * @param [type]  $meta_value  Meta Value.
	 * @param boolean $unique      Unique.
	 */
	function fernet_add_user_meta( $user_id, $meta_key, $meta_value, $unique = false ) {
		$meta_value = fernet_encrypt( $meta_value );
		return add_user_meta( $user_id, $meta_key, $meta_value, $unique );
	}
}

if ( ! function_exists( 'fernet_update_user_meta' ) ) {
	/**
	 * Fernet Update User Meta.
	 *
	 * @param  [type] $user_id                 User ID.
	 * @param  [type] $meta_key                Meta Key.
	 * @param  [type] $meta_value              Meta value.
	 * @param  string $prev_value              Previous Value.
	 */
	function fernet_update_user_meta( $user_id, $meta_key, $meta_value, $prev_value = '' ) {
		$meta_value = fernet_encrypt( $meta_value );
		return update_user_meta( $user_id, $meta_key, $meta_value, $prev_value );
	}
}

if ( ! function_exists( 'fernet_get_user_meta' ) ) {
	/**
	 * Fernet Get User Meta.
	 *
	 * @param  [type]  $user_id              User ID.
	 * @param  string  $key                  Key.
	 * @param  boolean $single               Single.
	 */
	function fernet_get_user_meta( $user_id, $key = '', $single = false ) {

		if ( true === $single ) {
			return fernet_decrypt( get_user_meta( $user_id, $key, $single ) );
		}

		if ( false === $single ) {
			$meta = get_user_meta( $user_id, $key, $single );
			foreach ( $meta as $meta_value ) {
				$meta[] = fernet_decrypt( $meta_value );
			}
			return $meta;
		}

	}
}

if ( ! function_exists( 'fernet_add_option' ) ) {
	/**
	 * Fernet Add Option.
	 *
	 * @param [type] $option      Option.
	 * @param string $value       Value.
	 * @param string $deprecated  Deprecated.
	 * @param string $autoload    Autoload.
	 */
	function fernet_add_option( $option, $value = '', $deprecated = '', $autoload = 'yes' ) {
		$value = fernet_encrypt( $value );
		return add_option( $option, $value, '', $autoload );
	}
}

if ( ! function_exists( 'fernet_update_option' ) ) {
	/**
	 * Fernet Update Option.
	 *
	 * @param  [type] $option                 Option.
	 * @param  [type] $value                  Value.
	 * @param  [type] $autoload               Autoload.
	 */
	function fernet_update_option( $option, $value, $autoload = null ) {
		$value = fernet_encrypt( $value );
		return update_option( $option, $value, $autoload );
	}
}

if ( ! function_exists( 'fernet_get_option' ) ) {
	/**
	 * Fernet Get Option.
	 *
	 * @param  [type]  $option                Option.
	 * @param  boolean $default               Default.
	 */
	function fernet_get_option( $option, $default = false ) {
		$option_data = get_option( $option, $default );
		return fernet_decrypt( $option_data );
	}
}
