<?php
/**
 * @package DPS\Entitlements
 */
namespace DPS\Entitlements;

/**
 * Class User
 *
 * @property int    $ID         User ID
 * @property string $user_email User e-mail
 * @property string $user_login User login
 * @property string $user_pass  User password
 */
class User {

	/**
	 * @var array Messages stored here mainly for testing purposes
	 */
	public static $messages = array();

	/**
	 * @var \WP_User User object
	 */
	public $user;

	/**
	 * User constructor.
	 *
	 * @param \WP_User $user User object
	 */
	public function __construct( $user ) {

		$this->user = $user;

	}

	/**
	 * Get user object from DPS authentication token
	 *
	 * @param string $auth_token Authentication token
	 *
	 * @return User|\WP_Error User object or error message
	 */
	public static function get_user_from_auth_token( $auth_token ) {

		if ( '' === $auth_token ) {
			return Util::get_wp_error( 'auth-token-missing' );
		} elseif ( 32 !== strlen( $auth_token ) ) {
			return Util::get_wp_error( 'auth-token-invalid' );
		}

		$args = array(
			'meta_query' => array(
				array(
					'key'   => 'dps_auth_token',
					'value' => $auth_token,
				),
			),
			'orderby'    => 'ID',
			'order'      => 'ASC',
			'number'     => 1,
		);

		$users = get_users( $args );

		if ( $users ) {
			// Get first \WP_User object
			$user = current( $users );

			// Setup new User object from \WP_User
			$user = new self( $user );
		} else {
			$user = Util::get_wp_error( 'auth-token-invalid' );
		}

		return $user;

	}

	/**
	 * Get user object by login or e-mail address
	 *
	 * @param string $login_email Login or e-mail address
	 *
	 * @return User|bool|\WP_Error User object, false if not found, \WP_Error if there was a problem
	 */
	public static function get_user_by_login_email( $login_email ) {

		// Get user by login
		$user = get_user_by( 'login', $login_email );

		if ( ! $user || is_wp_error( $user ) ) {
			// Fallback, get user by email
			$user = get_user_by( 'email', $login_email );
		}

		if ( $user && ! is_wp_error( $user ) ) {
			// Setup new User object from \WP_User
			$user = new self( $user );
		}

		return $user;

	}

	/**
	 * Get user object from DPS authentication token
	 *
	 * @param bool $force_reset Force reset of auth token
	 *
	 * @return string Auth token
	 */
	public function get_auth_token( $force_reset = false ) {

		$auth_token = get_user_meta( $this->user->ID, 'dps_auth_token', true );

		if ( 32 !== strlen( $auth_token ) || $force_reset ) {
			$auth_token = wp_generate_password( 32, false );

			$this->set_auth_token( $auth_token );
		}

		return $auth_token;

	}

	/**
	 * Set DPS authentication token for user
	 */
	public function set_auth_token( $auth_token ) {

		update_user_meta( $this->user->ID, 'dps_auth_token', $auth_token );

	}

	/**
	 * Delete DPS authentication token for user
	 */
	public function delete_auth_token() {

		delete_user_meta( $this->user->ID, 'dps_auth_token' );

	}

	/**
	 * Check if subscription is active for user
	 *
	 * @return bool Whether subscription is active for user
	 */
	public function is_subscription_active() {

		// Check if subscription is active
		$subscription_is_active = get_user_meta( $this->user->ID, 'dps_active', true );
		$subscription_is_active = filter_var( $subscription_is_active, FILTER_VALIDATE_BOOLEAN );
		$subscription_is_active = apply_filters( 'dps_entitlement_user_subscription_is_active', $subscription_is_active, $this );

		return $subscription_is_active;

	}

	/**
	 * Deactivate subscription for user
	 */
	public function activate_subscription() {

		update_user_meta( $this->user->ID, 'dps_active', 1 );

		do_action( 'dps_entitlement_user_subscription_activated', $this );

	}

	/**
	 * Deactivate subscription for user
	 */
	public function deactivate_subscription() {

		delete_user_meta( $this->user->ID, 'dps_active' );

		do_action( 'dps_entitlement_user_subscription_deactivated', $this );

	}

	/**
	 * Get subscription period for user
	 *
	 * @return array|\WP_Error An array of start/end time or error message
	 */
	public function get_subscription_period() {

		$subscription_period = array(
			'start' => 0,
			'end'   => 0,
		);

		// Get subscription start and end
		$subscription_start_date = get_user_meta( $this->user->ID, 'dps_start_date', true );
		$subscription_start_date = apply_filters( 'dps_entitlement_user_subscription_start_date', $subscription_start_date, $this );

		$subscription_end_date = get_user_meta( $this->user->ID, 'dps_end_date', true );
		$subscription_end_date = apply_filters( 'dps_entitlement_user_subscription_end_date', $subscription_end_date, $this );

		if ( empty( $subscription_start_date ) || empty( $subscription_end_date ) ) {
			return Util::get_wp_error( 'subscription-missing' );
		}

		$subscription_period['start'] = strtotime( $subscription_start_date );
		$subscription_period['end']   = strtotime( $subscription_end_date );

		if ( false === $subscription_period['start'] || false === $subscription_period['end'] ) {
			return Util::get_wp_error( 'subscription-invalid' );
		}

		return $subscription_period;

	}

