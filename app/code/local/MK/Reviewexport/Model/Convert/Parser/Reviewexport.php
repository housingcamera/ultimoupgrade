<?php
class MK_Reviewexport_Model_Convert_Parser_Reviewexport extends Mage_Eav_Model_Convert_Parser_Abstract
{
    const MULTI_DELIMITER = ' , ';
   
     public function unparse()
    {
           $reviews = Mage::getModel('review/review')->getResourceCollection()
                    ->setDateOrder()
                    ->addRateVotes()
                    ->load();
                    $csv_fields  = array();

                    $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        foreach($reviews as $review)
        {
             $sku = $write->query('select sku from `catalog_product_entity` where entity_id = "'.$review->getEntity_pk_value().'" ');
             $sku = $sku->fetch();
          
             if($sku){
                $ratingCollection = Mage::getModel('rating/rating_option_vote')
                        ->getResourceCollection()
                        ->setReviewFilter($review->getId());
                                        
                $rating_val = "";
                $option = "";
                $option_value = '';
                foreach($ratingCollection as $rating)
                {
                                     
                    $option =  $rating->getOptionId();                        
                    $rating_val = $rating->getRatingId(); 
                    
                    if(!empty($option_value) && $option_value != '') 
                        $option_value = $option_value."@".$rating_val.":".$option; 
                     else
                        $option_value = $rating_val.":".$option;
                        
                }
        
                $csv_fields['created_at'] = $review->getCreated_at();
                $csv_fields['Sku'] = $sku['sku'];                
                $csv_fields['status_id'] = $review->getStatus_id();
                $csv_fields['title'] = $review->getTitle();
                $csv_fields['detail'] = $review->getDetail();
                $csv_fields['nickname'] = $review->getNickname();
                $csv_fields['customer_id'] = $review->getCustomer_id();
                $csv_fields['option_id'] = $option_value;
                $csv_fields['entity_id'] = $review->getId();
                            
             }
             
              $batchExport = $this->getBatchExportModel()
                        ->setId(null)
                        ->setBatchId($this->getBatchModel()->getId())
                        ->setBatchData($csv_fields)
                        ->setStatus(1)
                        ->save();
          }
          
     return $this;
}
     public function parse()
    {
            
    }
}