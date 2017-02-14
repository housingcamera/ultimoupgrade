<?php

/**
 * StitchLabs_ChannelIntegration extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category       StitchLabs
 * @package        StitchLabs_ChannelIntegration
 * @copyright      Copyright (c) 2014
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
class StitchLabs_ChannelIntegration_Model_Info_Api_V2
{
    /**
     * Info Server
     * @access public
     * @return string
     */
    public function server()
    {
        $result = array(
            'php_info_json' => json_encode($this->phpinfo_array())
        );
        $result = Mage::helper('api')->wsiArrayPacker($result);

        return $result;
    }

    /**
     * Info Module
     * @access public
     * @return string
     */
    public function module()
    {
        $response_payload = array();

        $module_info = Mage::getConfig()->getModuleConfig('StitchLabs_ChannelIntegration')->asArray();

        if (empty($module_info)) {
            $response_payload['msg'] = 'error';
        } else {
            $response_payload['msg'] = 'success';
            $response_payload['module_config'] = $module_info;
        }

        $var_path = Mage::getBaseDir('var');

        $package_path = $var_path . DS . 'package';

        $directory_listing = scandir($package_path);

        if (is_array($directory_listing)) {
            foreach ($directory_listing as $object_name) {
                if (stripos($object_name, 'StitchLabs_ChannelIntegration') !== FALSE) {
                    $version = explode('-', $object_name);
                    $version = explode('.xml', $version[1]);

                    $response_payload['module_package'] = array(
                        'name' => $object_name,
                        'version' => $version[0]
                    );
                }
            }
        }

        $result = array(
            'module_info_json' => json_encode($response_payload)
        );
        $result = Mage::helper('api')->wsiArrayPacker($result);

        return $result;
    }

    private function phpinfo_array()
    {
        ob_start();
        phpinfo();
        $info_arr = array();
        $info_lines = explode("\n", strip_tags(ob_get_clean(), "<tr><td><h2>"));
        $cat = "General";
        foreach ($info_lines as $line) {
            // new cat?
            preg_match("~<h2>(.*)</h2>~", $line, $title) ? $cat = $title[1] : null;
            if (preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)) {
                $info_arr[$cat][$val[1]] = $val[2];
            } elseif (preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)) {
                $info_arr[$cat][$val[1]] = array("local" => $val[2], "master" => $val[3]);
            }
        }
        return $info_arr;
    }
}