	/**
	 * Get DPS Entitlements for a user
	 *
	 * @return array|\WP_Error List of entitlements or error message
	 */
	public function get_entitlements() {

		$entitlements = array();

		// Check if subscription is active
		$subscription_is_active = $this->is_subscription_active();

		if ( ! $subscription_is_active ) {
			// Subscription is not active

			// Delete auth token from user
			//$this->delete_auth_token();

			return Util::get_wp_error( 'subscription-inactive' );
		}

		// Check if issues should be restricted to subscription period
		$restrict_issues_to_subscription_period = apply_filters( 'dps_entitlement_user_restrict_issues_to_subscription_period', true, $this );

		$subscription_start_time = 0;
		$subscription_end_time   = 0;

		if ( $restrict_issues_to_subscription_period ) {
			$subscription_period = $this->get_subscription_period();

			if ( is_wp_error( $subscription_period ) ) {
				return $subscription_period;
			}

			$subscription_start_time = $subscription_period['start'];
			$subscription_end_time   = $subscription_period['end'];
		}

		// Get issues from Adobe DPS API
		$fulfillment_url = 'http://edge.adobe-dcfs.com/ddp/issueServer/issues?targetDimensions=all&accountId=%s';
		$fulfillment_url = sprintf( $fulfillment_url, Server::$fulfillment_account_id );

		$response = wp_remote_get( $fulfillment_url );

		if ( ! is_wp_error( $response ) ) {
			// Get XML response
			$result = wp_remote_retrieve_body( $response );

			$fulfillment_xml = new \SimpleXMLElement( $result );

			// Get available issues
			$issues = $fulfillment_xml->xpath( '/results/issues/issue' );

			foreach ( $issues as $issue ) {
				// Broker must be appleStore
				if ( ! isset( $issue->brokers->broker[0] ) || 'appleStore' != $issue->brokers->broker[0] ) {
					continue;
				}

				// Check publish time against subscription period
				$issue_publish_time = strtotime( $issue->publicationDate );

				if ( ! $restrict_issues_to_subscription_period || ( $subscription_start_time <= $issue_publish_time && $issue_publish_time <= $subscription_end_time ) ) {
					$entitlements[] = array(
						'productId'      => $issue['productId'],
						'subscriberType' => 'direct',
						'subscriberId'   => $this->user->user_email,
					);
				}
			}
		} else {
			// Connection issue, bad response, etc
			return Util::get_wp_error( 'dps-connection-failed' );
		}

		return $entitlements;

	}

	/**
	 * Check if a user has an entitlement by Product ID
	 *
	 * @param string $product_id Entitlement Product ID
	 *
	 * @return bool|\WP_Error Whether user has entitlement or error message
	 */
	public function has_entitlement( $product_id ) {

		$product_id = trim( $product_id );

		$entitlements = $this->get_entitlements();

		$has_entitlement = false;

		if ( $entitlements && ! is_wp_error( $entitlements ) && '' !== $product_id ) {
			foreach ( $entitlements as $entitlement ) {
				if ( $product_id === $entitlement['productId'] ) {
					$has_entitlement = true;

					break;
				}
			}
		}

		return $has_entitlement;

	}

	/**
	 * Get currently registered Device UUIDs for user
	 *
	 * @return array
	 */
	public function get_registered_uuids() {

		$uuids = get_user_meta( $this->user->ID, 'dps_uuids', true );

		if ( empty( $current_uuids ) || ! is_array( $current_uuids ) ) {
			$uuids = array();
		}

		$uuids = apply_filters( 'dps_entitlement_user_registered_uuids', $uuids, $this );

		return $uuids;

	}

	/**
	 * Register Device UUID to user
	 *
	 * @param string $uuid
	 */
	public function register_uuid( $uuid ) {

		$uuids = $this->get_registered_uuids();

		if ( ! in_array( $uuid, $uuids ) ) {
			$uuids[] = $uuid;
		}

		update_user_meta( $this->user->ID, 'dps_uuids', $uuids );

		do_action( 'dps_entitlement_user_uuid_registered', $uuid, $uuids, $this );

	}

	/**
	 * @param      $uuid
	 * @param bool $register
	 *
	 * @return bool
	 */
	public function is_uuid_allowed( $uuid, $register = true ) {

		$max_devices = apply_filters( 'dps_entitlement_user_max_devices', 10, $this );

		$uuids = $this->get_registered_uuids();

		$uuid_is_allowed = false;

		if ( in_array( $uuid, $uuids ) ) {
			// Device is allowed
			$uuid_is_allowed = true;
		} elseif ( $this->max_devices < 1 || count( $uuids ) <= $max_devices ) {
			$uuid_is_allowed = true;

			if ( $register ) {
				$this->register_uuid( $uuid );
			}
		}

		$uuid_is_allowed = apply_filters( 'dps_entitlement_user_uuid_is_allowed', $uuid_is_allowed, $uuid, $register, $this );

		return $uuid_is_allowed;

	}

	/**
	 * Magic method to get User property
	 *
	 * @param string $name Property name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {

		return $this->user->{$name};

	}

	/**
	 * Magic method to set User property
	 *
	 * @param string $name  Property name
	 * @param mixed  $value Property value
	 */
	public function __set( $name, $value ) {

		$this->user->{$name} = $value;

	}

}