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

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/** @var $product \Magento\Catalog\Model\Product */
$product = Bootstrap::getObjectManager()
    ->create('Magento\Catalog\Model\Product');

/** @var $product \Magento\Catalog\Model\Product */
$product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setId(5000)
    ->setAttributeSetId(4)
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1])
    ->setNewsFromDate(date('Y-m-d', strtotime('-2 day')))
    ->setNewsToDate(date('Y-m-d', strtotime('+2 day')))
    ->setDescription('Description with <b>html tag</b>')
    ->setShortDescription('short desc')
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->save();

$productTwo = Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class, ['data' => $product->getData()]);
$productTwo->setUrlKey('simple-product-two')
    ->setId(5001)
    ->setName('Simple Product Two')
    ->setSku('simple-two')
    ->save();


$productThree = Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class, ['data' => $product->getData()]);
$productThree->setUrlKey('simple-product-three')
    ->setId(5002)
    ->setName('Simple Product Three')
    ->setSku('simple-three')
    ->save();

$productFour = Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class, ['data' => $product->getData()]);
$productFour->setUrlKey('simple-product-four')
    ->setId(5003)
    ->setName('Simple Product four')
    ->setSku('simple-four')
    ->save();

/** @var \Magento\Catalog\Model\Category $category */
$category = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(333)
    ->setCreatedAt('2014-06-23 09:50:07')
    ->setName('Category 1')
    ->setParentId(2)
    ->setPath('1/2/3')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setAvailableSortBy(['position'])
    ->setPostedProducts([5001 => 10, 5002 => 20, 5003 => 30])
    ->save();
