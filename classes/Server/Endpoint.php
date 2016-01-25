<?php
/**
 * @package DPS\Entitlements
 */
namespace DPS\Entitlements\Server;

use DPS\Entitlements\Server;

/**
 * Class Endpoint
 */
class Endpoint {

	/**
	 * @var \WP_Error Error response
	 */
	public $error;

	/**
	 * @var int Response code
	 */
	public $response_code = Server::HTTP_SUCCESS;

	/**
	 * Endpoint constructor.
	 */
	public function __construct() {

		// Nothing to see here

	}

	/**
	 * Build XML response for endpoint
	 *
	 * @param \SimpleXMLElement $xml
	 *
	 * @return \SimpleXMLElement
	 */
	public function build_xml_response( $xml ) {

		return $xml;

	}

	/**
	 * Render response (default is XML)
	 */
	public function render() {

		// Create new SimpleXMLElement object
		$xml = simplexml_load_string( '<?xml version="1.0" encoding="UTF-8"?>' );

		// Get XML response
		$xml = $this->build_xml_response( $xml );

		// Add response code
		$xml->addAttribute( 'httpResponseCode', $this->response_code );

		// Add error message and code if set
		if ( is_wp_error( $this->error ) ) {
			$xml->addAttribute( 'errorMessage', $this->error->get_error_message() );
			$xml->addAttribute( 'errorCode', $this->error->get_error_code() );
		}

		// Force content type
		header( 'Content-Type: application/xml' );

		// Export object to XML
		echo $xml->asXML();

		die();

	}

}