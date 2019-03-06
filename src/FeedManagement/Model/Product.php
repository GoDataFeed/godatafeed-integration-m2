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

namespace GoDataFeed\FeedManagement\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Api\ProductTypeListInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use GoDataFeed\FeedManagement\Api\ProductInterface;
use GoDataFeed\FeedManagement\Model\Product\ValidatorInterface;
use GoDataFeed\FeedManagement\Model\Product\ResponseCreatorInterface;

/**
 * Class Product encapsulates logic for webapi requests handling.
 * @package GoDataFeed\FeedManagement\Model
 * @author  akozyr
 */
class Product implements ProductInterface
{
    const DEFAULT_PAGE_NUMBER = 1;
    const DEFAULT_PAGE_SIZE = 50;

    /**
     * Input params that can be get from the requests;
     * @var array
     */
    private $allowedParams = [
        'getProduct' => [
            'id',
        ],
        'getProducts' => [
            'store',
            'website',
            'status',
            'type',
            'category_id',
            'page',
            'limit',
            'order_field',
            'order_direction',
        ],
        'getProductsCount' => [
            'store',
            'website',
            'status',
            'type',
            'category_id',
        ],
    ];
    /**
     * @var array
     */
    private $swappedParams = [
        'website' => 'website_id',
        'type' => 'type_id',
    ];

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
    /**
     * @var \GoDataFeed\FeedManagement\Model\ResponseCreatorInterface
     */
    private $responseCreator;
    /**
     * @var \GoDataFeed\FeedManagement\Model\ValidatorInterface
     */
    private $validator;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var ProductMetadataInterface
     */
    private $metdata;

    /**
     * @var ProductTypeListInterface
     */
    private $productTypeList;

    /**
     * Product constructor.
     *
     * @param Http                       $request
     * @param ValidatorInterface         $validator
     * @param ResponseCreatorInterface   $responseCreator
     * @param ManagerInterface           $messageManager
     * @param SearchCriteriaBuilder      $searchCriteriaBuilder
     * @param FilterBuilder              $filterBuilder
     * @param SortOrderBuilder           $sortOrderBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param CollectionFactory          $productCollectionFactory
     * @param LoggerInterface            $logger
     * @param ProductMetadataInterface   $metdata
     * @param ProductTypeListInterface   $productTypeListInterface
     */
    public function __construct(
        Http $request,
        ValidatorInterface $validator,
        ResponseCreatorInterface $responseCreator,
        ManagerInterface $messageManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $productCollectionFactory,
        LoggerInterface $logger,
        ProductMetadataInterface $metdata,
        ProductTypeListInterface $productTypeListInterface
    )
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->responseCreator = $responseCreator;
        $this->messageManager = $messageManager;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->metdata = $metdata;
        $this->logger = $logger;
        $this->productTypeList = $productTypeListInterface;

    }

    /**
     * @inheritdoc
     */
    public function getProduct($id)
    {
        $result = '';
        $params['id'] = $id;
        $filteredParams = $this->filterParams(__FUNCTION__, $params);
        try {
            if ($this->validator->validate($filteredParams)) {
                $product = $this->productRepository->getById($id);
                $result = $this->responseCreator->createResponse(__FUNCTION__, [$product]);
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
            throw $e;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getProducts()
    {
        $result = '';
        $filteredParams = $this->filterParams(__FUNCTION__, $this->request->getParams());
        try {
            if ($this->validator->validate($filteredParams)) {
                $filteredParams = $this->updateTypesFilter($filteredParams);
                $result = $this->responseCreator->createResponse(__FUNCTION__, [$this->getCollection($filteredParams)]);
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
            throw $e;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getProductsCount()
    {
        $result = '';
        $filteredParams = $this->filterParams(__FUNCTION__, $this->request->getParams());
        try {
            if ($this->validator->validate($filteredParams)) {
                $filteredParams = $this->updateTypesFilter($filteredParams);
                $result = $this->responseCreator->createResponse(__FUNCTION__, [$this->getCollection($filteredParams, false)]);
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
            throw $e;
        }
        return $result;
    }

    /**
     * Method removes product type filter if all possible types is recieved from GDF
     *
     * @param string $filters
     *
     * @return array $filters
     */
    private function updateTypesFilter($filters)
    {
        if (!empty($filters['type_id'])) {
            $productTypes = [];
            foreach ($this->productTypeList->getProductTypes() as $productType) {
                $productTypes [] = $productType->getName();
            }

            $filters['type_id'] = explode(',', $filters['type_id']);
            if (count(array_intersect($productTypes, $filters['type_id'])) === count($productTypes)) {
                unset($filters['type_id']);
            }
        }
        return $filters;
    }

    /**
     * Filters requested params. Returns only allowed ones for current API method
     *
     * @param string $method Name of function
     * @param array  $params
     *
     * @return array
     */
    private function filterParams($method, $params)
    {
        $filteredParams = array_intersect_key($params, array_flip($this->allowedParams[$method]));
        foreach ($this->swappedParams as $oldParam => $newParam) {
            if (array_key_exists($oldParam, $filteredParams)) {
                $filteredParams[$newParam] = $filteredParams[$oldParam];
                unset($filteredParams[$oldParam]);
            }
        }

        return $filteredParams;
    }

    /**
     * Method use collection to support old versions of Magento 2 that dont work with website_id and
     * repository proper way and to speed up the count requests.
     *
     * @param array   $inputParams
     * @param boolean $limit
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getCollection($inputParams, $limit = true)
    {
        $productCollection = $this->productCollectionFactory->create();
        if ($limit) {
            $productCollection->setPage(self::DEFAULT_PAGE_NUMBER, self::DEFAULT_PAGE_SIZE);
        }
        foreach ($inputParams as $key => $param) {
            if ('limit' === $key) {
                $productCollection->setPageSize($param);
                continue;
            }
            if ('page' === $key) {
                $productCollection->setCurPage($param);
                continue;
            }
            if ('order_field' === $key) {
                $orderDirection = !empty($inputParams['order_direction']) ? $inputParams['order_direction'] : 'ASC';
                $productCollection->setOrder($param, $orderDirection);
                continue;
            }
            if ('order_direction' === $key) {
                continue;
            }
            if ('type_id' === $key) {
                $productCollection->addAttributeToFilter($key, ['in' => $param]);
                continue;
            }
            if ('category_id' === $key) {
                $productCollection->addCategoriesFilter(['eq' => $param]);
                continue;
            }
            if ('website_id' === $key) {
                $productCollection->addWebsiteFilter($param);
                continue;
            }
            if ('store' === $key) {
                $productCollection->addStoreFilter($param);
                continue;
            }
            $productCollection->addAttributeToFilter($key, $param);
        }
        return $productCollection;
    }
}