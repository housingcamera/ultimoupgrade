<?php
/**
 * Infortis UltraMegaMenu - category navigation menu
 */

class Infortis_UltraMegamenu_Block_Navigation extends Mage_Catalog_Block_Navigation
{
	//Constants
	const DDTYPE_NONE			= 0;
	const DDTYPE_MEGA			= 1;
	const DDTYPE_CLASSIC		= 2;
	const DDTYPE_SIMPLE			= 3;

	protected $_ddTypeClass;

	//Base parameters
	protected $_isSimpleSidemenu = FALSE;

	//Additional parameters
	protected $_catBlocksDelimiter;
	
	//previously added:
	protected $_showNumProd = FALSE;
	protected $_tplProcessor = NULL;
	protected $_curCategoryId = NULL;
	//end:NEW

	/**
	 * NEW:	
	 * Resource initialization
	 */
	protected function _construct()
	{
		parent::_construct();
		$this->_ddTypeClass = array(self::DDTYPE_MEGA => "mega", self::DDTYPE_CLASSIC => "classic", self::DDTYPE_SIMPLE => "simple");
		$this->_isSimpleSidemenu = FALSE;
		$this->_catBlocksDelimiter = "#@#";
		$this->_showNumProd = FALSE;
		$this->_tplProcessor = NULL;

		if (Mage::registry('current_category'))
		{
			$this->_curCategoryId = Mage::registry('current_category')->getId();
		}
	}

	/* Added to default method:
	 * - check if current page is home page
	 * - check if secure
	 */
	/**
	 * Get Key pieces for caching block content
	 *
	 * @return array
	 */
	public function getCacheKeyInfo()
	{
		$shortCacheId = array(
			'CATALOG_NAVIGATION',
			Mage::app()->getStore()->getId(),
			Mage::getDesign()->getPackageName(),
			Mage::getDesign()->getTheme('template'),
			Mage::getSingleton('customer/session')->getCustomerGroupId(),
			'template' => $this->getTemplate(),
			'name' => $this->getNameInLayout(),
			$this->getCurrenCategoryKey(),
			Mage::helper('ultramegamenu')->getIsHomePage(),
			(int)Mage::app()->getStore()->isCurrentlySecure(),
		);
		$cacheId = $shortCacheId;

		$shortCacheId = array_values($shortCacheId);
		$shortCacheId = implode('|', $shortCacheId);
		$shortCacheId = md5($shortCacheId);

		$cacheId['category_path'] = $this->getCurrenCategoryKey();
		$cacheId['short_cache_id'] = $shortCacheId;

		return $cacheId;
	}

