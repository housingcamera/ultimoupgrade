<?php

/**
 * @author 
 * Router for deciding to go on party checkout or regular onestepcheckout
 */
class CEC_VolusionRewrites_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard {
    // test  - http://127.0.0.1/housingcamera/Underwater-Camera-Housings-for-Nikon-DSLR--s/22.htm
    /**
     * Match the request
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {
       //checking before even try to find out that current module
       //should use this router   
       if (!$this->_beforeModuleMatch()) {
            return false;
        }
       
        $this->fetchDefault();
 
        $path = trim($request->getPathInfo(), '/');
        
        //Identify Volusion URL
        
        preg_match("/(.*)\-p\/(.*)\.htm(.*)/",$path,$m);
        if (isset($m[2])){
           /* Mage::log("Path : " . $path,null,'rewrite.log');
            Mage::log("M 0 : " . $m[0],null,'rewrite.log');
            Mage::log("M 1 : " . $m[1],null,'rewrite.log');
            Mage::log("M 2 : " . $m[2],null,'rewrite.log'); */

            $product =  Mage::getModel('catalog/product')->loadByAttribute('sku',$m[2]);
            if ($product){
                $url = $product->getProductUrl();
                 Mage::app()->getFrontController()->getResponse()
                ->setRedirect($url,301)
                ->sendResponse();
                  exit;
            }else{
                return false;
            }
            //Mage::log("New Url : " . $url,null,'rewrite.log');
        }else return false;
    }
}
