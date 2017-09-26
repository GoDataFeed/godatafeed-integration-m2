<?php
namespace GoDataFeed\Products\Model;

use GoDataFeed\Products\Api\ProductInterface;
use Magento\Catalog\Model\ProductFactory;

class Product implements ProductInterface
{
    protected $_modelProductFactory;
    protected $_categoryFactory;
    protected $_productCollectionFactory;
    protected $attributeRepositoryInterface;
    protected $request;
    protected $stockRegistry;
    protected $attributeSet;
    protected $attributeCollection;
    protected $_storeManager;

    const PAGE_SIZE_DEFAULT = 50;
    const PAGE_SIZE_MAX = 250;
    const RESOURCE_COLLECTION_PAGING_ERROR = 'Resource collection paging error.';
    const RESOURCE_COLLECTION_PAGING_LIMIT_ERROR = 'The paging limit exceeds the allowed number.';
    const RESOURCE_COLLECTION_ORDERING_ERROR = 'Resource collection ordering error.';

    private function _applyCategoryFilter($collection)
    {
        $params=$this->request->getParams();
        if (array_key_exists("category_id",$params)){
            $categoryId = $params['category_id'];
            if($categoryId) {
                $category = $this->_categoryFactory->create()->load($categoryId);
                if (!$category->getId()) {
                    $this->error('category_id');
                }
                $collection->addCategoryFilter($category);
            }
        }

        return $collection;
    }

    private function _applyCollectionCustomModifiers($collection)
    {
        $params=$this->request->getParams();
        if (array_key_exists("page",$params)){
            $page = $params['page'];
            if ($page != abs($page)) {
                return(self::RESOURCE_COLLECTION_PAGING_ERROR);
            }
        } else {
            $page=1;
        }

        if (array_key_exists("limit",$params)){
            $limit = $params['limit'];
            if (null == $limit) {
                $limit = self::PAGE_SIZE_DEFAULT;
            } else {
                if ($limit != abs($limit) || $limit > self::PAGE_SIZE_MAX) {
                    return(self::RESOURCE_COLLECTION_PAGING_LIMIT_ERROR);
                }
            }
        } else {
            $limit = self::PAGE_SIZE_DEFAULT;
        }

        if (array_key_exists("order_field",$params)){$orderField = $params['order_field'];
            if (array_key_exists("order_direction",$params)){$orderDirection =$params['order_direction'];
                if (null !== $orderField) {
                    $attribute = $this->attributeRepositoryInterface->get('catalog_product', $orderField);
                    if (!is_string($orderField)|| !$attribute )
                    {
                        return(self::RESOURCE_COLLECTION_ORDERING_ERROR);
                    }
                    $collection->setOrder($orderField, $orderDirection);
                }
            }
        }

        $collection->setCurPage($page)->setPageSize($limit);
        return $collection;
    }