	/**
	 * Render category to html
	 *
	 * @param array $parentInfo
	 * @param Mage_Catalog_Model_Category $category
	 * @param int Nesting level number
	 * @param boolean Whether ot not this item is last, affects list item class
	 * @param boolean Whether ot not this item is first, affects list item class
	 * @param boolean Whether ot not this item is outermost, affects list item class
	 * @param string Extra class of outermost list items
	 * @param string If specified wraps children list in div with this class
	 * @param boolean Whether ot not to add on* attributes to list item
	 * @return string
	 */
	protected function _renderCategoryMenuItemHtml($category, $level = 0, $isLast = FALSE, $isFirst = FALSE,
		$isOutermost = FALSE, $outermostItemClass = '', $childrenWrapClass = '', $noEventAttributes = FALSE, $parentInfo = null)
	{
		if (!$category->getIsActive()) {
			return '';
		}
		$html = '';

		// get all children
		if (Mage::helper('catalog/category_flat')->isEnabled()) {
			$children = (array)$category->getChildrenNodes();
			$childrenCount = count($children);
		} else {
			$children = $category->getChildren();
			$childrenCount = $children->count();
		}
		$hasChildren = ($children && $childrenCount);

		// select active children
		$activeChildren = array();
		foreach ($children as $child) {
			if ($child->getIsActive()) {
				$activeChildren[] = $child;
			}
		}
		$activeChildrenCount = count($activeChildren);
		$hasActiveChildren = ($activeChildrenCount > 0);





		/**
		 *
		 * Dropdown type
		 *
		 */
		$helper = Mage::helper('ultramegamenu');

		//Improved performance:
		//Assign category to variable cat. Old variable cat will still indicate where we're "loading" attributes.
		$cat = $category;

		//Get dropdown type attribute.
		//Apply constraints of dropdown types: inheritance and order of types.
		//IMPORTANT: these constraints have to be applied just after retrieving value of the dropdown type!
		$ddType = intval($cat->getData('umm_dd_type')); //Dropdown type

		if ($ddType === self::DDTYPE_NONE)
		{
			if ($parentInfo["ddType"] === self::DDTYPE_MEGA)
			{
				//If current category has no type and parent has type 'mega' - current category can't have any type.
				//Keep the type 'none'.
			}
			else
			{
				//If current category has no type and parent has any type except 'mega'
				$ddType = self::DDTYPE_CLASSIC;
			}
		}
		elseif ($ddType === self::DDTYPE_MEGA)
		{
			if ($parentInfo["ddType"] === self::DDTYPE_MEGA)
			{
				//If current category has type 'mega' and parent has type 'mega' - current category can't have any type.
				//Change to type 'none'.
				$ddType = self::DDTYPE_NONE;
			}
		}
		elseif ($ddType === self::DDTYPE_SIMPLE)
		{
			if ($parentInfo["ddType"] === self::DDTYPE_MEGA)
			{
				//If current category has type 'simple' and parent has type 'mega' - current category can't have any type.
				//Change to type 'none'.
				$ddType = self::DDTYPE_NONE;
			}
			elseif ($level === 0)
			{
				//On the top level, category can't have type 'simple'.
				//Change to type 'classic'.
				$ddType = self::DDTYPE_CLASSIC;
			}
		}

		//IMPORTANT:
		//Save info about current category (to pass it later to subcategories).
		//This has to be saved AFTER applying dropdown type constraints.
		$info = array(
			"ddType" => $ddType, //Info about current category's dropdown type
		);





		/**
		 *
		 * Category blocks
		 *
		 */
		$getCatBlocks = FALSE;
		$catBlocks = array();
		$htmlBeforeChildren = '';
		$htmlAfterChildren = '';
		$hasAnyCatBlocks = FALSE;
		$levelClass = "level" . $level;

		//Check if category blocks should be retrieved.
		//Retrieve category blocks only if current OR parent category has type 'mega'.
		if (FALSE === $this->_isSimpleSidemenu 
			&& ($ddType === self::DDTYPE_MEGA || $parentInfo["ddType"] === self::DDTYPE_MEGA)
			)
		{
			$getCatBlocks = TRUE;
			//Here limitation can be added: e.g. show category blocks only below selected level
		}

		//Retrieve category blocks
		if ($getCatBlocks)
		{
			$ddBlocks_raw = $this->_getCatBlock($cat, "umm_dd_blocks");

			//If attribute value not empty/null
			if ($ddBlocks_raw)
			{
				$catBlocks = explode($this->_catBlocksDelimiter, $ddBlocks_raw);
			}
		}

		//Render dropdown with category blocks of the current category if dropdown type is 'mega'.
		//Even if there's no category blocks, this code has to be executed to create wrapper around the subcategories.
		//TODO: maybe it's good to check if there are any subcategories. If no subcat. and no category blocks, then no need to execute this code.
		if (FALSE === $this->_isSimpleSidemenu && $ddType === self::DDTYPE_MEGA)
		{
			$ddProportions_raw = $cat->getData("umm_dd_proportions"); //Dropdown block proportions

			//Calculate grid units for category blocks (left and right) and submenu with subcategories (central column)
			if ($ddProportions_raw)
			{
				//IMPORTANT: it is assumed that values of proportions are numbers
				$proportions = explode(";", $ddProportions_raw);
				$leftUnits		= $proportions[0];
				$centralUnits	= $proportions[1];
				$rightUnits		= $proportions[2];
			}
			else
			{
				//If proportions not specified, all columns are equal
				$leftUnits = $centralUnits = $rightUnits = 4;
			}
			$leftGridClass		= "grid12-" . $leftUnits;
			$childrenGridClass	= "grid12-" . $centralUnits;
			$rightGridClass		= "grid12-" . $rightUnits;

			//Grid units constraints
			if (empty($catBlocks[1]) && empty($catBlocks[2])) //Block "Left" is empty & block "Right" is empty
			{
				$leftGridClass		= '';
				$childrenGridClass	= "grid12-12";
				$rightGridClass		= '';
			}
			elseif (empty($catBlocks[1])) //Block "Left" is empty
			{
				$leftGridClass		= '';
				$childrenGridClass	= "grid12-" . ($leftUnits + $centralUnits);
			}
			elseif (empty($catBlocks[2])) //Block "Right" is empty
			{
				$childrenGridClass	= "grid12-" . ($centralUnits + $rightUnits);
				$rightGridClass		= '';
			}
			//TODO: Try to split units so that each block has equal amount
			elseif (!$hasActiveChildren) //If no subcategories
			{
				$leftGridClass		= "grid12-" . ($leftUnits + $centralUnits);
				$childrenGridClass	= '';
				$rightGridClass		= "grid12-" . $rightUnits;
			}

			/*
			 * Check if there are any blocks in this category and get those blocks
			 */

			//Block "Top"
			if (!empty($catBlocks[0]))
			{
				$hasAnyCatBlocks = TRUE;
				$htmlBeforeChildren .= '<div class="nav-block nav-block--top std grid-full">' . $catBlocks[0] . '</div>';
			}

			//Block "Left"
			if (!empty($catBlocks[1]))
			{
				$hasAnyCatBlocks = TRUE;
				$htmlBeforeChildren .= '<div class="nav-block nav-block--left std ' . $leftGridClass . '">' . $catBlocks[1] . '</div>';
			}

			//Children
			if ($hasActiveChildren)
			{
				//IMPORTANT: no "nav-block" class here in central block:
				$htmlBeforeChildren .= '<div class="nav-block--center ' . $childrenGridClass . '">';
				//IMPORTANT: here between these elements the list of cateogry's children (subcategories) will be inserted...
				$htmlAfterChildren .= '</div>';
			}

			//Block "Right"
			if (!empty($catBlocks[2]))
			{
				$hasAnyCatBlocks = TRUE;
				$htmlAfterChildren .= '<div class="nav-block nav-block--right std ' . $rightGridClass . '">' . $catBlocks[2] . '</div>';
			}

			//Block "Bottom"
			if (!empty($catBlocks[3]))
			{
				$hasAnyCatBlocks = TRUE;
				$htmlAfterChildren .= '<div class="nav-block nav-block--bottom std grid-full">' . $catBlocks[3] . '</div>';
			}
		}

		//Indicates that the category has a dropdown box
		$hasDropdown = ($hasActiveChildren || $hasAnyCatBlocks) ? TRUE : FALSE;





		/**
		 *
		 * Calculate and apply classes to: item, link, dropdown wrapper, submenu
		 *
		 */
		//NEW:
		$itemClasses = array("nav-item");
		$linkClasses = array();
		$wrapperClasses = array(); //IMPORTANT: has to be empty. If any class exist, the wrapper needs to be built for current category.
		$submenuClasses = array("nav-submenu");

		$panelWrapperWidthStyle = '';
		$submenuWidthStyle = '';
		$caret = '';
		//end:NEW


		// prepare list item html classes
		$itemClasses[] = $levelClass;
		$itemClasses[] = "nav-" . $this->_getItemPosition($level);
		if ($this->isCategoryActive($category))
		{
			$itemClasses[] = "active";

			//If active class doesn't have any children, add special class (required by accordion script to mark the initial category)
			if ($category->getId() === $this->_curCategoryId)
			{
				$itemClasses[] = "current";
			}
		}
		if ($isOutermost && $outermostItemClass) {
			$itemClasses[] = $outermostItemClass;
			$linkClasses[] = $outermostItemClass;
		}
		if ($isFirst) {
			$itemClasses[] = "first";
		}
		if ($isLast) {
			$itemClasses[] = "last";
		}


		//NEW:

		if (FALSE === $this->_isSimpleSidemenu)
		{
			//Apply classes based on dropdown type
			if ($ddType === self::DDTYPE_CLASSIC)
			{
				/*
				 * Item classes
				 */
				if ($hasDropdown)
				{
					$itemClasses[] = "nav-item--parent"; //This item has dropdown

					//If item has dropdown, when category has type 'classic', add a special dropdown class to the submenu
					$submenuClasses[] = "nav-panel--dropdown";
				}
				$itemClasses[] = $this->_ddTypeClass[self::DDTYPE_CLASSIC];

				/*
				 * Submenu classes
				 */
				$submenuClasses[] = "nav-panel"; //Indicate that this is the panel for accordion in mobile menu mode
			}
			elseif ($ddType === self::DDTYPE_MEGA)
			{
				/*
				 * Item classes
				 */
				if ($hasDropdown)
				{
					$itemClasses[] = "nav-item--parent"; //This item has dropdown

					//If item has dropdown, when category has type 'mega', add a special dropdown class to the wrapper
					$wrapperClasses[] = "nav-panel--dropdown";
				}
				$itemClasses[] = $this->_ddTypeClass[self::DDTYPE_MEGA];
				
				/*
				 * Panel wrapper classes
				 */
				//- Submenu is wrapped inside additional div. That div will be a real dropdown box for this category.
				//- Add class 'nav-panel' to indicate that this is the actual panel for accordion in mobile menu mode.
				//  IMPORTANT: do not add 'nav-panel' class to the submenu when category has type 'mega',
				//  that class is already added to the dropdown wrapper.
				$wrapperClasses[] = "nav-panel";

				if ($childrenWrapClass)
				{
					$wrapperClasses[] = $childrenWrapClass;
				}

				/*
				 * Submenu classes
				 */
				$submenuClasses[] = "nav-submenu--mega";

				//Number of columns with subcategories
				//Create column classes which will be used to arrange in columns all the items on the level below.
				//Important: subcategory column classes have to be added directly to the submenu (because of selectors structure).
				$ddSubcatColsCount = intval($cat->getData("umm_dd_columns"));
				if ($ddSubcatColsCount === 0)
				{
					$ddSubcatColsCount = 4; //Default width of mega mdropdown is full-width so 4 is resonable default number of columns
				}
				$submenuClasses[] = "dd-itemgrid dd-itemgrid-" . $ddSubcatColsCount . "col";
			}
			elseif ($ddType === self::DDTYPE_SIMPLE)
			{
				/*
				 * Item classes
				 */
				$itemClasses[] = $this->_ddTypeClass[self::DDTYPE_SIMPLE];

				/*
				 * Submenu classes
				 */
				$submenuClasses[] = "nav-panel"; //Indicate that this is the panel for accordion in mobile menu mode
			}
			elseif ($ddType === self::DDTYPE_NONE)
			{
				/*
				 * Submenu classes
				 */
				$submenuClasses[] = "nav-panel"; //Indicate that this is the panel for accordion in mobile menu mode
			}

			//Dropdown width
			if ($ddWidth_raw = $cat->getData("umm_dd_width"))
			{
				//Evaluate dropdown width
				$panelWidthStyle = '';
				$widthClass = '';

				//If contain 'px' or '%' string, set the dropdown width using inline styles
				if (strpos($ddWidth_raw, "px") || strpos($ddWidth_raw, "%"))
				{
					$panelWidthStyle = ' style="width:' . $ddWidth_raw . ';"'; //Important: leave space at the beginning
				}
				else
				{
					//If width is in proper range (0; 12], create a grid class to apply it to the dropdown
					$widthClass = intval($ddWidth_raw);
					if (0 < $widthClass && $widthClass <= 12)
					{
						$widthClass = "no-gutter grid12-" . $widthClass;
					}
					else
					{
						//Important: if no grid class, set empty value
						$widthClass = '';
					}
				}

				//Apply dropdown width based on dropdown type
				if ($ddType === self::DDTYPE_CLASSIC)
				{
					//apply the width style to the submenu which in this case is the dropdown of the current category
					$submenuWidthStyle = $panelWidthStyle;
				}
				elseif ($ddType === self::DDTYPE_MEGA)
				{
					$panelWrapperWidthStyle = $panelWidthStyle;

					//If class exist, add the grid class to the dropdown
					if ($widthClass)
					{
						$wrapperClasses[] = $widthClass;
					}
				}
			}
			else
			{
				//If dropdown width not specified, use default width
				//IMPORTANT: but only if current category has type 'mega'
				if ($ddType === self::DDTYPE_MEGA)
				{
					$wrapperClasses[] = "full-width";
				}
			}

			//Apply classes indicating that there are only category blocks or only subcategories
			if ($hasAnyCatBlocks)
			{
				if (FALSE === $hasActiveChildren)
				{
					//If category has some category blocks but has NO SUBCATEGORIES
					$itemClasses[] = "nav-item--only-blocks";
				}
			}
			else
			{
				if ($hasActiveChildren)
				{
					//If category has NO CATEGORY BLOCKS, but has some subcategories
					$itemClasses[] = "nav-item--only-subcategories";
				}
			}

		} //end: FALSE === $this->_isSimpleSidemenu

		if ($hasDropdown)
		{
			//Add parent class if category has a dropdown box (so it has children or any of the category blocks)
			$itemClasses[] = "parent";

			//Add caret to indicate parent item parent item
			if (FALSE === $this->_isSimpleSidemenu)
			{
				$caret = '<span class="caret"></span>';
			}
		}





		/**
		 *
		 * Other elements
		 *
		 */

		//Number of products in category
		$num = '';
		if ($this->_showNumProd && $this->_isSimpleSidemenu) {
			$num = '<span class="number">('. $this->_getNumberOfProducts($cat) .')</span>';
		}

		//Category label
		$catLabel = $this->_getCategoryLabelHtml($cat, $level);

		//Category URL
		//Check if there is a custom target URL for the category
		if ($catTarget_raw = $cat->getData("umm_cat_target"))
		{
			if ($catTarget_raw === "#")
			{
				//If custom target is the hash character, use the hash as the target URL and add special class to the link
				$targetUrl = "#";
				$linkClasses[] = "no-click";
			}
			elseif ($catTarget_raw = trim($catTarget_raw))
			{
				//If custom target is not empty:
				if (strpos($catTarget_raw, "http") === 0)
				{
					//If custom target starts with "http", use it as the target URL
					$targetUrl = $catTarget_raw;
				}
				else
				{
					//Otherwise append it to the store base URL
					$targetUrl = Mage::getBaseUrl() . $catTarget_raw;
				}
			}
			else
			{
				//If custom target is empty, load the original URL of the category
				$targetUrl = $this->getCategoryUrl($category);
			}
		}
		else
		{
			$targetUrl = $this->getCategoryUrl($category);
		}





		/**
		 *
		 * Build the menu item and other elements
		 *
		 */

		//WHAT: beginning of the menu item --------------------------------------------------

		$html .= "<li" . ($itemClasses ? ' class="' . implode(" ", $itemClasses) . '"'  : '')  . ">";



		//WHAT: menu item: above the link and subcategories --------------------------------------------------

		//NEW:
		//If parent category has type 'mega', insert category block (Block "Top") above the category link.
		if (FALSE === $this->_isSimpleSidemenu && $parentInfo["ddType"] === self::DDTYPE_MEGA)
		{
			//If Block "Top" exist
			if (!empty($catBlocks[0]))
			{
				$html .= '<div class="nav-block nav-block--top std">' . $catBlocks[0] . '</div>';
			}
		}
		//end:NEW



		//WHAT: category link --------------------------------------------------

		$html .= '<a href="' . $targetUrl . '"' . ($linkClasses ? ' class="' . implode(" ", $linkClasses) . '"'  : '') . '>';
		$html .= '<span>' . $this->escapeHtml($category->getName()) . $num . $catLabel . '</span>' . $caret; //NEW: insert category label and caret
		$html .= '</a>';

		// render children
		$htmlChildren = '';
		$j = 0;
		foreach ($activeChildren as $child) {
			$htmlChildren .= $this->_renderCategoryMenuItemHtml(
				$child,								//category
				($level + 1),						//level
				($j == $activeChildrenCount - 1),	//isLast
				($j == 0),							//isFirst
				FALSE,								//isOutermost
				$outermostItemClass,
				$childrenWrapClass,
				$noEventAttributes,
				$info								//current category info needed by subcategories
			);
			$j++;
		}



		//WHAT: submenu with subcategories (category's children) --------------------------------------------------

		//TODO:? Can replace with this condition? Looks like it's OK. Check it.
		//if ($hasDropdown)
		if (!empty($htmlChildren) || $hasAnyCatBlocks) //NEW: added condition
		{
			//NEW:
			//Add opener for the menu item
			$html .= '<span class="opener"></span>';

			//If any wrapper classes exist, the wrapper needs to be built
			if (!empty($wrapperClasses))
			{
				$html .= '<div class="' . implode(' ', $wrapperClasses) . '"' . $panelWrapperWidthStyle . '><div class="nav-panel-inner">'; //NEW: additional inner wrapper
			}
			//end:NEW

			$html .= $htmlBeforeChildren; //NEW:
			
			if (!empty($htmlChildren))
			{
				$html .= '<ul class="' . $levelClass .' '. implode(' ', $submenuClasses) . '"' . $submenuWidthStyle . '>'; //TODO: added submenuClasses for acco
				$html .= $htmlChildren;
				$html .= '</ul>';
			}
			
			$html .= $htmlAfterChildren; //NEW:

			//If any wrapper classes exist, the wrapper needs to be built
			if (!empty($wrapperClasses))
			{
				$html .= "</div></div>";
			}
		}



		//WHAT: menu item: after subcategories --------------------------------------------------

		//NEW:
		//If parent category has type 'mega', insert category block (Block "Bottom") below the category link and children.
		if (FALSE === $this->_isSimpleSidemenu && $parentInfo["ddType"] === self::DDTYPE_MEGA)
		{
			//If Block "Bottom" exist
			if (!empty($catBlocks[3]))
			{
				$html .= '<div class="nav-block nav-block--bottom std">' . $catBlocks[3] . '</div>';
			}
		}
		//end:NEW

		$html .= "</li>";

		return $html;
	}
	//end: _renderCategoryMenuItemHtml
	
