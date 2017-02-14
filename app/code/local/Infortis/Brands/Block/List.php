<?php
/**
 * List of brands
 */

class Infortis_Brands_Block_List extends Infortis_Brands_Block_Abstract
{
	const CACHE_TAG = 'brands_list';

	/**
	 * Block cache key informative items
	 *
	 * @var array
	 */
	protected $_cacheKeyArray = NULL;


	/* /////////////////////////////////////////////////////////////////////////////// */


	/**
	 * Brand collection
	 *
	 * @var array
	 */
	protected $_brandCollection = NULL;

	/**
	 * Cache key of requested brand collection (brands selected by admin)
	 * Uniqe identifier of brand collection in cache.
	 *
	 * @var string
	 */
	protected $_collectionCacheKey = NULL;

	/**
	 * Cache key of final brand collection (brands which will be rendered)
	 *
	 * @var string
	 */
	protected $_finalCollectionCacheKey = NULL;

	/**
	 * Selected brands string (from param or from global config)
	 *
	 * @var string
	 */
	protected $_selectedBrandsString = NULL;

	/**
	 * Flag: use all brands or selected brands
	 *
	 * @var array
	 */
	protected $_flagUseAllBrands = true;

	/**
	 * Brand URL keys
	 *
	 * @var array
	 */
	protected $_urlKeys = NULL;

	/**
	 * Cache tags
	 *
	 * @var array
	 */
	protected $_collectionCacheTags = array(Mage_Eav_Model_Entity_Attribute::CACHE_TAG, self::CACHE_TAG);

	/**
	 * Resource initialization
	 */
	protected function _construct()
	{
		///Mage::log('_construct'); ///
		parent::_construct();

		$this->addData(array(
			'cache_lifetime'    => 31536000,
			'cache_tags'        => $this->_collectionCacheTags,
			//'cache_tags'        => array(Mage_Eav_Model_Entity_Attribute::CACHE_TAG),
			//'cache_tags'        => array(Mage_Cms_Model_Block::CACHE_TAG),
			//'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG),
		));

		$this->_flagUseAllBrands = true;

		$this->_prepareTheCollection();
	}

	/**
	 * Get cache key informative items
	 *
	 * @return array
	 */
	public function getCacheKeyInfo()
	{
		///Mage::log('getCacheKeyInfo: List'); ///
		if (NULL === $this->_cacheKeyArray)
		{
			///Mage::log('getCacheKeyInfo -- KEY===NULL'); ///
			$this->_cacheKeyArray = array(
				'BRANDS_LIST',
				Mage::app()->getStore()->getId(),
				$this->getTemplateFile(),
				'template' => $this->getTemplate(),
				(int)Mage::app()->getStore()->isCurrentlySecure(),

				$this->getBrandAttributeId(),
				$this->_getFinalCollectionCacheKey(),
				//IMPORTANT: Tutaj powinien byc tylko hash z kolekcji wynikowej aby nie odswiezac cache calego bloku zbyt czesto.
			);
		}

		//
		///Mage::log('getCacheKeyInfo -- KEY md5= ' . md5(implode("+", $this->_cacheKeyArray))); ///
		//
		return $this->_cacheKeyArray;
	}

	/**
	 * Get collection id
	 *
	 * @return string
	 */
	protected function _getFinalCollectionCacheKey()
	{
		///Mage::log('_getFinalCollectionCacheKey'); ///
		if (NULL === $this->_finalCollectionCacheKey)
		{
			$this->_prepareTheCollection();
		}
		return $this->_finalCollectionCacheKey;
	}

	/**
	 * Get cache key of brand collection (uniqe identifier of brand collection in cache)
	 *
	 * @return string
	 */
	protected function _getCollectionCacheKey()
	{
		///Mage::log('_getCollectionCacheKey'); ///
		if (NULL === $this->_collectionCacheKey)
		{
			$this->_prepareTheCollection();
		}
		return $this->_collectionCacheKey;
	}



	/**
	 * ///////////////////////////////////////////////////////////////////////////////
	 * Prepare collection
	 */
	protected function _prepareTheCollection()
	{
	///Mage::log('>>> _prepareTheCollection'); ///

		$this->_prepareSelectedBrandsAndFlags();
		$this->_prepareCollectionCacheKey();
		$brands = $this->_getBrandCollection();
		$this->_finalCollectionCacheKey = md5(implode("+", $brands));

	///Mage::log('<<< _prepareTheCollection -- _finalCollectionCacheKey= ' . $this->_finalCollectionCacheKey); ///
	}

