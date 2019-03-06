<?php
/**
 * Copyright 2018 Method Merchant, LLC or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 */

namespace GoDataFeed\FeedManagement\Api;
/**
 * API interface for product export to GDF
 */
interface ProductInterface
{
    /**
     * Method retrieves information about the product with defined id
     *
     * @param string $id of the param.
     *
     * @return mixed - list of the information about the product as the param Value array.
     * @throws \Exception
     */
    public function getProduct($id);

    /**
     * Method retrieves information about the list of the product. Supports next optional filters:
     * website
     * type
     * store
     * status
     * category_id
     * limit
     * order_field
     * order_direction
     *
     * @return mixed - list of the information about the products as the param Value array.
     * @throws \Exception
     */
    public function getProducts();

    /**
     * Method retrieves information about the amount of the product. Supports next optional filters:
     * website
     * type
     * store
     * status
     * category_id
     *
     * @return string - amount of the products.
     * @throws \Exception
     */
    public function getProductsCount();
}