	/**
	 * Render categories menu in HTML. TODO: add new default param value 'level-top'
	 *
	 * @param bool Add opener if menu is used as accordion.
	 * @param int Level number for list item class to start from
	 * @param string Extra class of outermost list items
	 * @param string If specified wraps children list in div with this class
	 * @return string
	 */
	public function renderCategoriesMenuHtml($isAccordion = FALSE, $level = 0, $outermostItemClass = '', $childrenWrapClass = '')
	{
		//TODO: get rid of these param: $isAccordion
		
		$activeCategories = array();
		foreach ($this->getStoreCategories() as $child) {
			if ($child->getIsActive()) {
				$activeCategories[] = $child;
			}
		}
		$activeCategoriesCount = count($activeCategories);
		$hasActiveCategoriesCount = ($activeCategoriesCount > 0);

		if (!$hasActiveCategoriesCount) {
			return '';
		}

		//NEW:
		//Info about parent category.
		//IMPORTANT: at the beginning parent doesn't exist so it has to have no type.
		$parentInfo = array("ddType" => self::DDTYPE_NONE);
		//end:NEW

		$html = '';
		$j = 0;
		foreach ($activeCategories as $category) {
			$html .= $this->_renderCategoryMenuItemHtml(
				$category,
				$level,
				($j == $activeCategoriesCount - 1),
				($j == 0),
				TRUE,
				$outermostItemClass,
				$childrenWrapClass,
				TRUE,
				$parentInfo
			);
			$j++;
		}

		return $html;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * Render categories menu HTML
	 *
	 * @param bool Add opener if menu is used as accordion
	 * @param string|int Starting category - a point where traverse begins
	 * @param int Depth of recursion - how many levels of subcategories retrieve
	 * @return string
	 */
	public function renderMe($isAccordion, $parent = 0, $recursionLevel = 0)
	{
		//If in mode "parent_no_siblings"
		$curCategoryId = '';
		$curCategoryLevel = '';
		if ($parent === 'parent_no_siblings')
		{
			if ($curCategory = Mage::registry('current_category'))
			{
				$curCategoryId		= $curCategory->getId();
				$curCategoryLevel	= $curCategory->getLevel();
			}
		}

		//Save additional parameters
		$this->_isSimpleSidemenu = TRUE;
		$this->_showNumProd = Mage::helper('ultramegamenu')->getCfg('sidemenu/num_of_products');
		//TODO: get rid of these param: $isAccordion

		//Basic parameters (from default version of this method) for the main loop
		$level = 0;
		$outermostItemClass = '';
		$childrenWrapClass = '';

		//Retrieve categories by parent
		$parentCategoryId = $this->_getParentCategoryId($parent); //Retrieve parent category ID based on parameter value
		$storeCategories = $this->_getCategoriesByParent($parentCategoryId, $recursionLevel);
		$activeCategories = array();
		/* Mage::log('renderMe - $storeCategories: ' . get_class($storeCategories) .' : '. gettype($storeCategories)); */

		foreach ($storeCategories as $child)
		{
			if ($child->getIsActive())
			{
				if ($parent === 'parent_no_siblings') //If is in mode "parent_no_siblings"
				{
					if ($curCategoryLevel !== '' && $child->getLevel() == $curCategoryLevel && $child->getId() != $curCategoryId)
					{
						//Omit categories from the same level as current category (leave only current category)
						continue;
					}
				}

				$activeCategories[] = $child;
			}
		}
		$activeCategoriesCount = count($activeCategories);
		$hasActiveCategoriesCount = ($activeCategoriesCount > 0);

		if (!$hasActiveCategoriesCount) {
			return '';
		}

		//Info about parent category.
		//IMPORTANT: at the beginning parent doesn't exist so it has to have no type.
		$parentInfo = array("ddType" => self::DDTYPE_NONE);

		$html = '';
		$j = 0;
		foreach ($activeCategories as $category) {
			$html .= $this->_renderCategoryMenuItemHtml(
				$category,
				$level,
				($j == $activeCategoriesCount - 1),
				($j == 0),
				TRUE,
				$outermostItemClass,
				$childrenWrapClass,
				TRUE,
				$parentInfo
			);
			$j++;
		}

		return $html;
	}

	/**
	 * NEW:
	 * Retrieve categories by parent
	 *
	 * @param int $parentCategoryId Starting category - a point where traverse begins
	 * @param int $recursionLevel Depth of recursion - how many levels of subcategories retrieve
	 * @param bool $sorted
	 * @param bool $asCollection
	 * @param bool $toLoad
	 * @return Varien_Data_Tree_Node_Collection|Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection|array
	 */
	protected function _getCategoriesByParent($parentCategoryId = 0, $recursionLevel = 0, $sorted=FALSE, $asCollection=FALSE, $toLoad=TRUE)
	{
		//Check if starting category exists
		/* @var $category Mage_Catalog_Model_Category */ 
		$category = Mage::getModel('catalog/category');
		if ($parentCategoryId === NULL || !$category->checkId($parentCategoryId))
		{
			return array();
		}

		//Retrieve categories
		if (Mage::helper('catalog/category_flat')->isEnabled())
		{
			$storeCategories = $this->_getCategoriesByParentFlat($parentCategoryId, $recursionLevel, $sorted, $asCollection, $toLoad);
		}
		else
		{
			$storeCategories = $category->getCategories($parentCategoryId, $recursionLevel, $sorted, $asCollection, $toLoad);
		}
		
		return $storeCategories;
	}

	/**
	 * NEW:
	 * Retrieve flat categories by parent
	 *
	 * @param int $parentCategoryId Starting category - a point where traverse begins
	 * @param int $recursionLevel Depth of recursion - how many levels of subcategories retrieve
	 * @param bool $sorted
	 * @param bool $asCollection
	 * @param bool $toLoad
	 * @return Varien_Data_Tree_Node_Collection|Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection|array
	 */
	protected function _getCategoriesByParentFlat($parentCategoryId = 0, $recursionLevel = 0, $sorted=FALSE, $asCollection=FALSE, $toLoad=TRUE)
	{
		$flat = Mage::getResourceModel('catalog/category_flat');
		return $flat->getCategories($parentCategoryId, $recursionLevel, $sorted, $asCollection, $toLoad);
	}

	/**
	 * NEW:
	 * Retrieve parent category ID based on parameter value
	 *
	 * @param string|int $parent
	 * @return NULL|int
	 */
	protected function _getParentCategoryId($parent)
	{
		//Get starting category ID
		$parentCategoryId = NULL;

		if ($parent === 'current')
		{
			$curCategory = Mage::registry('current_category');
			if ($curCategory)
			{
				$parentCategoryId = $curCategory->getId();
			}
		}
		elseif ($parent === 'parent')
		{
			$curCategory = Mage::registry('current_category');
			if ($curCategory)
			{
				$parentCategoryId = $curCategory->getParentId();
			}
		}
		elseif ($parent === 'parent_no_siblings')
		{
			$curCategory = Mage::registry('current_category');
			if ($curCategory)
			{
				$parentCategoryId = $curCategory->getParentId();
			}
		}
		elseif ($parent === 'root' || !$parent)
		{
			$parentCategoryId = Mage::app()->getStore()->getRootCategoryId();
		}
		elseif (is_numeric($parent)) //IMPORTANT: this condition has to be placed after if(!parent)
		{
			$parentCategoryId = intval($parent);
		}

		//If no current category (e.g. if current page is the home page), get fallback category (root)
		$fallback = Mage::helper('ultramegamenu')->getCfg('sidemenu/fallback');
		if ($parentCategoryId === NULL && $fallback)
		{
			$parentCategoryId = Mage::app()->getStore()->getRootCategoryId();
		}

		return $parentCategoryId;
	}

	/**
	 * NEW:
	 * Get number of products in category
	 *
	 * @param Mage_Catalog_Model_Category
	 * @return int
	 */
	protected function _getNumberOfProducts($category)
	{
		return Mage::getModel('catalog/layer')
			->setCurrentCategory($category->getID())
			->getProductCollection()
			->getSize();
	}

	/**
	 * NEW:
	 * Get sidebar menu block title, process variables/placeholders
	 *
	 * @return string
	 */
	public function renderBlockTitle()
	{
		$helper = Mage::helper('ultramegamenu');
		$curCategory = Mage::registry('current_category');

		//Is mega menu type enabled for the sidebar menu
		$isMegamenu = $this->getIsMegamenu();
		if ($isMegamenu === NULL) //Param not set
		{
		    $isMegamenu = $helper->getCfg('sidemenu/is_megamenu');
		}

		//If no current category (e.g. if current page is the home page), and if not mega menu, try to get fallback title
		if (!$curCategory && !$isMegamenu)
		{
			//Check if fallback enabled
			$fallback = $helper->getCfg('sidemenu/fallback');
			if ($fallback)
			{
				$blockNameFallback = $helper->getCfg('sidemenu/block_name_fallback');
				if ($blockNameFallback)
				{
					return $blockNameFallback;
				}
				//Or else get the standard title
			}
		}

		//Get standard block title from parameter
		$blockName = $this->getBlockName();
		if ($blockName === NULL) //Param not set
		{
			$blockName = $helper->getCfg('sidemenu/block_name');
		}

		//Replace variables/placeholders in the title
		$currentCategoryName = '';
		if ($curCategory)
		{
			$currentCategoryName = $curCategory->getName();
		}
		$blockName = str_replace('[current_category]', $currentCategoryName, $blockName);

		return $blockName;
	}

	/**
	 * NEW:
	 * Get category block (attribute) content.
	 *
	 * @param Mage_Catalog_Model_Category
	 * @param string $attrId ID of the attribute
	 * @return string
	 */
	protected function _getCatBlock($cat, $attrId)
	{
		if (!$this->_tplProcessor)
		{
			$this->_tplProcessor = Mage::helper('cms')->getBlockTemplateProcessor();
		}
		
		return $this->_tplProcessor->filter( trim($cat->getData($attrId)) );
	}
	
	/**
	 * NEW:
	 * Get category label HTML
	 *
	 * @param Mage_Catalog_Model_Category
	 * @return string
	 */
	protected function _getCategoryLabelHtml($cat, $level)
	{
		$catLabelKey = $cat->getData('umm_cat_label');
		
		if ($catLabelKey)
		{
			$catLabelValue = trim(Mage::helper('ultramegamenu')->getCfg('category_labels/' . $catLabelKey));
			if ($catLabelValue)
			{
				if ($level == 0)
				{
					return '<span class="cat-label cat-label-'. $catLabelKey .' pin-bottom">' . $catLabelValue . '</span>';
				}
				else
				{
					return '<span class="cat-label cat-label-'. $catLabelKey .'">' . $catLabelValue . '</span>';
				}
			}
		}
		
		return '';
	}
}
