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
/**
 * Interface ValidatorInterface responsible for input params validation
 * @package GoDataFeed\FeedManagement\Model\Product
 */
interface ValidatorInterface
{
    /**
     * Method validates request params
     * Either returns TRUE (success) or throws error (validation failed)
     *
     * @param array $params
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return boolean
     */
    public function validate(array $params);
}