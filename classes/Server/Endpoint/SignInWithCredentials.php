<?php
/**
 * @package DPS\Entitlements
 */
namespace DPS\Entitlements\Server\Endpoint;

use DPS\Entitlements\User;
use DPS\Entitlements\Util;
use DPS\Entitlements\Server;
use DPS\Entitlements\Server\Endpoint;

/**
 * Class SignInWithCredentials
 */
class SignInWithCredentials extends Endpoint {

	/**
	 * {@inheritdoc}
	 */
	public function build_xml_response( $xml ) {

		$auth_token = $this->get_auth_token_from_credentials();

		if ( ! is_wp_error( $auth_token ) ) {
			$xml->addChild( 'authToken', $auth_token );
		} else {
			$this->response_code = Server::HTTP_FORBIDDEN;
			$this->error         = $auth_token;
		}

		return $xml;

	}

	/**
	 * Get auth token from credentials and check if UUID is allowed
	 *
	 * @return string|\WP_Error Auth token or false if there is an error
	 */
	public function get_auth_token_from_credentials() {

		$uuid = '';

		if ( ! empty( $_REQUEST['uuid'] ) ) {
			$uuid = trim( $_REQUEST['uuid'] );
		}

		if ( ! empty( $uuid ) ) {
			$user_login = '';
			$password   = '';

			if ( isset( $_REQUEST['emailAddress'] ) || isset( $_REQUEST['password'] ) ) {
				// Allow for integration when not sent via XML
				if ( ! empty( $_REQUEST['emailAddress'] ) ) {
					$user_login = $_REQUEST['emailAddress'];
				}

				if ( ! empty( $_REQUEST['password'] ) ) {
					$password = $_REQUEST['password'];
				}
			} else {
				// DPS sends credentials via XML request string
				$credentials_string = file_get_contents( 'php://input' );
				$credentials_xml    = simplexml_load_string( $credentials_string );

				if ( $credentials_xml ) {
					if ( ! empty( $credentials_xml->emailAddress ) ) {
						$user_login = $credentials_xml->emailAddress;
					}

					if ( ! empty( $credentials_xml->password ) ) {
						$password = $credentials_xml->password;
					}
				}
			}

			$user_login = trim( $user_login );
			$password   = trim( $password );

			if ( ! empty( $user_login ) && ! empty( $password ) ) {
				$user = User::get_user_by_login_email( $user_login );

				if ( $user && ! is_wp_error( $user ) ) {
					if ( wp_check_password( $password, $user->user_pass ) ) {
						if ( $user->is_uuid_allowed( $uuid ) ) {
							// Device is allowed

							// Get / create auth token for user
							$auth_token = $user->get_auth_token();
						} else {
							// Max limit reached
							$auth_token = Util::get_wp_error( 'max-devices' );
						}
					} else {
						// Password does not match
						$auth_token = Util::get_wp_error( 'login-invalid' );
					}
				} else {
					// User not found
					$auth_token = Util::get_wp_error( 'login-invalid' );
				}
			} else {
				// Missing details
				$auth_token = Util::get_wp_error( 'login-required' );
			}
		} else {
			// Missing UUID
			$auth_token = Util::get_wp_error( 'uuid-invalid' );
		}

		return $auth_token;

	}

}