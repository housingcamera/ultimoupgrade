<?php

class Stamped_Core_ApiClient
{
	const STAMPED_API_KEY_CONFIGURATION = 'stamped/stamped_settings_group/stamped_apikey';
	const STAMPED_API_SECRET_CONFIGURATION = 'stamped/stamped_settings_group/stamped_apisecret';
	const STAMPED_STORE_URL_CONFIGURATION = 'stamped/stamped_settings_group/stamped_storeurl';

	const STAMPED_SECURED_API_URL_DEVELOPMENT = "http://requestb.in/102buqg1";
	const STAMPED_UNSECURED_API_URL_DEVELOPMENT = "http://requestb.in/102buqg1";
	
	const STAMPED_SECURED_API_URL = "https://%s:%s@stamped.io/api/%s";
	
	public static function isConfigured($store)
	{
		//check if both app_key and secret exist
		if((self::getApiKey($store) == null) or (self::getApiSecret($store) == null))
		{
			return false;
		}

		return true;
	}

	public static function getOrderProductsData($order) 
	{
        Mage::app()->setCurrentStore($order->getStoreId());

        $products = $order->getAllVisibleItems(); //filter out simple products
		$products_arr = array();
		
		foreach ($products as $product) {
			//use configurable product instead of simple if still needed
            $full_product = Mage::getModel('catalog/product')->load($product->getProductId());

            $configurable_product_model = Mage::getModel('catalog/product_type_configurable');
            $parentIds= $configurable_product_model->getParentIdsByChild($full_product->getId());
            if (count($parentIds) > 0) {
            	$full_product = Mage::getModel('catalog/product')->load($parentIds[0]);
            }

			$product_data = array();

			$product_data['productId'] = $full_product->getId();
			$product_data['productDescription'] = Mage::helper('core')->htmlEscape(strip_tags($full_product->getDescription()));
			$product_data['productTitle'] = $full_product->getName();
			try 
			{
				$product_data['productUrl'] = $full_product->getUrlInStore(array('_store' => $order->getStoreId()));
				$product_data['productImageUrl'] = $full_product->getImageUrl();
			} catch(Exception $e) {}
			
			$product_data['productPrice'] = $product->getPrice();

			$products_arr[] = $product_data;
		}

		return $products_arr;
	}

	public static function API_POST($path, $data, $store, $timeout=30) {
	
                try {
			$encodedData = json_encode($data);
			$ch = curl_init(self::getApiUrlAuth($store).$path);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($encodedData),
			));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$result = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			if (in_array($httpCode, array(200, 201))) {
				return json_decode($result, true);
			}
			if (400 === $httpCode) {
				$result = json_decode($result, true);
			}
			if (401 === $httpCode) {
				throw new Exception('API Key or API Secret is invalid, please do check. If you need any assistance, please contact us.');
			}
			
                } catch (Exception $e) {
                    Mage::log('Failed execute API Post. Error: '.$e);

					return;
                }
	}
	
	public static function API_GET2($path, $store, $timeout=30) {
	try {
			$ch = curl_init($path);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$result = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			if (in_array($httpCode, array(200, 201))) {
				return json_decode($result, true);
			}
			if (400 === $httpCode) {
				$result = json_decode($result, true);
			}
			if (401 === $httpCode) {
				throw new Exception('API Key or API Secret is invalid, please do check. If you need any assistance, please contact us.');
			}
			
                } catch (Exception $e) {
                    Mage::log('Failed execute API Get. Error: '.$e);

					return;
                }
	}

	public static function API_GET($path, $store, $timeout=30) 
	{
		try {
			//  Initiate curl
			$ch = curl_init();
			// Disable SSL verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			// Will return the response, if false it print the response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// Set the url
			curl_setopt($ch, CURLOPT_URL,self::getApiUrlAuth($store).$path);
			// Execute
			$result=curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			// Closing
			curl_close($ch);

			//$encodedData = json_encode($result);
			//return $encodedData;

			if (in_array($httpCode, array(200, 201))) {
				return json_decode($result, true);
			}
			if (400 === $httpCode) {
				$result = json_decode($result, true);
			}
			if (401 === $httpCode) {
				throw new Exception('API Key or API Secret is invalid, please do check. If you need any assistance, please contact us.');
			}
			
			
                } catch (Exception $e) {
                    Mage::log('Failed execute API Get. Error: '.$e);

					return;
                }
	}

    public static function getApiKey($store)
    {
        return (Mage::getStoreConfig(self::STAMPED_API_KEY_CONFIGURATION, $store));
    }
	
    public static function getApiSecret($store)
    {
        return (Mage::getStoreConfig(self::STAMPED_API_SECRET_CONFIGURATION, $store));
    }
	
    public static function getApiStoreUrl($store)
    {
		$store_url = (Mage::getStoreConfig(self::STAMPED_STORE_URL_CONFIGURATION, $store));
		if (!$store_url){
			$store_url = Mage::app()->getStore($store->getId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
		}

        return $store_url;
    }

	public static function getApiUrlAuth($store)
	{
		$apiKey = self::getApiKey($store);
		$apiSecret = self::getApiSecret($store);
		$store_url = self::getApiStoreUrl($store);

		return sprintf(self::STAMPED_SECURED_API_URL, $apiKey, $apiSecret, $store_url); 
	}
	
	public static function getRichSnippet($productId, $store)
	{
       	return self::API_GET("/richsnippet?productId=".$productId, $store);
	}

	public static function createReviewRequest($order, $store)
	{
       	return self::API_POST("/survey/reviews", $order, $store);
	}

	public static function createReviewRequestBulk($orders, $store)
	{
       	return self::API_POST("/survey/reviews/bulk", $orders, $store);
	}
}