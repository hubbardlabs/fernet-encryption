<?php
/**
 * Fernet Class.
 *
 * @package fernet-encryption
 */

/**
 * Fernet-PHP
 *
 * Copyright (C) Kelvin Mo 2014
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above
 *    copyright notice, this list of conditions and the following
 *    disclaimer in the documentation and/or other materials provided
 *    with the distribution.
 *
 * 3. The name of the author may not be used to endorse or promote
 *    products derived from this software without specific prior
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
 * IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 **/

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * An implementation of the Fernet token specification in PHP.
 *
 * @link https://github.com/fernet/spec/blob/master/Spec.md
 */
class Fernet {

	const VERSION = "\x80";

	/**
	 * Encryption Key.
	 *
	 * @var string
	 */
	private $encryption_key;

	/**
	 * Signing Key.
	 *
	 * @var string
	 */
	private $signing_key;

	/**
	 * Creates an instance of the Fernet encoder/decoder.
	 *
	 * @param string $key The Fernet key, encoded in base64url format.
	 * @throws \Exception If the key is not a base64url encoded string or libraries missing.
	 */
	public function __construct( string $key ) {
		if ( ! function_exists( 'openssl_random_pseudo_bytes' ) && ! function_exists( 'mcrypt_create_iv' ) ) {
			return new WP_Error( 'missing-libraries', __( 'Fernet Encryption requires mcrypt_create_iv and openssl_random_pseudo_bytes.', 'fernet-encryption' ) );
		}

		$key = self::base64url_decode( $key );

		if ( strlen( $key ) !== 32 ) {
			return new WP_Error( 'invalid-key-length', __( 'The key length appears to be invalid.', 'fernet-encryption' ) );
		}

		$this->signing_key    = substr( $key, 0, 16 );
		$this->encryption_key = substr( $key, 16 );
	}

	/**
	 * Encodes a Fernet token.
	 *
	 * @param string $message the message to be encoded in the token.
	 * @return string the token.
	 */
	public function encode( string $message ) {
		$iv = $this->get_iv();

		// PKCS7 padding.
		$pad      = 16 - ( strlen( $message ) % 16 );
		$message .= str_repeat( chr( $pad ), $pad );

		if ( function_exists( 'openssl_encrypt' ) ) {
			$ciphertext = base64_decode( openssl_encrypt( $message, 'aes-128-cbc', $this->encryption_key, OPENSSL_ZERO_PADDING, $iv ) ); // phpcs:ignore
		} elseif ( function_exists( 'mcrypt_encrypt' ) ) {
			$ciphertext = mcrypt_encrypt( MCRYPT_RIJNDAEL_128, $this->encryption_key, $message, 'cbc', $iv );
		}

		if ( PHP_INT_SIZE === 8 ) {
			$signing_base = self::VERSION . pack( 'J', $this->get_time() ) . $iv . $ciphertext;
		} else {
			$signing_base = self::VERSION . pack( 'NN', 0, $this->get_time() ) . $iv . $ciphertext;
		}

		$hash = hash_hmac( 'sha256', $signing_base, $this->signing_key, true );
		return self::base64url_encode( $signing_base . $hash );
	}

	/**
	 * Decodes a Fernet token.
	 *
	 * @param string $token the token to decode.
	 * @param int    $ttl the maximum number of seconds since the creation of the token for the token to be considered valid.
	 * @return string|null the decoded message, or null if the token is invalid for whatever reason.
	 */
	public function decode( string $token, int $ttl = null ) {
		$raw = self::base64url_decode( $token );

		$hash          = substr( $raw, -32 );
		$signing_base  = substr( $raw, 0, -32 );
		$expected_hash = hash_hmac( 'sha256', $signing_base, $this->signing_key, true );

		if ( ! is_string( $hash ) ) {
			return null;
		}
		if ( ! $this->secure_compare( $hash, $expected_hash ) ) {
			return null;
		}

		if ( PHP_INT_SIZE === 8 ) {
			$parts = unpack( 'Cversion/Jtime', substr( $signing_base, 0, 9 ) );
		} else {
			$parts = unpack( 'Cversion/Ndummy/Ntime', substr( $signing_base, 0, 9 ) );

			// If $parts['dummy'] is not zero, this means the timestamp in the token.
			// is beyond the 32 bit limit.
			if ( 0 !== $parts['dummy'] ) {
				return null;
			}
		}

		if ( chr( $parts['version'] ) !== self::VERSION ) {
			return null;
		}

		if ( null !== $ttl ) {
			if ( $parts['time'] + $ttl < $this->get_time() ) {
				return null;
			}
		}

		$iv         = substr( $signing_base, 9, 16 );
		$ciphertext = substr( $signing_base, 25 );

		if ( function_exists( 'openssl_decrypt' ) ) {
			$message = openssl_decrypt( base64_encode( $ciphertext ), 'aes-128-cbc', $this->encryption_key, OPENSSL_ZERO_PADDING, $iv ); // phpcs:ignore
		} elseif ( function_exists( 'mcrypt_decrypt' ) ) {
			$message = mcrypt_decrypt( MCRYPT_RIJNDAEL_128, $this->encryption_key, $ciphertext, 'cbc', $iv );
		}
		if ( false === $message ) {
			return null;
		}

		$pad = ord( $message[ strlen( $message ) - 1 ] );
		if ( substr_count( substr( $message, -$pad ), chr( $pad ) ) !== $pad ) {
			return null;
		}

		return substr( $message, 0, -$pad );
	}

