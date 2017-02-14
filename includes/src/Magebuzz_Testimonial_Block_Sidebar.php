<?php
/*
* Copyright (c) 2014 www.magebuzz.com
*/
class Magebuzz_Testimonial_Block_Sidebar extends Mage_Core_Block_Template {		
    
    protected $_count;
	
    public function __construct() {
            parent::__construct();			
            if(Mage::getStoreConfig('testimonial/general_option/testimonial_sidebar_slider')){
                    $this->setTemplate('testimonial/sidebar/slider.phtml');		
            }			
            else {				
                    $this->setTemplate('testimonial/testimonial_sidebar.phtml');		
            }	
            $this->_count = 0;
    }
		
    protected function _prepareLayout() {
            if(Mage::getStoreConfig('testimonial/general_option/testimonial_sidebar_slider')){
                    $this->getLayout()->getBlock('head')->addJs('magebuzz/testimonial/jquery/jquery.min.js');
                    $this->getLayout()->getBlock('head')->addJs('magebuzz/testimonial/jquery/jquery.bxslider.js');
                    $this->getLayout()->getBlock('head')->addJs('magebuzz/testimonial/jquery/testimonial_slider.js');
                    $this->getLayout()->getBlock('head')->addCss('css/magebuzz/testimonial/testimonial_slider.css');
            }			
            return parent::_prepareLayout();
    }
		
    public function getTestimonialsLast(){
            $limit = Mage::helper('testimonial')->getMaxTestimonialsOnSidebar();
            $collection = Mage::getModel('testimonial/testimonial')->getCollection();
            $collection->setOrder('created_time', 'DESC');
            $collection->addFieldToFilter('status',1);
            $collection->setPageSize($limit);
            return $collection;
    }
        
    public function getTestimonialsRand(){
        /**
         * Added by Tal to get Testimonials in Random order
         */
          $limit = Mage::helper('testimonial')->getMaxTestimonialsOnSidebar();
            $collection = Mage::getModel('testimonial/testimonial')->getCollection();
            $this->_count = count($collection->getAllIds());
            $collection->addFieldToFilter('status',1);
            $collection->setOrder('created_time', 'DESC');
            $collection->setPageSize($limit);
            $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
            
            return $collection;
    }

    public function getContentTestimonialSidebar($_description, $count) {
            $short_desc = substr($_description, 0, $count);
             if(substr($short_desc, 0, strrpos($short_desc, ' '))!='') {
                    $short_desc = substr($short_desc, 0, strrpos($short_desc, ' '));
                    $short_desc = $short_desc.'...';
            }
            return $short_desc;
    }
    
    public function getTestimonialCount(){
            return $this->_count;
    }
}