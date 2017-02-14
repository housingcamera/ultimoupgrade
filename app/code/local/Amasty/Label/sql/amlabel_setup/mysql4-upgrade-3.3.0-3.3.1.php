<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
$this->startSetup();

$this->run("
  INSERT INTO `{$this->getTable('amlabel/label')}`
         (`label_id`, `pos`, `is_single`, `name`, `is_active`, `stores`,
         `prod_txt`, `prod_img`, `prod_pos`, `prod_style`, `cat_txt`, `cat_img`, `cat_pos`, `cat_style`,
         `is_new`, `is_sale`, `special_price_only`, `include_type`, `include_sku`, `category`,
          `attr_code`, `attr_multi`, `product_stock_enabled`,
          `attr_rule`, `attr_value`, `stock_less`, `stock_more`, `stock_status`, `from_date`, `to_date`,
          `date_range_enabled`, `from_price`, `to_price`, `by_price`, `price_range_enabled`,
          `customer_groups`, `customer_group_enabled`, `use_for_parent`, `cat_text_style`, `prod_text_style`,
          `cat_image_width`, `cat_image_height`, `prod_image_width`, `prod_image_height`)
    VALUES (NULL, '0', '0', 'On Sale Label', '0', ',1,2,3,', '', 'sale-red.png', '2',
        'font-size: 12px;', 'Sale', 'label-red.png', '2', 'padding: 11px 0 0 7px;font-size: 18px;color: #fcfcfc;',
        '0', '2', '1', '0', NULL, NULL, '', '0', '0',  '0', '', '0', '0',
        '0', NULL, NULL, '0', '0.0000', '0.0000', '0', '0', '', '0', '0', '', '', '', '', '', '');
");

$this->run("
  INSERT INTO `{$this->getTable('amlabel/label')}`
         (`label_id`, `pos`, `is_single`, `name`, `is_active`, `stores`,
         `prod_txt`, `prod_img`, `prod_pos`, `prod_style`, `cat_txt`, `cat_img`, `cat_pos`, `cat_style`,
         `is_new`, `is_sale`, `special_price_only`, `include_type`, `include_sku`, `category`,
          `attr_code`, `attr_multi`, `product_stock_enabled`,
          `attr_rule`, `attr_value`, `stock_less`, `stock_more`, `stock_status`, `from_date`, `to_date`,
          `date_range_enabled`, `from_price`, `to_price`, `by_price`, `price_range_enabled`,
          `customer_groups`, `customer_group_enabled`, `use_for_parent`, `cat_text_style`, `prod_text_style`,
          `cat_image_width`, `cat_image_height`, `prod_image_width`, `prod_image_height`)
    VALUES (NULL, '0', '0', 'New Label', '0', ',1,2,3,', '', 'new-arrival.png', '2',
        'text-align: center; line-height: 70px;', '', 'new-green.png', '2', 'text-align: center; line-height: 40px;',
        '2', '0', '1', '0', NULL, NULL, '', '0', '0', '0', '', '0', '0',
        '0', NULL, NULL, '0', '0.0000', '0.0000', '0', '0', '', '0', '0', '', '', '', '', '', '');
");

$this->endSetup();
