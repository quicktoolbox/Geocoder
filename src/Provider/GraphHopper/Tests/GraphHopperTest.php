<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GraphHopper\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\GraphHopper\GraphHopper;

/**
 * @author Gary Gale <gary@vicchi.org>
 */
class GraphHopperTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName()
    {
        $provider = new GraphHopper($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('graphhopper', $provider->getName());
    }

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['GRAPHHOPPER_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GRAPHHOPPER_API_KEY value in phpunit.xml.');
        }

        $provider = new GraphHopper($this->getHttpClient($_SERVER['GRAPHHOPPER_API_KEY']), $_SERVER['GRAPHHOPPER_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('242 Acklam Road, London, United Kingdom'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(51.521124, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-0.20360200000000001, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('Acklam Road', $result->getStreetName());
        $this->assertEquals('London', $result->getLocality());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
    }

    public function testGeocodeWithRealAddressAndLocale()
    {
        if (!isset($_SERVER['GRAPHHOPPER_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GRAPHHOPPER_API_KEY value in phpunit.xml.');
        }

        $provider = new GraphHopper($this->getHttpClient($_SERVER['GRAPHHOPPER_API_KEY']), $_SERVER['GRAPHHOPPER_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('242 Acklam Road, London, United Kingdom')->withLocale('fr'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(51.521124, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-0.20360200000000001, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('Acklam Road', $result->getStreetName());
        $this->assertEquals('Londres', $result->getLocality());
        $this->assertEquals('Royaume-Uni', $result->getCountry()->getName());
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['GRAPHHOPPER_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GRAPHHOPPER_API_KEY value in phpunit.xml.');
        }

        $provider = new GraphHopper($this->getHttpClient($_SERVER['GRAPHHOPPER_API_KEY']), $_SERVER['GRAPHHOPPER_API_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(54.0484068, -2.7990345));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(54.048411999999999, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-2.7989549999999999, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('1', $result->getStreetNumber());
        $this->assertEquals('Gage Street', $result->getStreetName());
        $this->assertEquals('LA1 1UH', $result->getPostalCode());
        $this->assertEquals('Lancaster', $result->getLocality());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GraphHopper provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new GraphHopper($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GraphHopper provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new GraphHopper($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GraphHopper provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv4()
    {
        $provider = new GraphHopper($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GraphHopper provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new GraphHopper($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }
}
