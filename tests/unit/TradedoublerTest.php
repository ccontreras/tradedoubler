<?php

use tradedoubler\Tradedoubler;

/**
 * @author Cesar Contreras <ccdl15c@gmail.com>
 */
class TradedoublerTest extends \PHPUnit_Framework_TestCase
{

  private $token = '44D41DD809F1C630E9A99E6A8F244E963012FDA2';
  private $openAPI = null;

  protected function setUp()
  {
    $this->openAPI = new Tradedoubler($this->token);
  }

  protected function tearDown()
  {
  }

  /**
   * TEST - The client library should be initialized.
   */
  public function testInitialized()
  {
    $this->assertNotNull($this->openAPI);
  }

  /**
   * TEST - The token should have passed into the library.
   */
  public function testTokenShouldBeSet()
  {
    $this->assertEquals($this->openAPI->getToken(), $this->token);
  }

  /**
   * TEST - The API should retrieve a valid endpoint according to the passed path.
   */
  public function testShouldRetrieveAnEndpoint()
  {
    $endpoint = $this->openAPI->getServiceEndpoint('advertisers.products.categories');

    $this->assertEquals('/productCategories', $endpoint);
  }

  /**
   * TEST - The library should create a valid endpoint URL.
   */
  public function testShouldCreateAValidURL()
  {
    $endpoint = $this->openAPI->getServiceEndpoint('advertisers.products.categories');
    $builtURL = $this->openAPI->buildEndpointURL($endpoint);
    $expected = Tradedoubler::BASE_URL.Tradedoubler::VERSION."/productCategories?token={$this->token}";

    $this->assertEquals($builtURL, $expected);
  }

  /**
   * TEST - The library should request data against the API.
   */
  public function testShouldRequestData()
  {
    $response = $this->openAPI->getServiceData('advertisers.products.categories');

    $this->assertNotNull($response);
  }

}
