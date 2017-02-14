<?php
/**
 * Magento
 *
 * @category   Flagbit
 * @package    Flagbit_FeedReader
 * @copyright  Copyright (c) 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 */

/**
 * Feed reader sidebar
 *
 * @category   Flagbit
 * @package    Flagbit_FeedReader
 * @author     David Fuhr <fuhr@flagbit.de>
 */
class CEC_RssContent_Block_Faq extends CEC_RssContent_Block_Abstract
{
	/**
	 * The constructor
	 * 
	 * @return Flagbit_FeedReader_Block_Sidebar
	 */
	public function __construct()
	{
		parent::__construct();
		
		// set the template
		$this->setTemplate('rsscontent/faq.phtml');
                
	}
}