	/**
	 * Prepare flag: use all brands or selected brands.
	 * Prepare string with selected brands.
	 * Important: this method has to be called before other methods which prepare the collection
	 */
	protected function _prepareSelectedBrandsAndFlags() //TODO adjust name to functions
	{
		///Mage::log('_prepareSelectedBrandsAndFlags'); ///

		//TODO move here retrieving of selected

		//If brand list is provided via parameter, it overrides brands selection from config
		$selectedBrands = $this->getBrands(); //parameter: brands
		if ($selectedBrands === NULL) //Param not set
		{
			if ($this->_helper->getCfg('list/all_brands'))
			{
				$this->_flagUseAllBrands = true;
			}
			else
			{
				$this->_flagUseAllBrands = false;
				$this->_selectedBrandsString = $this->_helper->getCfg('list/brands'); //Get string with brand list from config
			}
		}
		else //Param is set
		{
			$this->_flagUseAllBrands = false;
			$this->_selectedBrandsString = $selectedBrands; //Get string with brand list from parameter
		}
	}

	/**
	 * Prepare collection cache key
	 *
	 * @return string
	 */
	protected function _prepareCollectionCacheKey()
	{
		///Mage::log('_prepareCollectionCacheKey'); ///

		//Other variables
		$key[] = 'brands';
		$key[] = Mage::app()->getStore()->getId();

		//Basic variables
		if ($this->_flagUseAllBrands)
		{
			//If all brands, add empty item
			//$key[] = '';
		}
		else
		{
			//If not all brands, add string with selected brands
			$key[] = $this->_selectedBrandsString;
		}
		$key[] = $this->_helper->getCfg('list/assigned');
		$key[] = $this->_helper->getCfg('list/assigned_in_stock');

		$this->_collectionCacheKey = 'brands-' . md5(implode("|", $key)); //IMPORTANT: has to be hash, too long key will not create cache

		///Mage::log('_prepareCollectionCacheKey -- key= ' . $this->_collectionCacheKey); ///
	}

	/**
	 * Get the collection of brands
	 *
	 * @return array
	 */
	protected function _getBrandCollection()
	{
		///Mage::log('%%%cache%%%'); ///
		if (NULL === $this->_brandCollection)
		{
			///Mage::log('%%%cache%%% -- NULL'); ///
			$cache = Mage::getSingleton('core/cache');
			$key = $this->_getCollectionCacheKey();
			if (! $data = $cache->load($key))
			{
				///Mage::log('%%%cache%%% -- COLLECTION IS NOT IN CACHE !!! !!!'); ///
				$brands = $this->_buildBrandsCollection();
				$this->_brandCollection = $brands;
				///Mage::log('%%%cache%%% -- serialize = ' . serialize($brands)); ///

				//Save in cache
				$data = urlencode(serialize($brands));
				$cache->save($data, $key, $this->_collectionCacheTags, 2592000); //30 days: 3600*24*30

				///Mage::log('%%%cache%%% -- urlencode = ' . $data); ///
			}
			else
			{
				///Mage::log('%%%cache%%% -- COLLECTION GET FROM CACHE = ' . $data); ///

				//Get from cache
				$this->_brandCollection = unserialize(urldecode($data));
			}

			if (!$this->_brandCollection)
			{
				$this->_brandCollection = array();
			}
		}
		return $this->_brandCollection;
	}



	/**
	 * ///////////////////////////////////////////////////////////////////////////////
	 * Create the collection of brands
	 *
	 * @return array
	 */
	protected function _buildBrandsCollection()
	{
		///Mage::log('>>> >>> _buildBrandsCollection'); ///
		$showAssignedToProducts = $this->_helper->getCfg('list/assigned');

		if ($this->_flagUseAllBrands)
		{
			if ($showAssignedToProducts)
			{
				$brands = $this->_getAllBrandsInUse();
			}
			else
			{
				$brands = $this->_getAllBrands();
			}
		}
		else //Only selected brands
		{
			if ($showAssignedToProducts)
			{
				$brands = $this->_getAllBrandsInUse();
				///Mage::log('_buildBrandsCollection -- SELECTED ASSIGNED');

				$selectedBrands = $this->_getSelectedBrands();
				$brands = array_intersect($selectedBrands, $brands);
				///Mage::log('_buildBrandsCollection -- returned brands       -- count = ' . count($brands)); ///
			}
			else
			{
				$brands = $this->_getSelectedBrands();
				///Mage::log('_buildBrandsCollection -- SELECTED !assigned');
				///Mage::log('_buildBrandsCollection -- selectedBrands -- count = ' . count($brands)); ///
			}
		}

		///Mage::log('<<< <<< _buildBrandsCollection'); ///
		return $brands;
	}

