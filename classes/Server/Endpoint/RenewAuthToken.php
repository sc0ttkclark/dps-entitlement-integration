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
 * Class RenewAuthToken
 */
class RenewAuthToken extends Endpoint {

	/**
	 * {@inheritdoc}
	 */
	public function build_xml_response( $xml ) {

		$auth_token = $this->renew_auth_token();

		if ( $auth_token && ! is_wp_error( $auth_token ) ) {
			$xml->addChild( 'authToken', $auth_token );
		} else {
			$this->response_code = Server::HTTP_FORBIDDEN;
			$this->error         = $auth_token;
		}

		return $xml;

	}

	/**
	 * Renew auth token or revoke if inactive
	 *
	 * @return string|\WP_Error Auth token or error message
	 */
	public function renew_auth_token() {

		$auth_token = '';
		$uuid       = '';

		if ( ! empty( $_REQUEST['authToken'] ) ) {
			$auth_token = trim( $_REQUEST['authToken'] );
		}

		if ( ! empty( $_REQUEST['uuid'] ) ) {
			$uuid = trim( $_REQUEST['uuid'] );
		}

		if ( ! empty( $uuid ) ) {
			// Get user from auth token
			$user = User::get_user_from_auth_token( $auth_token );

			if ( ! is_wp_error( $user ) ) {
				// Check if subscription is active
				$subscription_is_active = $user->is_subscription_active();

				if ( $subscription_is_active ) {
					// Check if UUID is registered
					if ( ! $user->is_uuid_allowed( $uuid, false ) ) {
						$auth_token = Util::get_wp_error( 'uuid-not-registered' );
					}
				} else {
					// Subscription is not active

					// Delete auth token from user
					$user->delete_auth_token();

					$auth_token = Util::get_wp_error( 'subscription-inactive' );
				}
			} else {
				// Error getting user from auth token
				$auth_token = $user;
			}
		} else {
			// Missing UUID
			$auth_token = Util::get_wp_error( 'uuid-invalid' );
		}

		return $auth_token;

	}

}