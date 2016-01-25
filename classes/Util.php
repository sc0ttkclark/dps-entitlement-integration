<?php
/**
 * @package DPS\Entitlements
 */
namespace DPS\Entitlements;

/**
 * Class Util
 */
class Util extends Singleton {

	/**
	 * @var array Messages stored here mainly for testing purposes
	 */
	public static $messages = array();

	/**
	 * Setup translatable messages
	 */
	public static function setup_messages() {

		// DPS
		self::$messages['dps-connection-failed'] = __( 'Cannot connect to DPS fulfillment server', 'dps-entitlement-integration' );

		// Auth token
		self::$messages['auth-token-missing'] = __( 'Authentication token is required', 'dps-entitlement-integration' );
		self::$messages['auth-token-invalid'] = __( 'Invalid or expired authentication token', 'dps-entitlement-integration' );

		// Subscription
		self::$messages['subscription-inactive'] = __( 'Subscription not active', 'dps-entitlement-integration' );
		self::$messages['subscription-missing']  = __( 'Subscription does not exist', 'dps-entitlement-integration' );
		self::$messages['subscription-invalid']  = __( 'Subscription invalid', 'dps-entitlement-integration' );

		// Product
		self::$messages['product-required'] = __( 'Entitlement Product required', 'dps-entitlement-integration' );

		// Login
		self::$messages['login-invalid']       = __( 'Login does not exist or password does not match', 'dps-entitlement-integration' );
		self::$messages['login-required']      = __( 'Login and Password are required', 'dps-entitlement-integration' );
		self::$messages['max-devices']         = __( 'You have reached the Maximum number of devices allowed for this account', 'dps-entitlement-integration' );
		self::$messages['uuid-not-registered'] = __( 'Device ID is not registered for this account', 'dps-entitlement-integration' );
		self::$messages['uuid-invalid']        = __( 'Device ID is invalid', 'dps-entitlement-integration' );

	}

	/**
	 * Get WP_Error from code
	 *
	 * @param string $code    Error code
	 * @param string $message Error message
	 *
	 * @return \WP_Error
	 */
	public static function get_wp_error( $code, $message = '' ) {

		if ( isset( self::$messages[ $code ] ) ) {
			$message = self::$messages[ $code ];
		}

		return new \WP_Error( $code, $message );

	}

}