    private function _getAllAttributes()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $coll = $objectManager->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class);
        $coll->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, 4);
        $attributes = $coll->load()->getItems();
        return $attributes;
    }

    private function _prepareProductForResponse($product, $attributes)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $base_image_url = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        $productData['attribute_set_id'] = $product->getAttributeSetId();
        $attributeSetRepository = $this->attributeSet->get($product->getAttributeSetId());
        $attributeSetName = $attributeSetRepository->getAttributeSetName();
        $productData['attribute_set_name'] = $attributeSetName;

        $cats = $product->getCategoryIds();
        $category_name = array();
        $category_parent_id = array();
        $category_parent_name = array();
        $category_parent_name_arr = array();
        foreach ($cats as $category_id) {
            $_cat = $this->_categoryFactory->create()->load($category_id);
            $category_name[] = $_cat->getName();
            $path = $_cat->getPath();
            $ids = explode('/', $path);
            array_shift($ids);
            $category_parent_id = implode('/', $ids);
            foreach ($ids as $key => $value) {
                $_category = $this->_categoryFactory->create()->load($value);
                $category_parent_name[$key] = $_category->getName();
            }
            $category_parent_name_arr = implode('/', $category_parent_name);
        }

        $productData['category_breadcrumb'] = implode('/', $category_name);
        $productData['category_id'] = implode('/', $cats);
        $productData['category_parent_id'] = $category_parent_id;
        $productData['category_parent_name'] = $category_parent_name_arr;

        $associatedIds = array();
        if ($product->getTypeId() == "configurable") {
            $_children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($_children as $child) {
                $associatedIds[] = $child->getID();
            }
            $productData['child_skus'] = implode(',', $associatedIds);
        }

        $productData['custom_design_from'] = $product->getCustomDesignFrom();
        $productData['custom_design_to'] = $product->getCustomDesignTo();
        $productData['entity_id'] = $product->getId();

        $gallery_images = array();

        if (($key = array_search($base_image_url, $gallery_images)) !== false) {
            unset($gallery_images[$key]);
        }

        $productData['gallery_images'] = array_values($gallery_images);
        $images = $product->getMediaGalleryImages();
        $img = '';
        $sm = '';
        $tn = '';

        foreach ($images as $image) {
            $gallery_images[] = $image->getUrl();
            if ($image->getMediaType() === 'image') {
                $img = $image->getFile();
            }
            if ($image->getMediaType() === 'small_image') {
                $sm = $image->getFile();
            }
            if ($image->getMediaType() === 'thumbnail') {
                $tn = $image->getFile();
            }
        }

        $productData['image_path'] = $img;
        $productData['image_url'] = $base_image_url;
        $productData['image_url_small'] = $sm;
        $productData['image_url_thumbnail'] = $tn;

        $stockItem = $this->stockRegistry->getStockItem($product->getId());

        $productData['is_in_stock'] = $stockItem->getData('is_in_stock');
        $productData['is_saleable'] = $product->getIsSalable();
        $productData['keywords'] = $product->getMetaKeyword();
        $productData['msrp'] = number_format($product->getMsrp(), '2', '.', ',');
        $productData['news_from_date'] = $product->getNewsFromDate();
        $productData['news_to_date'] = $product->getNewsToDate();

        if ($product->getTypeId() == "simple") {
            $parentIds = $objectManager->create('Magento\GroupedProduct\Model\Product\Type\Grouped')->getParentIdsByChild($product->getId());
            if (!$parentIds)
                $parentIds = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($product->getId());

            if (isset($parentIds[0])) {
                $parentSku = $this->_modelProductFactory->create()->load($parentIds[0])->getSku();
                $productData['parent_sku'] = $parentSku;
            }
        }

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

        foreach ($attributes as $attribute) {
            $aType = $attribute->getFrontendInput();
            if ($aType === 'text' || $aType === 'textarea') {
                $attributeName = $attribute->getName();
                $attributeValue = $product->getData($attributeName);
                $productData[$attributeName . '_attribute'] = $attributeValue;
	        }
            if ($aType === 'select' || $aType === 'multiselect' || $aType === 'boolean' || $aType === 'swatch_visual' || $aType === 'swatch_text') {
                $attributeName = $attribute->getName();
                if ($attributeName != 'quantity_and_stock_status') {
                    $attributeValue = $product->getAttributeText($attributeName);
		        if (is_object($attributeValue)) {
                        $productData[$attributeName . '_attribute'] = (string)$attributeValue;
                    } else {
                        $productData[$attributeName . '_attribute'] = $attributeValue;
                    }
                }
            }
        }

        return $productData;
    }

    private function error($data) {
        $error = array();
        switch ($data) {
            case "category_id":
                $message['error'] = array(array('code' => 400, 'message' => 'Category not found.'));
                break;
        }
        $error['messages'] = $message;
        print_r(json_encode($error));
    }

    public function __construct(
        ProductFactory $modelProductFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepositoryInterface,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attributeCollection,
        \Magento\Framework\App\Request\Http $request,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_modelProductFactory = $modelProductFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->attributeRepositoryInterface = $attributeRepositoryInterface;
        $this->request = $request;
        $this->stockRegistry = $stockRegistry;
        $this->attributeSet = $attributeSet;
        $this->attributeCollection = $attributeCollection;
        $this->_storeManager = $storeManager;
    }

    public function getProduct($id) {
        $product = $this->_modelProductFactory->create()->load($id);
        $attributes = $this->_getAllAttributes();
        return $this->_prepareProductForResponse($product,$attributes);
    }

    public function getProducts() {
        $params=$this->request->getParams();
        $products=array();

        $productCollection = $this->_productCollectionFactory->create();
        $productCollection=$this->_applyCollectionCustomModifiers($productCollection);
        $productCollection=$this->_applyCategoryFilter($productCollection);

        if (array_key_exists("store", $params)) {
            $store = $params['store'];
            $productCollection->addStoreFilter($store);
        }

        if (array_key_exists("website", $params)) {
            $website = $params['website'];
            $productCollection->addWebsiteFilter($website);
        }

        if (array_key_exists("type",$params)){
            $type = $params['type'];
            $productCollection->addAttributeToFilter('type_id', array('in' => $type));
        }

        if (array_key_exists("status",$params)){
            $status = $params['status'];
            $productCollection->addAttributeToFilter('status', array('eq' => array($status)));
        }

        $attributes = $this->_getAllAttributes();
        foreach($productCollection as $product) {
            $_product = $this->_modelProductFactory->create()->load($product->getId());
            $products[]=$this->_prepareProductForResponse($_product,$attributes);
        }

        return $products;
    }

    public function getProductsCount() {
        $params=$this->request->getParams();
        $productCollection = $this->_productCollectionFactory->create();

        if (array_key_exists("store", $params)) {
            $store = $params['store'];
            $productCollection->addStoreFilter($store);
        }

        if (array_key_exists("website", $params)) {
            $website = $params['website'];
            $productCollection->addWebsiteFilter($website);
        }

        if (array_key_exists("type",$params)){
            $type = $params['type'];
            $productCollection->addAttributeToFilter('type_id', array('in' => $type));
        }

        if (array_key_exists("status",$params)){
            $status = $params['status'];
            $productCollection->addAttributeToFilter('status', array('eq' => array($status)));
        }

        $productCollection = $this->_applyCategoryFilter($productCollection);
        return count($productCollection);
    }
}