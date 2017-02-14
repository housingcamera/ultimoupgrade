<?php
/**
 * Copyright (c) 2016-present, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the code directory.
 */

if (file_exists(__DIR__.'/common.php')) {
  include_once 'common.php';
} else {
  include_once 'Facebook_AdsToolbox_Block_common.php';
}

class Facebook_AdsToolbox_Block_ViewCategory
  extends Facebook_AdsToolbox_Block_Common {

  public function getCategory() {
    return $this->escapeQuotes(
      Mage::getSingleton('catalog/layer')->getCurrentCategory()->getName());
  }
}
