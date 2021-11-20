<?php

defined( 'ABSPATH' ) or exit;

class WC_Ezdefi_Api
{
	protected $api_url;

	protected $api_key;

	protected $public_key;

	protected $db;

	/**
	 * Constructs the class
	 */
	public function __construct( $api_url = '', $api_key = '' ) {
		$this->api_url = $api_url;
		$this->api_key = $api_key;

		$this->db = new WC_Ezdefi_Db();
	}

	/**
	 * Set API Url
	 *
	 * @param $api_url
	 */
	public function set_api_url( $api_url )
	{
		$this->api_url = $api_url;
	}

	/**
	 * Get API Url
	 *
	 * @return string
	 */
	public function get_api_url()
	{
		if( empty( $this->api_url ) ) {
			$api_url = $this->db->get_api_url();
			$this->set_api_url( $api_url );
		}

		return $this->api_url;
	}

	/**
	 * Set API Key
	 *
	 * @param $api_key
	 */
	public function set_api_key( $api_key )
	{
		$this->api_key = $api_key;
	}

	/**
	 * Get API Key
	 *
	 * @return string
	 */
	public function get_api_key()
	{
		if( empty( $this->api_key ) ) {
			$api_key = $this->db->get_api_key();
			$this->set_api_key( $api_key );
		}

		return $this->api_key;
	}

    /**
     * Set public key
     *
     * @param $public_key
     */
	public function set_public_key( $public_key )
    {
        $this->public_key = $public_key;
    }

    /**
     * Get public key
     *
     * @return mixed
     */
    public function get_public_key()
    {
        if( empty( $this->public_key ) ) {
            $public_key = $this->db->get_public_key();
            $this->set_public_key( $public_key );
        }

        return $this->public_key;
    }

	/**
	 * Build API Path
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public function build_path($path)
	{
		return rtrim( $this->get_api_url(), '/' ) . '/' . $path;
	}

	/**
	 * Get API Header
	 *
	 * @return array
	 */
	public function get_headers()
	{
		$headers = array(
			'api-key' => $this->get_api_key(),
			'accept' => 'application/xml',
		);

		return $headers;
	}

	/**
	 * Call API
	 *
	 * @param string $path
	 * @param string $method
	 * @param array $data
	 *
	 * @return array|WP_Error
	 */
	public function call($path, $method = 'GET', $data = [])
	{
		$url = $this->build_path( $path ) ;

		$method = strtolower( $method );

		$headers = $this->get_headers();

		if($method === 'post') {
			return wp_remote_post( $url, array(
				'headers' => $headers,
				'body' => $data
			) );
		}

		if( ! empty( $data ) ) {
			$url = sprintf("%s?%s", $url, http_build_query( $data ) );
		}

		return wp_remote_get( $url, array( 'headers' => $headers ) );
	}

    /**
     * Get website config from gateway
     */
	public function get_website_config()
    {
        $public_key = $this->get_public_key();

        $response = $this->call( "website/$public_key" );

        return $this->parse_response( $response );
    }

    /**
     * Get website config coins
     *
     * @return |null
     */
    public function get_website_coins()
    {
        $website_config = $this->get_website_config();

        if( is_null( $website_config ) ) {
            return null;
        }

        return $website_config['coins'];
    }

	/**
	 * Create ezDeFi Payment
	 *
	 * @param array $order
	 * @param array $coin_data
	 * @param bool $amountId
	 *
	 * @return array|WP_Error
	 */
    public function create_ezdefi_payment( $order, $coin_data, $amountId = false )
    {
    	$value = $this->calculate_discounted_price( $order->get_total(), $coin_data['discount'] );

	    if( $amountId ) {
            $rate = $this->get_token_exchange( $order->get_currency(), $coin_data['token']['symbol'] );

            if( is_null( $rate ) ) {
                return new WP_Error( 'create_ezdefi_payment', 'Can not create payment.' );
            }

            $value = round( $value * $rate, $coin_data['decimal'] );
	    }

	    $uoid = $this->generate_uoid( $order->get_order_number(), $amountId );

	    $data = [
		    'uoid' => $uoid,
		    'to' => $coin_data['walletAddress'],
		    'value' => $value,
		    'safedist' => $coin_data['blockConfirmation'],
		    'duration' => $coin_data['expiration'] * 60,
		    'callback' => home_url() . '/?wc-api=ezdefi',
            'coinId' => $coin_data['_id'],
	    ];

	    if( $amountId ) {
		    $data['amountId'] = true;
		    $data['currency'] = $coin_data['token']['symbol'] . ':' . $coin_data['token']['symbol'];
	    } else {
		    $data['currency'] = $order->get_currency() . ':' . $coin_data['token']['symbol'];
	    }

	    $response = $this->call( 'payment/create', 'post', $data );

	    return $this->parse_response( $response );
    }

