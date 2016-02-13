<?php

/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OrviSoft Latest to newer
 * versions in the future. If you wish to customize the code for your
 * needs please refer to https://orvisoft.com/ for more information.
 *
 * @category    OrviSoft Latest Magento Connect
 * @package     Orvisoft_Latest
 * @copyright   Copyright (c) 20015 OrviSoft Private Limited. (www.orvisoft.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * OrviSoft Latest Package Module.
 *
 * @category   OrviSoft Latest Magento Connect
 * @package    Orvisoft_Latest
 * @author     Farhan Islam <farhan@orvisoft.com>
 */
class Orvisoft_Latest_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
	public function match(Zend_Controller_Request_Http $request)
	{
		$request->setModuleName('test-frontname')
			->setControllerName('index')
			->setActionName('index');
		return true;
	}
 
}
