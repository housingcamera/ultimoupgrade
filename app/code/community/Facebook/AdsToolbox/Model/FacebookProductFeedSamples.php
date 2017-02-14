<?php
/**
 * Copyright (c) 2016-present, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the code directory.
 */

if (file_exists(__DIR__.'/../lib/fb.php')) {
  include_once __DIR__.'/../lib/fb.php';
} else {
  include_once 'Facebook_AdsToolbox_lib_fb.php';
}

if (file_exists(__DIR__.'/FacebookProductFeed.php')) {
  include_once 'FacebookProductFeed.php';
} else {
  include_once 'Facebook_AdsToolbox_Model_FacebookProductFeed.php';
}

class FacebookProductFeedSamples extends FacebookProductFeed {

  public function generate() {
    $MAX = 12;
    $this->conversion_needed = false;

    $results = array();

    $products = Mage::getModel('catalog/product')->getCollection()
      ->addAttributeToSelect('*')
      ->addAttributeToFilter('visibility',
          array(
            'neq' =>
              Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
          )
      )
      ->addAttributeToFilter('status',
          array(
            'neq' =>
              Mage_Catalog_Model_Product_Status::STATUS_DISABLED
          )
      )
      ->setPageSize($MAX)
      ->setCurPage(0);

    foreach ($products as $product) {
      $results[] = $this->buildProductEntry($product);
    }

    return $results;
  }
}