	/**
	 * Get ezDeFi Payment
	 *
	 * @param int $paymentid
	 *
	 * @return array|WP_Error
	 */
    public function get_ezdefi_payment( $paymentid )
    {
	    $response = $this->call( 'payment/get', 'get', array(
	        'paymentid' => $paymentid
        ) );

	    return $this->parse_response( $response );
    }

	/**
	 * Calculate discounted price
	 *
	 * @param float $price
	 * @param float|int $discount
	 *
	 * @return float|int
	 */
    public function calculate_discounted_price( $price, $discount )
    {
	    if( floatval( $discount ) > 0) {
		    return $price * (number_format((100 - $discount) / 100, 8));
	    }

	    return $price;
    }

	/**
	 * Get token exchange
	 *
	 * @param string $fiat
	 * @param string $token
	 *
	 * @return float|null
	 */
    public function get_token_exchange( $fiat, $token )
    {
	    $response = $this->call( 'token/exchange/' . $fiat . ':' . $token, 'get' );

	    return $this->parse_response( $response );
    }

    /**
     * Get token exchanges
     *
     * @param $value
     * @param $from
     * @param $to
     *
     * @return |null
     */
    public function get_token_exchanges( $value, $from, $to )
    {
    	$url = "token/exchanges?amount=$value&from=$from&to=$to";

	    $response = $this->call( $url, 'get' );

	    return $this->parse_response( $response );
    }

	/**
	 * Generate uoid with suffix
	 *
	 * @param int $uoid
	 * @param boolean $amountId
	 *
	 * @return string
	 */
    public function generate_uoid( $uoid, $amountId )
    {
	    if( $amountId ) {
		    return $uoid . '-1';
	    }

	    return $uoid = $uoid . '-0';
    }

    /**
     * Check API key
     *
     * @return array|WP_Error
     */
    public function check_api_key()
    {
    	$response = $this->call( 'user/show', 'get' );

    	return $response;
    }

	/**
	 * Get list token by keyword
	 *
	 * @param string $keyword
	 *
	 * @return array|WP_Error
	 */
	public function get_list_currency( $keyword = '' )
	{
		$version = get_bloginfo( 'version' );

		$response = $this->call( 'token/list', 'get', array(
			'keyword' => $keyword,
			'domain' => get_home_url(),
			'platform' => 'wordpress v' . $version
		) );

		return $response;
	}

    /**
     * Get transaction detail
     *
     * @param $id
     *
     * @return |null
     */
	public function get_transaction( $id )
	{
		$response = $this->call( 'transaction/get', 'get', array(
			'id' => $id
		) );

		return $this->parse_response( $response );
	}

	public function update_callback_url()
    {
        return wp_remote_request(
            $this->build_path( 'website/update_callback' ),
            array(
                'method' => 'PUT',
                'headers' => $this->get_headers(),
                'body' => array(
                    'websiteId' => $this->get_public_key(),
                    'callback' => home_url() . '/?wc-api=ezdefi'
                )
            )
        );
    }

    /**
     * Parse response
     *
     * @param $response
     *
     * @return |null
     */
	protected function parse_response( $response )
    {
        if( is_wp_error( $response ) ) {
            return null;
        }

        $response = json_decode( $response['body'], true );

        if( $response['code'] < 0 ) {
            return null;
        }

        return $response['data'];
    }
}