	/**
	 * Generates an initialisation vector for AES encryption.
	 *
	 * @return string a bytestream containing an initialisation vector.
	 */
	protected function get_iv() {
		if ( function_exists( 'random_bytes' ) ) {
			return random_bytes( 16 );
		} elseif ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
			return openssl_random_pseudo_bytes( 16 );
		} elseif ( function_exists( 'mcrypt_create_iv' ) ) {
			return mcrypt_create_iv( 16 );
		}
	}

	/**
	 * Obtains the current time.
	 *
	 * This method is required to facilitate unit testing.
	 *
	 * @return int the current time.
	 */
	protected function get_time() {
		return ( new \DateTime() )->getTimestamp();
	}

	/**
	 * Compares two strings using the same time whether they're equal or not.
	 * This function should be used to mitigate timing attacks when, for.
	 * example, comparing password hashes.
	 *
	 * @param string $str1 String 1.
	 * @param string $str2 String 2.
	 * @return bool true if the two strings are equal.
	 */
	protected function secure_compare( string $str1, string $str2 ) {
		if ( function_exists( 'hash_equals' ) ) {
			return hash_equals( $str1, $str2 );
		}

		$xor    = $str1 ^ $str2;
		$result = strlen( $str1 ) ^ strlen( $str2 ); // Not the same length, then fail ($result != 0).
		for ( $i = strlen( $xor ) - 1; $i >= 0; $i-- ) {
			$result += ord( $xor[ $i ] );
		}
		return ! $result;
	}

	/**
	 * Generates a random key for use in Fernet tokens.
	 *
	 * @return string A base64url encoded key.
	 * @throws \Exception If libraries are missing.
	 */
	public static function generate_key() {
		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
			$key = openssl_random_pseudo_bytes( 32 );
		} elseif ( function_exists( 'mcrypt_create_iv' ) ) {
			$key = mcrypt_create_iv( 32 );
		} else {
			return new WP_Error( 'missing-libraries', __( 'Fernet Encryption requires mcrypt_create_iv and openssl_random_pseudo_bytes.', 'fernet-encryption' ) );
		}
		return self::base64url_encode( $key );
	}
	/**
	 * Encodes data encoded with Base 64 Encoding with URL and Filename Safe Alphabet.
	 *
	 * @param string $data the data to encode.
	 * @param bool   $pad whether padding characters should be included.
	 * @return string the encoded data.
	 * @link http://tools.ietf.org/html/rfc4648#section-5
	 */
	public static function base64url_encode( $data, $pad = true ) {
		$encoded = strtr( base64_encode( $data ), '+/', '-_' ); // phpcs:ignore
		if ( ! $pad ) {
			$encoded = trim( $encoded, '=' );
		}
		return $encoded;
	}

	/**
	 * Decodes data encoded with Base 64 Encoding with URL and Filename Safe Alphabet.
	 *
	 * @param string $data the encoded data.
	 * @return string|bool the original data or FALSE on failure. The returned data may be binary.
	 * @link http://tools.ietf.org/html/rfc4648#section-5
	 */
	public static function base64url_decode( $data ) {
		return base64_decode( strtr( $data, '-_', '+/' ) ); // phpcs:ignore
	}
}
