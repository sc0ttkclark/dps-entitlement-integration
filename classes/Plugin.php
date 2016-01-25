<?php
/**
 * @package DPS\Entitlements
 */
namespace DPS\Entitlements;

/**
 * Class Plugin
 */
class Plugin extends Singleton {

	/**
	 * Initialize the class
	 */
	protected function __construct() {

		add_action( 'init', array( $this, 'init_server' ) );
		add_filter( 'query_vars', array( $this, 'add_dps_query_var' ) );
		add_action( 'dps_entitlement_server_register_endpoints', array( $this, 'register_endpoints' ) );

	}

	/**
	 * Add rewrite rule and run DPS Entitlements Server
	 */
	public function init_server() {

		add_rewrite_rule( 'dps-api/([^/]+)', 'index.php?dps_endpoint=$matches[1]' );

		$endpoint = get_query_var( 'dps_endpoint' );

		if ( ! empty( $endpoint ) ) {
			require_once DPS_ENTITLEMENTS_DIR . 'classes/Server.php';
			require_once DPS_ENTITLEMENTS_DIR . 'classes/Server/Endpoint.php';
			require_once DPS_ENTITLEMENTS_DIR . 'classes/User.php';
			require_once DPS_ENTITLEMENTS_DIR . 'classes/Util.php';

			$fulfillment_account_id = get_option( 'dps_fulfillment_account_id' );

			$server = Server::get_instance( $fulfillment_account_id );
			$server->render_endpoint( $endpoint );
		}

	}

	/**
	 * Add query var for DPS Endpoint
	 *
	 * @param array $query_vars
	 *
	 * @return array
	 */
	public function add_dps_query_var( $query_vars ) {

		$query_vars[] = 'dps_endpoint';

		return $query_vars;

	}

	/**
	 * Register endpoints for DPS API
	 *
	 * @param Server $server
	 */
	public function register_endpoints( $server ) {

		$server->register_endpoint( 'entitlements', 'Entitlements' );
		$server->register_endpoint( 'SignInWithCredentials', 'SignInWithCredentials' );
		$server->register_endpoint( 'RenewAuthToken', 'RenewAuthToken' );
		$server->register_endpoint( 'verifyEntitlement', 'VerifyEntitlement' );
		$server->register_endpoint( 'CreateAccount', 'CreateAccount' );
		$server->register_endpoint( 'ForgotPassword', 'ForgotPassword' );
		$server->register_endpoint( 'ExistingSubscription', 'ExistingSubscription' );
		$server->register_endpoint( 'Banner', 'Banner' );

	}

	/**
	 * Settings page
	 *
	 * Tool: Delete DPS User Meta (and optionally deactivate plugin too)
	 * Tool: Delete DPS Auth Tokens
	 * Tool: Delete DPS UUIDs
	 *
	 * Option: Set Fulfillment Account ID
	 *
	 * Option: Set Banner image
	 * Option: Enable Create Account page
	 * Option: Enable Existing Subscription page
	 * Option: Set Logo for Create Account + Forgot Password + Existing Subscription pages
	 */

	/**
	 * User profile screen tool:
	 *
	 * Clear/Reset Known UUIDs for a user (in case they reach limit)
	 */

}