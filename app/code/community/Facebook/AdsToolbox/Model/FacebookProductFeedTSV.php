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

class FacebookProductFeedTSV extends FacebookProductFeed {

  const TSV_FEED_FILENAME = 'facebook_adstoolbox_product_feed.tsv';

// full row should be
// id\ttitle\tdescription\tgoogle_product_category\tproduct_type\tlink\timage_link\tbrand\tcondition\tavailability\tprice\tsale_price\tsale_price_effective_date\tgtin\tbrand\tmpn\titem_group_id\tgender\tage_group\tcolor\tsize\tshipping\tshipping_weight\tcustom_label_0
// ref: https://developers.facebook.com/docs/marketing-api/dynamic-product-ads/product-catalog
  const TSV_HEADER = "id\ttitle\tdescription\tlink\timage_link\tbrand\tcondition\tavailability\tprice\tshort_description\tproduct_type\tgoogle_product_category";

  function __construct() {
    $this->quote_chars = array(
      chr(145),
      chr(146),
      chr(147),
      chr(148),
      "\xe2\x80\x98",
      "\xe2\x80\x99",
      "\xe2\x80\x9c",
      "\xe2\x80\x9d",
    );
    $this->replacement_chars = array("'", "'", '"', '"', "'", "'", '"', '"');
  }

  protected function tsvescape($t) {
    // replace newlines as TSV does not allow multi-line value
    return str_replace(array("\r", "\n", "&nbsp;", "\t"), ' ', $t);
  }

  protected function buildProductAttr($attr_name, $attr_value) {
    return $this->buildProductAttrText($attr_name, $attr_value, 'tsvescape');
  }

  protected function defaultCondition() {
    return 'new';
  }

  protected function getFileName() {
    return self::TSV_FEED_FILENAME;
  }

  protected function buildHeader() {
    // shame that we can not insert any comments in TSV
    return self::TSV_HEADER;
  }

  protected function buildFooter() {
    return null;
  }

  protected function buildProductEntry($product) {
    $items = parent::buildProductEntry($product);
    $imploded_str = implode("\t", array_values($items));
    return str_replace(
      $this->quote_chars,
      $this->replacement_chars,
      $imploded_str);
  }

}
