<?php
/**
 * Copyright (c) 2016-present, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the code directory.
 */

if (file_exists(__DIR__.'/FacebookProductFeed.php')) {
  include_once 'FacebookProductFeed.php';
} else {
  include_once 'Facebook_AdsToolbox_Model_FacebookProductFeed.php';
}

if (file_exists(__DIR__.'/FacebookProductFeedTSV.php')) {
  include_once 'FacebookProductFeedTSV.php';
} else {
  include_once 'Facebook_AdsToolbox_Model_FacebookProductFeedTSV.php';
}

if (file_exists(__DIR__.'/FacebookProductFeedXML.php')) {
  include_once 'FacebookProductFeedXML.php';
} else {
  include_once 'Facebook_AdsToolbox_Model_FacebookProductFeedXML.php';
}

if (file_exists(__DIR__.'/FacebookProductFeedSamples.php')) {
  include_once 'FacebookProductFeedSamples.php';
} else {
  include_once 'Facebook_AdsToolbox_Model_FacebookProductFeedSamples.php';
}

class Facebook_AdsToolbox_Model_Observer {

  public function addToCart($observer) {
    if (!session_id()) { return; }
    $productId = $observer->getProduct()->getId();
    $session = Mage::getSingleton("core/session",  array("name"=>"frontend"));
    $addToCartArray = $session->getData("fbms_add_to_cart") ?: array();
    $addToCartArray[] = $productId;
    $session->setData("fbms_add_to_cart", $addToCartArray);
  }

  public function estimateFeedGenerationTime() {
    $supportzip = extension_loaded('zlib');
    $format = Mage::getStoreConfig(
      FacebookProductFeed::PATH_FACEBOOK_ADSTOOLBOX_FEED_GENERATION_FORMAT
    ) ?: 'TSV';
    $feed = ($format === 'TSV') ? new FacebookProductFeedTSV() :
                                  new FacebookProductFeedXML();

    // Estimate = MAX (Appx Time to Gen 500 Products + 30 , Last Runtime + 20)
    $time_estimate = $feed->estimateGenerationTime();
    $time_previous_avg =
      Mage::getStoreConfig('facebook_ads_toolbox/dia/feed/runtime_avg') + 20.0;
    return max($time_estimate, $time_previous_avg);
  }

  private function _createFileLockForFeedPath($feedpath) {
    $lock_path = $feedpath.'.lck';
    $fp = fopen($lock_path, 'w');
    fclose($fp);
  }

  private function _removeFileLockForFeedPath($feedpath) {
    $lock_path = $feedpath.'.lck';
    unlink($lock_path);
  }

  private function _isFileStaleLockedForFeedPath($feedpath) {
    $lock_path = $feedpath.'.lck';
    if (file_exists($lock_path)) {
      if (FacebookProductFeed::fileIsStale($lock_path)) {
        return 'stale_lock';
      } else {
        return 'fresh_lock';
      }
    } else {
      return 'no_lock';
    }
  }

  public function internalGenerateFacebookProductFeed(
    $throwException = false,
    $checkCache = true
  ) {
    FacebookProductFeed::log('feed generation start...');
    $time_start = time();
    $supportzip = extension_loaded('zlib');
    $format = Mage::getStoreConfig(
      FacebookProductFeed::PATH_FACEBOOK_ADSTOOLBOX_FEED_GENERATION_FORMAT
    ) ?: 'TSV';
    $feed = ($format === 'TSV') ? new FacebookProductFeedTSV() :
                                  new FacebookProductFeedXML();
    $feed_target_file_path = $feed->getTargetFilePath($supportzip);

    if ($checkCache) {
      $isstale = $feed->cacheIsStale($supportzip);
      $lock_status =
        $this->_isFileStaleLockedForFeedPath($feed_target_file_path);
      if (($lock_status ==  'no_lock') && !$isstale) {
        $time_end = time();
        FacebookProductFeed::log(
          sprintf(
            'feed files are fresh and complete, skip generation, '.
            'time used: %d seconds',
            ($time_end - $time_start)));
        return array($format, $feed, $supportzip);
      } else if ($lock_status == 'fresh_lock') {
        if ($throwException) {
          throw new Exception(
            sprintf('Lock is fresh, generation must be in process.')
          );
        } else {
          FacebookProductFeed::log(
            sprintf('Lock is fresh, generation must be in process.')
          );
          return;
        }
      }
      // no_lock & stale feed, or stale_lock, we will regen the feed
    }

    try {
      $this->_createFileLockForFeedPath($feed_target_file_path);
      $feed->save();
      if ($supportzip) {
        $feed->saveGZip();
      }
    } catch (\Exception $e) {
      FacebookProductFeed::log(sprintf(
        'Caught exception: %s. %s', $e->getMessage(), $e->getTraceAsString()
      ));
      if ($supportzip) {
        $feed->saveGZip();
      }
      if ($throwException) {
        throw $e;
      }
      return;
    }
    $this->_removeFileLockForFeedPath($feed_target_file_path);

    $time_end = time();
    $feed_gen_time = ($time_end - $time_start);
    FacebookProductFeed::log(
      sprintf(
        'feed generation finished, time used: %d seconds',
        $feed_gen_time));

    // Update feed generation online time estimate w/ 25% decay.
    $old_feed_gen_time =
      Mage::getStoreConfig('facebook_ads_toolbox/dia/feed/runtime_avg');
    if ($feed_gen_time < $old_feed_gen_time) {
      $feed_gen_time = $feed_gen_time * 0.25 + $old_feed_gen_time * 0.75;
    }

    Mage::getModel('core/config')->saveConfig(
      'facebook_ads_toolbox/dia/feed/runtime_avg',
      $feed_gen_time
    );
    return array($format, $feed, $supportzip);
  }

  public function generateFacebookProductFeed($schedule) {
    $this->internalGenerateFacebookProductFeed();
  }

  public function generateFacebookProductSamples() {
    $feed = new FacebookProductFeedSamples();
    return $feed->generate();
  }

  public function disableCache($observer) {
    $controller_name =
      $observer->getEvent()->getControllerAction()->getFullActionName();

    // Clear cache for FB controllers.
    if (strpos($controller_name, 'adminhtml_fb') !== false) {
      Mage::app()->getCacheInstance()->cleanType('config');
    }
  }
}
