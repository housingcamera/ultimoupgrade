<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Follow Up Email
 * @version   1.0.14
 * @build     630
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Email_Helper_ErrorHandler extends Mage_Core_Helper_Abstract
{
    /**
     * Call email error handler if error is fatal.
     */
    public function shutdownFunction()
    {
        $error = error_get_last();
        if (is_array($error) && $error['type'] == E_ERROR) {
            $this->errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Assign status "Error" to current queue.
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     *
     * @return bool
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (strpos($errfile, 'emaildesign') !== false) {
            $queue = Mage::registry('email_queue');
            if ($queue) {
                $content = file_get_contents($errfile);
                $template = $queue->getTemplate();
                $areaCode = ucfirst($template->getAreaCodeByContent($content));
                $templateUrl = Mage::helper('adminhtml')->getUrl(
                    'adminhtml/emaildesign_template/edit',
                    array('id' => $template->getId())
                );

                $message = sprintf(
                    '
                        This error is related to improper use of variables in the email template. Error Details:
                            <b>Email template:</b> <a target="_blank" href="%s">%s</a> (ID: %u);
                            <b>Area</b>: "%s";
                            <b>line</b>: #%u;
                            <b>Error message:</b> "%s".
                    ',
                    $templateUrl, $template->getTitle(), $template->getId(), $areaCode, $errline, $errstr
                );

                $queue->error($message);

                return true;
            }
        }

        return false;
    }

    /**
     * Register shutdown and error handler functions.
     */
    public function handleErrors()
    {
        register_shutdown_function(array($this, 'shutdownFunction'));
        set_error_handler(array($this, 'errorHandler'));
    }

    /**
     * Restore original error handler.
     */
    public function restoreErrorHandler()
    {
        restore_error_handler();
    }
}
