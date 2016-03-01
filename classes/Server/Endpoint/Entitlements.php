<?php
/**
 * @package DPS\Entitlements
 */
namespace DPS\Entitlements\Server\Endpoint;

use DPS\Entitlements\User;
use DPS\Entitlements\Server;
use DPS\Entitlements\Server\Endpoint;

/**
 * Class Entitlements
 */
class Entitlements extends Endpoint {

	/**
	 * {@inheritdoc}
	 */
	public function build_xml_response( $xml ) {

		$entitlements = $xml->addChild( 'entitlements' );

		// v2 of Entitlements uses $_POST
		$is_v2 = false;

		if ( ! empty( $_POST ) ) {
			$is_v2 = true;
		}

		// Get entitlements
		$user_entitlements = $this->get_entitlements();

		if ( $user_entitlements ) {
			if ( ! is_wp_error( $user_entitlements ) ) {
				foreach ( $user_entitlements as $entitlement ) {
					$product = $entitlements->addChild( 'productId', $entitlement['productId'] );

					if ( $is_v2 ) {
						$product->addAttribute( 'subscriberType', $entitlement['subscriberType'] );
						$product->addAttribute( 'subscriberId', $entitlement['subscriberId'] );
					}
				}
			} else {
				// Invalid subscription for user
				$this->response_code = Server::HTTP_ERROR;
				$this->error         = $user_entitlements;
			}
		}

		return $xml;

	}

	/**
	 * Get entitlements from request based on auth token and corresponding user
	 *
	 * @return array|\WP_Error List of entitlements or error message
	 */
	public function get_entitlements() {

		$entitlements = array();

		// Get auth token
		$auth_token = '';

		if ( ! empty( $_REQUEST['authToken'] ) ) {
			$auth_token = trim( $_REQUEST['authToken'] );
		}

		// Get user from auth token
		$user = User::get_user_from_auth_token( $auth_token );

		if ( ! is_wp_error( $user ) ) {
			// Get entitlements for user
			$entitlements = $user->get_entitlements();
		} else {
			// No user for auth token found
			$this->response_code = Server::HTTP_FORBIDDEN;
			$this->error         = $user;
		}

		return $entitlements;

	}

}