	/**
	 * Get selected brands: from param or from global config
	 *
	 * @return array
	 */
	protected function _getSelectedBrands()
	{
		///Mage::log('_getSelectedBrands'); ///
		$brandString = $this->_selectedBrandsString; //$this->_getSelectedBrandsString();

		//Get array of brands from string
		if (!empty($brandString))
		{
			return array_map('trim', explode(',', $brandString));
			//return explode(',', $brandString);
		}
		else
		{
			return array();
		}
	}

	/**
	 * Returns all existing brands
	 *
	 * @return array
	 */
	protected function _getAllBrands()
	{
		///Mage::log('_getAllBrands'); ///

		/*$attributeModel = Mage::getSingleton('eav/config')
			->getAttribute('catalog_product', $this->getBrandAttributeId());*/
			
		/*
		getAllOptions ([bool $withEmpty = true], [bool $defaultValues = false])
			- bool $withEmpty: Add empty option to array
			- bool $defaultValues: Return default values
		*/
		$options = array();
		foreach ($this->_attributeModel->getSource()->getAllOptions(false, true) as $o)
		{
			$options[] = $o['label'];
		}
		
		return $options;
	}
	
	/**
	 * Returns only brands, which are currently assigned to products
	 *
	 * @return array
	 */
	protected function _getAllBrandsInUse()
	{
		///Mage::log('_getAllBrandsInUse'); ///
		$attributeCode = $this->getBrandAttributeId();
		/*$attributeModel = Mage::getSingleton('eav/config')
			->getAttribute('catalog_product', $attributeCode);*/
		
		//Get product collection
		$products = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToSelect($attributeCode)
			->addAttributeToFilter($attributeCode, array('neq' => ''))
			->addAttributeToFilter($attributeCode, array('notnull' => true))
			->addAttributeToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
			->addStoreFilter(Mage::app()->getStore()->getId())
			;

		//TODO: check how to add visibility filter
		//from: Mage_Catalog_Block_Product_New
		//$collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
		//OR:
		//from: Mage_Catalog_Model_Layer
		//Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		//Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

		//Filter brands which are currently assigned to products which are in stock
		if ($this->_helper->getCfg('list/assigned_in_stock'))
		{
			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);
		}

		//Get all (attribute's) values in use
		$attributeValuesInUse = array_unique($products->getColumnValues($attributeCode));

		//Get attribute options (text labels)
		$optionLabels = $this->_attributeModel->getSource()->getOptionText(
			implode(',', $attributeValuesInUse)
			);

		//If only one option retrieved (in that case it is string), convert to array
		if (is_string($optionLabels))
		{
			return array($optionLabels);
		}
		return $optionLabels;
	}



	/**
	 * ///////////////////////////////////////////////////////////////////////////////
	 * Get loaded list of brands
	 * Wrapper for the protected method
	 *
	 * @return array
	 */
	public function getLoadedBrands()
	{
		///Mage::log('FRONTEND getBrandsList'); ///
		return $this->_getBrandCollection();
	}

	/**
	 * Create unique block id for frontend
	 *
	 * @return string
	 */
	public function getFrontendHash()
	{
		return md5(implode("+", $this->getCacheKeyInfo()));
	}



	/**
	 * ///////////////////////////////////////////////////////////////////////////////
	 * Get brand URL key
	 * Override base method. Get URL from already prepared hashtable.
	 *
	 * @param string Brand name
	 * @param string URL separator
	 * @return string
	 */
	public function getBrandUrlKey($brand, $separator)
	{
		if (FALSE === isset($this->_urlKeys[$separator][$brand]))
		{
			$this->_urlKeys[$separator][$brand] = $this->_formatBrandUrlKey($brand, $separator);
		}
		return $this->_urlKeys[$separator][$brand];
	}
}
