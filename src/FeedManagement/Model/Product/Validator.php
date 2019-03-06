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

namespace GoDataFeed\FeedManagement\Model\Product;

use Magento\Catalog\Api\ProductTypeListInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Validator responsible for input params validation
 * @package GoDataFeed\FeedManagement\Model\Product
 * @author akozyr
 */
class Validator implements ValidatorInterface
{
    const DEFAULT_PAGE_SIZE_MAX = 250;
    /**
     * [0-9]
     */
    const TYPE_NUMERIC = 'numeric';
    /**
     * [azAZ0-9]
     */
    const TYPE_ALPHANUMERIC = 'alphanumeric';
    /**
     * [azAZ0-9_]
     */
    const TYPE_EXD_ALPHANUMERIC = 'exd_alphanumeric';

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepositoryInterface;
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var \Magento\Catalog\Api\ProductTypeListInterface
     */
    private $productTypeList;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * set of rules for each of the input params
     * @var array
     */
    private $rules = [
        'id' => [
            'type' => self::TYPE_NUMERIC,
            'min_length' => 1,
            'max_length' => 11,
            'min_value' => 1,
        ],
        'store' => [
            'type' => self::TYPE_NUMERIC,
            'min_length' => 1,
            'max_length' => 11,
            'min_value' => 1,
        ],
        'website_id' => [
            'type' => self::TYPE_EXD_ALPHANUMERIC,
            'min_length' => 1,
            'max_length' => 32,
            'min_value' => null,
        ],
        'status' => [
            'type' => self::TYPE_NUMERIC,
            'min_length' => 1,
            'max_length' => 3,
            'min_value' => null,
        ],
        'type_id' => [
            'type' => self::TYPE_EXD_ALPHANUMERIC,
            'min_length' => 1,
            'max_length' => 100,
            'min_value' => null,
        ],
        'category_id' => [
            'type' => self::TYPE_NUMERIC,
            'min_length' => 1,
            'max_length' => 11,
            'min_value' => 1,
        ],
        'page' => [
            'type' => self::TYPE_NUMERIC,
            'min_length' => 1,
            'max_length' => 11,
            'min_value' => 1,
        ],
        'limit' => [
            'type' => self::TYPE_NUMERIC,
            'min_length' => 1,
            'max_length' => 11,
            'min_value' => 1,
        ],
        'order_field' => [
            'type' => self::TYPE_EXD_ALPHANUMERIC,
            'min_length' => 1,
            'max_length' => 50,
            'min_value' => null,
        ],
        'order_direction' => [
            'type' => self::TYPE_ALPHANUMERIC,
            'min_length' => 1,
            'max_length' => 11,
            'min_value' => null,
        ],
    ];

    /**
     * Validate constructor.
     *
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepositoryInterface
     * @param \Magento\Catalog\Model\CategoryFactory        $categoryFactory
     * @param \Magento\Catalog\Api\ProductTypeListInterface $productTypeListInterface
     * @param \Magento\Framework\Message\ManagerInterface   $messageManager
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepositoryInterface,
        CategoryFactory $categoryFactory,
        ProductTypeListInterface $productTypeListInterface,
        ManagerInterface $messageManager
    ) {
        $this->attributeRepositoryInterface = $attributeRepositoryInterface;
        $this->categoryFactory = $categoryFactory;
        $this->productTypeList = $productTypeListInterface;
        $this->messageManager = $messageManager;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $params)
    {
        $this->basicValidation($params);
        $this->extraValidation($params);
        return true;
    }

    /**
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    private function basicValidation(array $params)
    {
        foreach ($params as $key => $value) {
            if (!$this->isValidType($this->rules[$key]['type'], $value)
                || $this->rules[$key]['min_length'] > strlen($value)
                || $this->rules[$key]['max_length'] < strlen($value)
                || !($value >= $this->rules[$key]['min_value'])
            ) {
                throw new LocalizedException(__("Basic validation failed, '{$key}' = '{$value}'"));
            }
        }
        return true;
    }

    /**
     * Checks type of the value.
     *
     * @param string $type
     * @param string $value
     *
     * @return bool
     */
    private function isValidType($type, $value)
    {
        $result = false;
        switch ($type) {
            case self::TYPE_NUMERIC:
                $result = ctype_digit((string)$value);
                break;
            case self::TYPE_ALPHANUMERIC:
                $result = ctype_alnum((string)$value);
                break;
            case self::TYPE_EXD_ALPHANUMERIC:
                $result = ctype_alnum(str_replace(['_', ','], '', $value));
                break;
        }
        return $result;
    }

    /**
     * Core func to validate request params
     * If something is wrong with a params it throws localized error message
     *
     * @param array $params
     *
     * @return bool|array
     * @throws \Exception
     */
    private function extraValidation(array $params)
    {

        if (array_key_exists('type_id', $params) && !$this->isProductType($params['type_id'])) {
            throw new LocalizedException(__("There is no such product type: Your value: '{$params['type_id']}'"));
        }
        if (array_key_exists('category_id', $params)) {
            $category = $this->categoryFactory->create()->load($params['category_id']);
            if (!$category->getId()) {
                throw new LocalizedException(__("Category not found. Your value: '{$params['category_id']}'"));
            }
        }
        if (array_key_exists('order_field', $params)) {
            if (!$this->isCorrectOrderField($params['order_field'])) {
                throw new LocalizedException(__("There is no such field to sort by, field: '{$params['order_field']}'"));
            }
        }
        if (array_key_exists('order_direction', $params) && !array_key_exists('order_field', $params)) {
            throw new LocalizedException(__("Oder direction parameter cannot be used without order by field"));
        }
        if (array_key_exists('order_direction', $params) && !in_array($params['order_direction'], ['ASC', 'DESC', 'asc', 'desc'])) {
            throw new LocalizedException(__("Order direction parameter should be one of the value: ASC, DESC, asc, desc. Your value: '{$params['order_direction']}'"));
        }
        if (array_key_exists('limit', $params)) {
            if ($params['limit'] > self::DEFAULT_PAGE_SIZE_MAX) {
                throw new LocalizedException(__("The paging limit exceeds the allowed number '" . self::DEFAULT_PAGE_SIZE_MAX
                    . "' : Your value: '{$params['limit']}'"));
            }
        }
        return true;
    }

    /**
     * Checks if requested product type exist.
     *
     * @param string $inputTypes
     *
     * @return bool
     */
    private function isProductType($inputTypes)
    {
        $result = false;
        $typesArray = explode(',', $inputTypes);
        $productTypes = [];
        foreach ($this->productTypeList->getProductTypes() as $productType) {
            $productTypes [] = $productType->getName();
        }
        if (count(array_intersect($productTypes, $typesArray)) == count($typesArray)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Checks if input order_filed is a string attribute code. Attribute ID is not excepted.
     *
     * @param string $field
     *
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isCorrectOrderField($field)
    {
        $attribute = $this->attributeRepositoryInterface->get('catalog_product', $field);
        if (!$attribute || $attribute->getAttributeCode() !== $field) {
            return false;
        }

        return true;
    }

}