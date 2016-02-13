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
class Orvisoft_Latest_Model_System_Categories extends Mage_Core_Model_Config_Data {
    /*
     * Magento Categories.
     * @return array of system categories
     */

    public function toOptionArray() {
        return $this->__getCats();
    }

    /*
     * Categories for selection.
     * @return array of categories list
     */

    private function __getCats() {

		$rootcatID = Mage::app()->getStore()->getRootCategoryId();

		$categories = Mage::getModel('catalog/category')
							->getCollection()
							->addAttributeToSelect('*')
							->addIsActiveFilter();
        $data = array();
        foreach ($categories as $attribute) {
			$data[] = array(
				'value' => $attribute->getId(),
				'label' => $attribute->getName()
			);
        }
        return $data;
    }

}
