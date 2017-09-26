<?php
namespace GoDataFeed\Products\Api;
 
interface ProductInterface
{
	/**
     * @param string $id of the param.
     * @return mixed|string of the param Value.
     */
    public function getProduct($id);
    /**
     * @return array of the param Value.
     */
    public function getProducts();
    /**
     * @return mixed|string of the param Value.
     */
    public function getProductsCount();
}
