<?php
/**
 * Copyright (c) 2016-present, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the code directory.
 */

if (file_exists(__DIR__.'/../../lib/fb.php')) {
  include_once __DIR__.'/../../lib/fb.php';
} else {
  include_once __DIR__.'/../../../../Facebook_AdsToolbox_lib_fb.php';
}

class Facebook_AdsToolbox_Adminhtml_FbmainController
  extends Mage_Adminhtml_Controller_Action {

  public function indexAction() {
    $this->loadLayout();
    $this->_setActiveMenu('facebook_ads_toolbox');
    $this->renderLayout();
  }

  public function ajaxAction() {
    try {
      $msg = Mage::helper('core/url')->getCurrentUrl();
      FacebookAdsToolbox::log("Set Settings Request : ".$msg);

      $dia_setting_id = $this->getRequest()->getParam('diaSettingId');
      if ($dia_setting_id) {
        Mage::getModel('core/config')->saveConfig(
          'facebook_ads_toolbox/dia/setting/id',
          $dia_setting_id
        );
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(
          Mage::helper('core')->jsonEncode(array('success' => true))
        );
      } else {
        $this->reportFailure($dia_setting_id, null);
      }
    } catch (Exception $e) {
      $this->reportFailure($dia_setting_id, $e);
    }
  }

  private function reportFailure($dia_setting_id, $e) {
    if ($e) {
      FacebookAdsToolbox::logException($e);
    }
    $msg = Mage::helper('core/url')->getCurrentUrl();
    FacebookAdsToolbox::log("Set Settings Failure : ".$msg." ".$dia_setting_id);

    Mage::throwException(
      'Set DIA setting ID failed:'.($dia_setting_id ?: 'null')
    );
  }
}
