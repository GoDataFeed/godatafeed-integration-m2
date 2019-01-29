<?php
/**
 * Copyright 2018 Method Merchant, LLC or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace GoDataFeed\FeedManagement\Test\Api;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GoDataFeedTest extends WebapiAbstract
{
    const SERVICE_NAME = 'godatafeedProductV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/godatafeed';

    /**
     * Get information about product with entity_id = :id
     *
     * @magentoApiDataFixture ../../../../app/code/GoDataFeed/FeedManagement/Test/_files/product_simple.php
     */
    public function testGetProduct()
    {
        $expectedData = [
            'entity_id' => 222,
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/getproduct/id/' . $expectedData['entity_id'],
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'getProduct',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo);

        $this->assertTrue(is_array($response));
        $this->assertEquals($expectedData['entity_id'], $response[4]);
    }

    /**
     * Get amount of products - full list
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     */
    public function testGetProductsCount()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/products/count',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'getProductsCount',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo);

        $this->assertTrue(is_int($response));
        $this->assertGreaterThanOrEqual(2, $response);
    }

    /**
     * Get amount of products - different params
     *
     * @magentoApiDataFixture ../../../../app/code/GoDataFeed/FeedManagement/Test/_files/products_new_website.php
     * @dataProvider  getProductsCountParams
     *
     * @param $requestedParams
     * @param $expectedCount
     */
    public function testGetProductsCountParams($requestedParams, $expectedCount)
    {
        $requestString = $this->buildRequestString($requestedParams);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/products/count' . $requestString,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'getProductsCount',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo);

        $this->assertTrue(is_int($response));
        $this->assertEquals($expectedCount, $response);
    }

    /**
     * @return array
     */
    public function getProductsCountParams()
    {
        return [
            [
                [
                    'website' => 'second_website',
                    'status' => Status::STATUS_ENABLED,
                    'type' => Configurable::TYPE_CODE,
                ],
                1,
            ],
            [
                [
                    'website' => 'second_website',
                    'status' => Status::STATUS_DISABLED,
                    'type' => Configurable::TYPE_CODE,
                ],
                0,
            ],
        ];
    }

    /**
     * Get product list - different params
     *
     * @magentoApiDataFixture ../../../../app/code/GoDataFeed/FeedManagement/Test/_files/products_in_category.php
     * @dataProvider  getProductsParams
     *
     * @param $requestedParams
     * @param $expectedResult
     */
    public function testGetProductsParams($requestedParams, $expectedResult)
    {
        $productIds = [];
        $requestString = $this->buildRequestString($requestedParams);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/products' . $requestString,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'getProducts',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo);

        foreach ($response as $product) {
            $productIds[] = $product['entity_id'];
        }

        $this->assertTrue(is_array($response));
        $this->assertEquals($requestedParams['limit'], count($response));
        $this->assertEmpty(array_diff($expectedResult, $productIds));
    }

    /**
     * @return array
     */
    public function getProductsParams()
    {
        return [
            [
                ['category_id' => 333, 'limit' => 3],
                [5001, 5002, 5003],
            ],
        ];
    }

    /**
     *  As long as request is in GET mode, we have to build request string
     *
     * @param array $requestedParams
     *
     * @return string
     */
    private function buildRequestString($requestedParams)
    {
        $requestString = '';
        foreach ($requestedParams as $key => $value) {
            $requestString .= "$key=$value&";
        }
        $requestString = rtrim($requestString, '&');

        return (!empty($requestString)) ? '?' . $requestString : '';
    }

    /**
     * Get product list - sorting
     *
     * @magentoApiDataFixture ../../../../app/code/GoDataFeed/FeedManagement/Test/_files/products_in_category.php
     * @dataProvider  getProductsSort
     *
     * @param $requestedParams
     * @param $expectedResult
     */
    public function testGetProductsSort($requestedParams, $expectedResult)
    {
        $productSKUs = [];
        $requestString = $this->buildRequestString($requestedParams);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/products' . $requestString,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'getProducts',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo);

        foreach ($response as $product) {
            $productSKUs[] = $product['sku_attribute'];
        }

        $this->assertTrue(is_array($response));
        $this->assertTrue($expectedResult === $productSKUs);
    }

    /**
     * @return array
     */
    public function getProductsSort()
    {
        return [
            [
                ['category_id' => 333, 'order_field' => 'sku', 'order_direction' => 'DESC'],
                ['simple-two', 'simple-three', 'simple-four'],
            ],
        ];
    }

}
