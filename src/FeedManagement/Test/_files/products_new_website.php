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


/** @var Magento\TestFramework\ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var $website \Magento\Store\Model\Website */
$website = $objectManager->create(\Magento\Store\Model\Website::class);

if (!$website->load('second_website', 'code')->getId()) {
    $website->setData(['code' => 'second_website', 'name' => 'Second Website', 'is_default' => '0']);
    $website->save();
}

$websiteId = $website->getId();

$group = $objectManager->create(\Magento\Store\Model\Group::class);

if (!$group->load('Second Group', 'name')->getId()) {
    $group->setData(['website_id' => $websiteId, 'name' => 'Second Group', 'root_category_id' => '2']);
    $group->save();
}

$groupId = $group->getId();

$store = $objectManager->create(\Magento\Store\Model\Store::class);
$storeId = $store->load('fixture_second_store', 'code')->getId();

if (!$storeId) {
    $store->setCode(
        'fixture_second_store'
    )->setWebsiteId(
        $websiteId
    )->setGroupId(
        $groupId
    )->setName(
        'Fixture Store'
    )->setSortOrder(
        10
    )->setIsActive(
        1
    );
    $store->save();

    $eventManager = $objectManager->create(\Magento\Framework\Event\ManagerInterface::class);
    $eventName = 'store_add';
    $eventManager->dispatch($eventName, ['store' => $store]);

    /* Refresh stores memory cache */
    $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->reinitStores();
}


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
    ->setWebsiteIds([$websiteId])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1])
    ->setNewsFromDate(date('Y-m-d', strtotime('-2 day')))
    ->setNewsToDate(date('Y-m-d', strtotime('+2 day')))
    ->setDescription('Description with <b>html tag</b>')
    ->setShortDescription('short desc')
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->save();

/** @var $productTwo \Magento\Catalog\Model\Product */
$productTwo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$productTwo->setTypeId(Configurable::TYPE_CODE)
    ->setId(5001)
    ->setAttributeSetId(4)
    ->setWebsiteIds([$websiteId])
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1])
    ->save();


