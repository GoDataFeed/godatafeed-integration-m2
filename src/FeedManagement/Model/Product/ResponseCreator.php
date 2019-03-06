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

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollection;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\UrlInterface;
use Magento\GroupedProduct\Model\Product\Type\GroupedFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class ResponseCreator encapsulates logic connected with the response format.
 * @package GoDataFeed\FeedManagement\Model
 * @author  akozyr
 */
class ResponseCreator implements ResponseCreatorInterface
{
    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;
    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\GroupedFactory
     */
    private $groupedFactory;
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory
     */
    private $configurableFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $productAttributeCollectionFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * ResponseCreator constructor.
     *
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface                                  $attributeSetRepositoryInterface
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface                                  $categoryRepositoryInterface
     * @param \Magento\Store\Model\StoreManagerInterface                                        $storeManagerInterface
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface                              $stockRegistryInterface
     * @param \Magento\GroupedProduct\Model\Product\Type\GroupedFactory                         $groupedFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $configurableFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                                   $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory          $productAttributeCollectionFactory
     */
    public function __construct(

        AttributeSetRepositoryInterface $attributeSetRepositoryInterface,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        StoreManagerInterface $storeManagerInterface,
        StockRegistryInterface $stockRegistryInterface,
        GroupedFactory $groupedFactory,
        ConfigurableFactory $configurableFactory,
        ProductRepositoryInterface $productRepository,
        ProductAttributeCollection $productAttributeCollectionFactory
    )
    {
        $this->attributeSetRepository = $attributeSetRepositoryInterface;
        $this->categoryRepository = $categoryRepositoryInterface;
        $this->storeManager = $storeManagerInterface;
        $this->stockRegistry = $stockRegistryInterface;
        $this->groupedFactory = $groupedFactory;
        $this->configurableFactory = $configurableFactory;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function createResponse($type, array $productsData)
    {
        $response = [];
        switch ($type) {
            case 'getProduct':
                $response = $this->createProductResponse($productsData[0]);
                break;
            case 'getProducts':
                $response = $this->createProductsResponse($productsData[0]);
                break;
            case 'getProductsCount':
                $response = $this->createProductsCountResponse($productsData[0]);
                break;
            default:
                break;
        }

        return $response;
    }

    /**
     * Method creates response for one product API request
     *
     * @param ProductInterface $product
     *
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createProductResponse(ProductInterface $product)
    {
        return $this->prepareResponseData($product);
    }

    /**
     * Method creates response for list of products API request
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $products
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createProductsResponse(Collection $productsCollection)
    {
        $preparedProducts = [];
        $productsCollection->addAttributeToSelect('*');
        $productsCollection->addMediaGalleryData();
        $products = $productsCollection->getItems();
        foreach ($products as $product) {
            $preparedProducts[] = $this->prepareResponseData($product);
        }

        return $preparedProducts;
    }

    /**
     * Method creates response for product's amount API request
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productsCollection
     *
     * @return int
     */
    private function createProductsCountResponse(Collection $productsCollection)
    {
        return $productsCollection->getSize();
    }

    /**
     * Method forms the response array
     *
     * @param $product
     *
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareResponseData(ProductInterface $product)
    {
        $productData = [];
        $productData = $this->prepareBasicParams($product, $productData);
        $productData = $this->prepareCategoryParams($product, $productData);
        $productData = $this->prepareChildSkuParams($product, $productData);
        $productData = $this->prepareParentSkuParams($product, $productData);
        $productData = $this->prepareImageParams($product, $productData);
        $productData = $this->prepareAdditionalAttributesParams($product, $productData);
        return $productData;
    }

    /**
     * Method retrieves and forms information about category of the product for response
     *
     * @param ProductInterface $product
     * @param array            $productData
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareCategoryParams(ProductInterface $product, array $productData)
    {
        $categoryIds = $product->getCategoryIds();
        $categoryName = [];
        $categoryParentId = [];
        $categoryParentName = [];
        $categoryParentNameArray = [];
        foreach ($categoryIds as $categoryId) {
            $category = $this->categoryRepository->get($categoryId);
            $categoryName[] = $category->getName();
            $path = $category->getPath();
            $ids = explode('/', $path);
            array_shift($ids);
            $categoryParentId = implode('/', $ids);
            foreach ($ids as $key => $value) {
                $childCategory = $this->categoryRepository->get($value);
                $categoryParentName[$key] = $childCategory->getName();
            }
            $categoryParentNameArray = implode('/', $categoryParentName);
        }

        $productData['category_breadcrumb'] = implode('/', $categoryName);
        $productData['category_id'] = implode('/', $categoryIds);
        $productData['category_parent_id'] = $categoryParentId;
        $productData['category_parent_name'] = $categoryParentNameArray;
        return $productData;
    }

    /**
     * Method responsible for the image params allocating
     *
     * @param ProductInterface $product
     * @param array            $productData
     *
     * @return mixed
     */
    private function prepareImageParams(ProductInterface $product, array $productData)
    {
        $imgFolder = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
        $baseImageUrl = $imgFolder . $product->getImage();
        $galleryImages = [];
        $images = $product->getMediaGalleryImages();
        if ($images) {
            foreach ($images as $image) {
                $galleryImages[] = $image->getUrl();
            }
        }
        if (($key = array_search($baseImageUrl, $galleryImages)) !== false) {
            unset($galleryImages[$key]);
        }
        $productData['gallery_images'] = array_values($galleryImages);

        $productData['image_path'] = $baseImageUrl;
        $productData['image_url'] = $baseImageUrl;
        $productData['image_url_small'] = $imgFolder.$product->getSmallImage();
        $productData['image_url_thumbnail'] = $imgFolder.$product->getThumbnail();
        return $productData;
    }

    /**
     * Method adds to response all customer created attributes.
     *
     * @param ProductInterface $product
     * @param array            $productData
     *
     * @return mixed
     */
    private function prepareAdditionalAttributesParams(ProductInterface $product, array $productData)
    {
        $productAttributes = $this->productAttributeCollectionFactory->create()->load();
        foreach ($productAttributes as $attribute) {
            $attributeName = strtolower($attribute->getName());
            $aType = $attribute->getFrontendInput();

            if ($aType === 'price') { // Get the price and the final price (after discounts)
                $attributeValue = $product->getData($attributeName);
                $productData[$attributeName] = number_format($attributeValue, '2', '.', '');
            }

            if ($aType === 'text' || $aType === 'textarea') {
                $attributeValue = $product->getData($attributeName);
                $productData[$attributeName] = $attributeValue;
            }

            if (in_array($aType, ['select', 'multiselect', 'boolean', 'swatch_visual', 'swatch_text'])
                && $attributeName != 'quantity_and_stock_status') {
                $attributeValue = $product->getAttributeText($attributeName);
                $productData[$attributeName] = is_object($attributeValue) ? (string)$attributeValue : $attributeValue;
            }
        }
        return $productData;
    }

    /**
     * Method forms parent SKUs for simple product in case the simple product is part of some composite product type
     *
     * @param  ProductInterface $product
     * @param array             $productData
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareParentSkuParams(ProductInterface $product, array $productData)
    {
        if ($product->getTypeId() == "simple") {
            $parentIds = $this->groupedFactory->create()->getParentIdsByChild($product->getId());
            if (!$parentIds) {
                $parentIds = $this->configurableFactory->create()->getParentIdsByChild($product->getId());
            }

            if (isset($parentIds[0])) {
                $parentSku = $this->productRepository->getById($parentIds[0])->getSku();
                $productData['parent_sku'] = $parentSku;
            }
        }
        return $productData;
    }

    /**
     * Method adds information about child products for configurable type of the product
     *
     * @param ProductInterface $product
     * @param array            $productData
     *
     * @return mixed
     */
    private function prepareChildSkuParams(ProductInterface $product, array $productData)
    {
        $associatedIds = [];
        if ($product->getTypeId() == 'configurable') {
            $children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($children as $child) {
                $associatedIds[] = $child->getID();
            }
            $productData['child_skus'] = implode(',', $associatedIds);
        }
        return $productData;
    }

    /**
     * Method responsible for adding basic attributes (static that belongs to the product entity) to the response.
     *
     * @param  ProductInterface $product
     * @param array             $productData
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareBasicParams(ProductInterface $product, array $productData)
    {
        $productData['attribute_set_id'] = $product->getAttributeSetId();
        $attributeSetRepository = $this->attributeSetRepository->get($product->getAttributeSetId());
        $productData['attribute_set_name'] = $attributeSetRepository->getAttributeSetName();
        $productData['custom_design_from'] = $product->getCustomDesignFrom();
        $productData['custom_design_to'] = $product->getCustomDesignTo();
        $productData['entity_id'] = $product->getId();

        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        $productData['is_in_stock'] = $stockItem->getData('is_in_stock');
        $productData['is_saleable'] = $product->getIsSalable();
        $productData['keywords'] = $product->getMetaKeyword();
        $productData['msrp'] = number_format($product->getMsrp(), '2', '.', ',');
        $productData['news_from_date'] = $product->getNewsFromDate();
        $productData['news_to_date'] = $product->getNewsToDate();

        $productData['price'] = number_format($product->getFinalPrice(), '2', '.', '');
        $productData['quantity'] = number_format($stockItem->getData('qty'), 0, '.', '');
        $productData['shipping_price'] = number_format($product->getShippingAmount(), 2, '.', '');
        $productData['special_from_date'] = $product->getSpecialFromDate();
        $productData['special_price'] = number_format($product->getSpecialPrice(), '2', '.', '');
        $productData['special_to_date'] = $product->getSpecialToDate();
        $productData['store_ids'] = implode(',', $product->getStoreIds());;
        $productData['title'] = $product->getName();
        $productData['type_id'] = $product->getTypeId();
        $productData['url'] = $product->getUrlModel()->getUrl($product);
        $productData['url_path'] = $product->getUrlKey();
        $productData['website_ids'] = implode(',', $product->getWebsiteIds());
        $productData['weight'] = number_format($product->getWeight(), '2', '.', '');
        return $productData;
    }

}