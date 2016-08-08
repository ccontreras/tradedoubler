<?php

namespace tradedoubler;

/**
 * Tradedoubler.
 *
 * Tradedoubler OpenAPI client library {@link http://dev.tradedoubler.com}.
 *
 * <code>
 *
 * ...
 *
 * $token = ...;
 * $tradedoubler = new Tradedoubler($token);
 *
 * $params['language'] = 'es';
 * $response = $tradedoubler->getServiceData('advertisers.products.feed', $params);
 *
 * ...
 *
 * </code>
 *
 * @author Cesar E. Contreras <ccdl15c@gmail.com>
 */
class Tradedoubler {

  const BASE_URL = 'http://api.tradedoubler.com/';
  const VERSION = '1.0';

  /** @var string    $token       Token to be authenticate requests.     */
  private $token;

  /** @var string    $error       Last error thrown during request.      */
  private $error;

  /** @var array     $services    Array with current available services. */
  static $services = array(
    'advertisers' => array(
      'claims' => array(
        'list'   => '/claims',
        'update' => '/claimUpdates',
        'status' => '/claimStatuses',
      ),
      'conversions' => array(
        'create' => '/conversions/subscriptions',
        'update' => '/conversions/subscriptions',
        'delete' => '/conversions/subscriptions',
        'list'   => '/conversions/subscriptions',
      ),
      'products' => array(
        'create'     => '/products',
        'delete'     => '/products',
        'query'      => '/products',
        'unlimited'  => '/productsUnlimited',
        'categories' => '/productCategories',
        'feed'       => '/productFeeds',
      ),
      'vouchers' => array(
        'query' => '/vouchers',
      ),
    ),
    'publishers' => array(
      'products' => array(
        'create'     => '/products',
        'delete'     => '/products',
        'query'      => '/products',
        'unlimited'  => '/productsUnlimited',
        'categories' => '/productCategories',
        'feed'       => '/productFeeds',
      ),
    ),
  );

  /**
   * Constructor.
   *
   * @param   string   $token   The API token for making request with.
   */
  public function __construct($token) {
    $this->token = $token;
  }

  /**
   * Makes request to tradedoubler's API.
   *
   * Example code:
   * ---------------------------------------------------------------------------
   *
   * <code>
   *  ...
   *  $params['language'] = 'es';
   *  $tradedoubler->getServiceData('advertisers.products.categories', $params);
   * </code>
   *
   * Available services:
   * ---------------------------------------------------------------------------
   *
   * <ul>
   *  <li>advertises.claims.list</li>
   *  <li>advertises.claims.update</li>
   *  <li>advertises.claims.status</li>
   * </ul>
   *
   * @param     string         $service        The path for the service.
   * @param     array          $params         Optional array with paramateres to be sent.
   * @param     string         $method         HTTP method to be used. Defaults to GET.
   * @return    mixed                          Returns an array with the response. If any error
   *                                           ocurred, it will return <code>null</code> and you can access
   *                                           the message by calling <code>getError()</code> on the instance.
   */
  public function getServiceData($service, $params = array(), $method = 'GET') {
    $method = strtoupper($method);
    $endpoint = $this->getServiceEndpoint($service);

    if ($endpoint) {
      $ch = curl_init();

      $opts[CURLOPT_RETURNTRANSFER] = true;
      $opts[CURLOPT_FRESH_CONNECT] = true;

      // choose which method to use.
      switch ($method) {
        case 'PUT':
          $opts[CURLOPT_HTTPHEADER] = array('Content-type: application/json');
          $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
          $opts[CURLOPT_POSTFIELDS] = json_encode($params);
          $opts[CURLOPT_URL] = $this->buildEndpointURL($endpoint);
          break;
        case 'POST':
          $opts[CURLOPT_HTTPHEADER] = array('Content-type: application/json');
          $opts[CURLOPT_POST] = true;
          $opts[CURLOPT_POSTFIELDS] = json_encode($params);
          $opts[CURLOPT_URL] = $this->buildEndpointURL($endpoint);
          break;
        case 'DELETE':
          $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
          $opts[CURLOPT_URL] = $this->buildEndpointURL($endpoint, $params);
          break;
        case 'GET':
        default:
          $opts[CURLOPT_HTTPGET] = true;
          $opts[CURLOPT_URL] = $this->buildEndpointURL($endpoint, $params);
      }

      curl_setopt_array($ch, $opts);

      $json = null;
      $resp = curl_exec($ch);

      if ($resp !== FALSE) {
        $json = json_decode($resp, true);
      } else {
        $this->error = curl_error($ch);
      }

      curl_close($ch);

      return $json;
    }

    $this->error = 'No endpoint found.';

    return null;
  }

  /**
   * Builds the final endpoint URL.
   *
   * @param    string      $endpoint      The endpoint we want to work with {@see Tradedoubler::getServiceEndpoint}.
   * @param    array       $params        Optional array of paramaters to be sent.
   * @return   string                     The final URL.
   */
  public function buildEndpointURL($endpoint, array $params = null) {
    $url = self::BASE_URL.self::VERSION.$endpoint;

    if ($params) {
      foreach ($params as $key => $value) {
        $url .= ";$key=$value";
      }
    }

    $url .= "?token={$this->token}";

    return $url;
  }

  /**
   * Retrieves an endpoint for a path.
   *
   * Exanple:
   * ---------------------------------------------------------------------------
   *
   * <code>
   *  ...
   *
   *  $tradedoubler->getServiceEndpoint('advertisers.products.categories');
   * </code>
   *
   * @param    string    $service      The path for the service. This must be like follows: <code>foo.bar.doe</code>.
   * @return   mixed                   Returns <code>null</code> if the endpoint was not found or string if found.
   */
  public function getServiceEndpoint($service) {
    if (preg_match_all('/(?P<keys>[a-z]+)\.?/', $service, $matches)) {
      return $this->searchEndpointRecursively($matches['keys'], static::$services);
    }

    return null;
  }

  /**
   * Search for the endpoint Recursivelyly.
   *
   * @param    array    $keys     Keys to be searched for.
   * @param    array    $arr      Array with the services.
   * @param    int      $i        The current working index.
   * @return   mixed              Returns <code>null</code> if not found or string otherwise.
   */
  protected function searchEndpointRecursively($keys, $arr, $i = 0) {
    $key = $keys[$i];

    if (isset($arr[$key])) {
      if (is_array($arr[$key])) {
        return $this->searchEndpointRecursively($keys, $arr[$key], ++$i);
      }

      return $arr[$key];
    }

    return null;
  }

  /**
   * Retrieves the last error thrown.
   *
   * @return   string
   */
  public function getError() {
    return $this->error;
  }

  /**
   * Retrieves the current working token.
   *
   * @return   string
   */
  public function getToken() {
    return $this->token;
  }

  /**
   * Sets the current working token.
   *
   * @param   string   $token   The new token to use.
   */
  public function setToken($token) {
    $this->token = $token;
  }

}
