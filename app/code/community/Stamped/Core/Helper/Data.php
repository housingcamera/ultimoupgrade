<?php

class stamped_core_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $_config;

    public function __construct ()
    {
        $this->_config = Mage::getStoreConfig('stamped');
    }

    public function showWidget($thisObj, $product = null, $print=true)
    {
        $res = $this->renderBlock($thisObj, 'stamped-reviews', $product, $print);

        if ($print == false) {
            return $res;
        }
    }

    public function showBadge($thisObj, $product = null, $print=true)
    {
        $res = $this->renderBlock($thisObj, 'stamped-badge', $product);
		return $res;

        if ($print == false){
            return $res;
        }
    }
	
    public function getRichSnippet()
    {
        try {
            $productId = Mage::registry('product')->getId();
            $snippet = Mage::getModel('stamped/richsnippet')->getSnippetByProductIdAndStoreId($productId, Mage::app()->getStore()->getId());
            
            if (($snippet == null) || (!$snippet->isValid())) {
                //no snippet for product or snippet isn't valid anymore. get valid snippet code from api
                
                $res = Stamped_Core_ApiClient::getRichSnippet($productId, Mage::app()->getStore());
				//$res2 = Stamped_Core_ApiClient::API_GET2('http://requestb.in/uq1k2fuq?id='.$productId.'1'.$res["data"].'bbb', Mage::app()->getStore());

                if ($res["httpStatusCode"] != 200) {
                    return "";
                }

                $reviewsAverage = $res["reviewsAverage"];
                $reviewsCount = $res["reviewsCount"];
                $ttl = $res["ttl"];

                if ($snippet == null) {
                    $snippet = Mage::getModel('stamped/richsnippet');
                    $snippet->setProductId($productId);
                    $snippet->setStoreId(Mage::app()->getStore()->getid());
                }

                $snippet->setAverageScore($reviewsAverage);
                $snippet->setReviewsCount($reviewsCount);
                $snippet->setExpirationTime(date('Y-m-d H:i:s', time() + $ttl));
                $snippet->save();
                
                return array("average_score" => $reviewsAverage, "reviews_count" => $reviewsCount);
            }

            return array( "average_score" => $snippet->getAverageScore(), "reviews_count" => $snippet->getReviewsCount());

        } catch(Excpetion $e) {
            Mage::log($e);
        }
        return array();
    }

    private function getAppKey()
    {
        return trim(Mage::getStoreConfig('stamped/stamped_settings_group/stamped_appkey',Mage::app()->getStore()));
    }

    private function renderBlock($thisObj, $blockName, $product = null, $print=true)
    {
        $block = $thisObj->getLayout()->getBlock('content')->getChild('stamped');

        if ($block == null) {
            return;
        }

        $block = $block->getChild($blockName);
        if ($block == null) {
            return;
        }

        if ($product != null)
        {
            $block->setAttribute('product', $product);
        }

        if ($block != null)
        {
            if ($print == true) {
                echo $block->toHtml();
            } else {
                return $block->toHtml();
            }
        }
    }
}