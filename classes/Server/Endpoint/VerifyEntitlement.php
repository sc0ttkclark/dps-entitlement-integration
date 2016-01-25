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
 * Class VerifyEntitlement
 */
class VerifyEntitlement extends Endpoint {

	/**
	 * {@inheritdoc}
	 */
	public function build_xml_response( $xml ) {

		$has_entitlement = $this->verify_entitlement();

		$entitled = 'false';

		if ( $has_entitlement && ! is_wp_error( $has_entitlement ) ) {
			$entitled = 'true';
		} else {
			$this->response_code = Server::HTTP_FORBIDDEN;
			$this->error         = $has_entitlement;
		}

		$xml->addChild( 'entitled', $entitled );

		return $xml;

	}

	/**
	 * Verify user entitlement
	 *
	 * @return bool|\WP_Error Whether user has entitlement or error message
	 */
	public function verify_entitlement() {

		$auth_token = '';
		$product_id = '';

		if ( ! empty( $_REQUEST['authToken'] ) ) {
			$auth_token = trim( $_REQUEST['authToken'] );
		}

		if ( ! empty( $_REQUEST['productId'] ) ) {
			$product_id = trim( $_REQUEST['productId'] );
		}

		if ( '' === $product_id ) {
			// Empty Product ID
			return Util::get_wp_error( 'product-required' );
		}

		// Get user from auth token
		$user = User::get_user_from_auth_token( $auth_token );

		if ( $user && ! is_wp_error( $user ) ) {
			// Check if user has entitlement
			$has_entitlement = $user->has_entitlement( $product_id );
		} else {
			// Error getting user from auth token
			$has_entitlement = $user;
		}

		return $has_entitlement;

	}

}