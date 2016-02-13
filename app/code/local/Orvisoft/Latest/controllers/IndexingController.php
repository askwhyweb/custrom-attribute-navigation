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
class Orvisoft_Latest_IndexingController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		echo 'Process Started...!<br />';
        Mage::getModel('orvisoftlatest/data')->setUrls();
		echo 'Reindexing Custom URLs succeeded...!';
    }
	
	public function removeAction(){
		$collection = Mage::getModel('core/url_rewrite')
											->getCollection();
		$collection->getSelect()->where('main_table.target_path LIKE ("latest/index/category/id/%")');									
		
		//$collection->setPageSize(1)->load();
		$i = 0;
		if ( $collection->count() > 0 ) {
			//$collection->getFirstItem()->delete();
			foreach($collection as $url){
				$url->delete();
				$i++;
			}
		}
		
		echo "Total $i records removed successfully...!";
	}
	
	public function findAction(){
		$designCategory = Mage::getResourceModel('catalog/category_collection')
								->addFieldToFilter('name', 'afghan')
								->getFirstItem(); // The child category
			print_r($designCategory->getId());
			if($designCategory){
				$designCategory = $designCategory->getId();
			}else{
				$designCategory = 0;
			}
	}
}