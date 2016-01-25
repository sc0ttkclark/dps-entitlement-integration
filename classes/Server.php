<?php
/**
 * @package DPS\Entitlements
 */
namespace DPS\Entitlements;

/**
 * Class Server
 */
class Server extends Singleton {

	/**
	 * @var string Entitlement Fulfillment Account ID
	 */
	public static $fulfillment_account_id;

	/**
	 * @const HTTP_SUCCESS HTTP code for Success
	 */
	const HTTP_SUCCESS = 200;

	/**
	 * @const HTTP_FORBIDDEN HTTP code for Access Forbidden
	 */
	const HTTP_FORBIDDEN = 401;

	/**
	 * @const HTTP_ERROR HTTP code for Error
	 */
	const HTTP_ERROR = 500;

	/**
	 * @var (string|\DPS\Entitlements\Server\Endpoint)[] Registered endpoints
	 */
	private $endpoints = array();

	/**
	 * Initialize the class
	 *
	 * @param string $fulfillment_account_id
	 */
	protected function __construct( $fulfillment_account_id = '' ) {

		if ( ! empty( $fulfillment_account_id ) ) {
			self::$fulfillment_account_id = $fulfillment_account_id;
		}

		do_action( 'dps_entitlement_server_register_endpoints', $this );

	}

	/**
	 * Register endpoint
	 *
	 * @param string                 $rewrite_path
	 * @param string|Server\Endpoint $endpoint_class
	 */
	public function register_endpoint( $rewrite_path, $endpoint_class ) {

		// Normalize rewrite path
		$rewrite_path = trim( $rewrite_path, '/' );

		if ( ! is_object( $endpoint_class ) ) {
			// Sanitize class name
			$endpoint_class = remove_accents( $endpoint_class );
			$endpoint_class = preg_replace( '/[^a-zA-Z0-9_]/', '', $endpoint_class );
		}

		$this->endpoints[ $rewrite_path ] = $endpoint_class;

	}

	/**
	 * Get registered endpoints
	 *
	 * @return array List of endpoint rewrite paths
	 */
	public function get_endpoints() {

		return array_keys( $this->endpoints );

	}

	/**
	 * Get endpoint from rewrite path and initialize it
	 *
	 * @param string $rewrite_path Rewrite path
	 *
	 * @return bool|Server\Endpoint
	 */
	public function get_endpoint( $rewrite_path ) {

		if ( ! isset( $this->endpoints[ $rewrite_path ] ) ) {
			// Endpoint does not exist
			return false;
		} elseif ( is_object( $this->endpoints[ $rewrite_path ] ) ) {
			// Endpoint already setup
			return $this->endpoints[ $rewrite_path ];
		}

		// Get endpoint class name
		$endpoint_class = $this->endpoints[ $rewrite_path ];

		// If class doesn't exist, attempt to autoload
		if ( ! class_exists( $endpoint_class ) ) {
			$class_file = DPS_ENTITLEMENTS_DIR . 'classes/Server/Endpoint/' . $endpoint_class . '.php';

			// If file exists, include it
			if ( file_exists( $class_file ) ) {
				require_once $class_file;
			}
		}

		// If class exists store it
		if ( ! class_exists( $endpoint_class ) ) {
			// Endpoint class does not exist
			return false;
		}

		$this->endpoints[ $rewrite_path ] = new $endpoint_class;

		return $this->endpoints[ $rewrite_path ];

	}

	/**
	 * Render endpoint
	 *
	 * @param string $endpoint
	 */
	public function render_endpoint( $endpoint ) {

		$endpoint = $this->get_endpoint( $rewrite_path );
		$endpoint->render();

		die();

	}

}