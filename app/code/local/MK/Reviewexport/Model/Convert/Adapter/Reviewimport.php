<?php
class MK_Reviewexport_Model_Convert_Adapter_Reviewimport extends Mage_Catalog_Model_Convert_Adapter_Product

{
    public function saveRow( array $data )
    {
          $write = Mage::getSingleton('core/resource')->getConnection('core_write');
          $sku = $write->query('select entity_id from `catalog_product_entity` where sku = "'.$data['Sku'].'" ');
          $sku = $sku->fetch();
                  
    if($sku)
    {
        $product_id = $sku['entity_id'];
             if($data['customer_id']=='')
             {
                $customerid = NULL; 
             }   
             else
             {
                 $customerid = $data['customer_id'];                 
             }
          $_review =   Mage::getModel('review/review')
            ->setCreatedAt($data['created_at'])
            ->setEntityPkValue($product_id)
            ->setEntityId(1)
            ->setStatusId($data['status_id'])
            ->setTitle($data['title'])
            ->setDetail($data['detail'])
            ->setStoreId(1)
             ->setStores(1)
            ->setCustomerId($customerid)
            ->setNickname($data['nickname'])
            ->save();
             if($data['option_id'])
             {
               $arr_data = explode("@",$data['option_id']);
             
                if(!empty($arr_data)) {
                                            
                    foreach($arr_data as $each_data) {
                    
                        $arr_rating = explode(":",$each_data);
                                              
                        if($arr_rating[1] != 0) {
            
                         Mage::getModel('rating/rating')
                            ->setRatingId($arr_rating[0])
                            ->setReviewId($_review->getId())
                            ->setCustomerId($customerid)
                            ->addOptionVote($arr_rating[1], $product_id);
                        }
                    }                                        
                }     
                $_review->aggregate();
             }
               
          }
    }
}