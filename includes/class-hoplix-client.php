<?php



if ( ! defined( 'ABSPATH' ) ) exit;



/**

 * Hoplix API client

 */

class Hoplix_Client {

    

    private $key = false;

    private $secretkey = false;

    private $lastResponseRaw;

	private $lastResponse;

	private $userAgent = 'Hoplix WooCommerce Plugin';

	private $apiUrl;

    

    /**

	 * @param string $key Hoplix Store API key

	 * @param string $secretkey Hoplix Store API key

	 * @param bool|string $disable_ssl Force HTTP instead of HTTPS for API requests

	 *

	 * @throws HoplixException if the library failed to initialize

	 */

	public function __construct( $key = '', $secretkey = '', $disable_ssl = false ) {



		$key       = (string) $key;

		$secretkey = (string) $secretkey;



		$this->userAgent .= ' ' . Hoplix_Base::VERSION . ' (WP ' . get_bloginfo( 'version' ) . ' + WC ' . WC()->version . ')';



		if ( ! function_exists( 'json_decode' ) || ! function_exists( 'json_encode' ) ) {

			throw new HoplixException( 'PHP JSON extension is required for the Hoplix API library to work!' );

		}

		if ( strlen( $key ) < 32 ) {

			throw new HoplixException( 'Missing or invalid Hoplix store key!' );

		}

        

		$this->key    = $key;

        $this->secret = $secretkey;



		if ( $disable_ssl ) {

			$this->apiUrl = str_replace( 'https://', 'http://', $this->apiUrl );

		}



		//setup api host

		$this->apiUrl = Hoplix_Base::get_hoplix_api_host();

	}

    

    /**

     * Returns total available item count from the last request if it supports paging (e.g order list) or null otherwise.

     *

     * @return int|null Item count

     */

	public function getItemCount() {

		return isset( $this->lastResponse['paging']['total'] ) ? $this->lastResponse['paging']['total'] : null;

	}



    /**

     * Perform a GET request to the API

     * @param string $path Request path (e.g. 'orders' or 'orders/123')

     * @param array $params Additional GET parameters as an associative array

     * @return mixed API response

     * @throws HoplixApiException if the API call status code is not in the 2xx range

     * @throws HoplixException if the API call has failed or the response is invalid

     */

	public function get( $path, $params = array() ) {

		return $this->request( 'GET', $path, $params );

	}



    /**

     * Perform a DELETE request to the API

     * @param string $path Request path (e.g. 'orders' or 'orders/123')

     * @param array $params Additional GET parameters as an associative array

     * @return mixed API response

     * @throws HoplixApiException if the API call status code is not in the 2xx range

     * @throws HoplixException if the API call has failed or the response is invalid

     */

	public function delete( $path, $params = array() ) {

		return $this->request( 'DELETE', $path, $params );

	}



    /**

     * Perform a POST request to the API

     * @param string $path Request path (e.g. 'orders' or 'orders/123')

     * @param array $data Request body data as an associative array

     * @param array $params Additional GET parameters as an associative array

     * @return mixed API response

     * @throws HoplixApiException if the API call status code is not in the 2xx range

     * @throws HoplixException if the API call has failed or the response is invalid

     */

	public function post( $path, $data = array(), $params = array() ) {

		return $this->request( 'POST', $path, $params, $data );

	}

    /**

     * Perform a PUT request to the API

     * @param string $path Request path (e.g. 'orders' or 'orders/123')

     * @param array $data Request body data as an associative array

     * @param array $params Additional GET parameters as an associative array

     * @return mixed API response

     * @throws HoplixApiException if the API call status code is not in the 2xx range

     * @throws HoplixException if the API call has failed or the response is invalid

     */

	public function put( $path, $data = array(), $params = array() ) {

		return $this->request( 'PUT', $path, $params, $data );

	}





    /**

     * Perform a PATCH request to the API

     * @param string $path Request path

     * @param array $data Request body data as an associative array

     * @param array $params

     * @return mixed API response

     * @throws HoplixApiException if the API call status code is not in the 2xx range

     * @throws HoplixException if the API call has failed or the response is invalid

     */

    public function patch( $path, $data = array(), $params = array() )

    {

        return $this->request( 'PATCH', $path, $params, $data );

    }



    /**

     * Return raw response data from the last request

     * @return string|null Response data

     */

	public function getLastResponseRaw() {

		return $this->lastResponseRaw;

	}

    /**

     * Return decoded response data from the last request

     * @return array|null Response data

     */

	public function getLastResponse() {

		return $this->lastResponse;

	}

    

    /**

	 * Internal request implementation

	 *

	 * @param $method

	 * @param $path

	 * @param array $params

	 * @param null $data

	 *

	 * @return

	 * @throws HoplixApiException

	 * @throws HoplixException

	 */

	private function request( $method, $path, array $params = array(), $data = null ) {

        $responseName   =   ($path == 'create-order') ? 'status' : 'result';

		$this->lastResponseRaw = null;

		$this->lastResponse    = null;

        

		$url = trim( $path, '/' );

        $baseData   =   array("api" => $this->key, "secret" => $this->secret, "currency" => get_woocommerce_currency());

        

		if ( ! empty( $params ) ) {

			$url .= '?' . http_build_query( $params );

		}

        

        //rewirte the data in body for pass the authentication keys

        $data   =   $data !== null ? array_merge($baseData, $data) : $baseData;

            

		$request = array(

			'timeout'    => 10,

			'user-agent' => $this->userAgent,

            'httpversion' => '1.0',

			//'method'     => $method,

			'headers'    => array('Content-Type' => 'application/json'),

			'body'       => json_encode($data),

		);

        

		$result = wp_remote_post( $this->apiUrl . $url, $request );

		//allow other methods to hook in on the api result

        $result = apply_filters( 'hoplix_api_result', $result, $method, $this->apiUrl . $url, $request );
        
		if ( is_wp_error( $result ) ) {

			throw new HoplixException( "API request failed - " . $result->get_error_message() );

		}
        
        
		$this->lastResponseRaw = $result['body'];

		$this->lastResponse    = $response = json_decode( $result['body'], true );
        
        
		if ( ! isset( $response['status'], $response[$responseName] ) ) {

			throw new HoplixException( 'Invalid API response' );

		}

		$status = (int) $response['status'];

		if ( $status < 200 || $status >= 300 ) {

			throw new HoplixApiException( (string) $response[$responseName], $status );

		}
        
        return $response[$responseName];

	}

    

}



/**

 * Class HoplixException Generic Hoplix exception

 */

class HoplixException extends Exception {}

/**

 * Class HoplixException Hoplix exception returned from the API

 */

class HoplixApiException extends HoplixException {}



?>