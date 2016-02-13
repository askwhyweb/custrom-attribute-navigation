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
class Orvisoft_Latest_Model_Data extends Mage_Core_Model_Abstract {
	function getCategoriesLimit(){
		$limit = Mage::getStoreConfig('orvisoftlatest_setup/settings/page_size');
		if(!(int)$limit){
			$limit = 9999;
		}
		return $limit;
	}
	
	function getProductsLimit(){
		return Mage::getStoreConfig('orvisoftlatest_setup/settings/product_size');
	}
	
	function getCategories(){
		$page_id = (int)Mage::app()->getRequest()->getParam('page');
		if($page_id < 1){
			$page_id = 1;
		}
		if($page_id > $this->getMaxPagesLimit()){
			$page_id = $this->getMaxPagesLimit();
		}
		$storeId = Mage::app()->getStore()->getStoreId();
		$_categories = Mage::getStoreConfig('orvisoftlatest_setup/settings/categories');
		//echo $categories;
		$rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();

		$categories = Mage::getModel('catalog/category')
			->getCollection()
			->setStoreId($storeId)
			->addFieldToFilter('is_active', 1)
			->addAttributeToFilter('path', array('like' => "1/{$rootCategoryId}/%"))
			->addAttributeToSelect('*')
			->addAttributeToFilter('entity_id', array(
					array('in' => explode(',', $_categories)),
			))
			//->setPageSize($this->getCategoriesLimit())
			//->setCurPage($page_id)
			;
		
		return $categories;
	}
	private $_getmaxpages;
	function getMaxPagesLimit(){
		if(isset($this->_getmaxpages) && (int)$this->_getmaxpages > 0){
			return $this->_getmaxpages;
		}

		$storeId = Mage::app()->getStore()->getStoreId();
		$_categories = Mage::getStoreConfig('orvisoftlatest_setup/settings/categories');
		//echo $categories;
		$rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();

		$categories = Mage::getModel('catalog/category')
			->getCollection()
			->setStoreId($storeId)
			->addFieldToFilter('is_active', 1)
			->addAttributeToFilter('path', array('like' => "1/{$rootCategoryId}/%"))
			->addAttributeToSelect('*')
			->addAttributeToFilter('entity_id', array(
					array('in' => explode(',', $_categories)),
			))
			->getSize();
		return $this->_getmaxpages = ceil((int)$categories/(int)$this->getCategoriesLimit());
	}
	function setUrls(){
		$data = Mage::helper('orvisoftlatest/data')->processIndexes();
		if(is_array($data) && count($data) > 0){
			//echo '<pre>'.print_r($data, true).'</pre>';
			$stores = $this->getAllStoreIds();
			foreach ($stores as $store){
				$this->recursiveRewrite($data, $store);
			}
		}
		return $data;
	}
	function recursiveRewrite($data, $store){
		if(is_array($data) && count($data) > 0){
			foreach ($data as $key => $val){
				foreach($val as $k => $v){
					if(!$this->verifyExisting($v['url'], $store)){
						$this->insertUrlRewrite($v, $store);
					}
				}
			}
		}
	}
	protected $res;
	protected $rc;
	
	function verifyExisting($key, $store){
		if(!isset($this->res)){
			$this->res = Mage::getSingleton('core/resource');
		}
		
		$resource = $this->res;
		
		if(!isset($this->rc)){
			$this->rc = $resource->getConnection('core_read');
		}
		
		$readConnection = $this->rc;

		$table = 'core_url_rewrite';
		
		$query = 'SELECT request_path FROM ' . $table . ' WHERE request_path = "' . $key . '" AND store_id = '.(int)$store.' LIMIT 1';
		
		$single = $readConnection->fetchOne($query);
		if(strlen($single) > 0){
			return true;
		}
		return false;
	}
	protected $stores;
	protected function getAllStoreIds(){
		if(isset($this->stores)){
			return $this->stores;
		}
		$allStores = Mage::app()->getStores();
		$data = array();
		foreach ($allStores as $_eachStoreId => $val) 
		{
			$data[] = Mage::app()->getStore($_eachStoreId)->getId(); // Store Id
		}
		return $this->stores = $data;
	}
	
	protected $increment;
	function insertUrlRewrite($val, $store){
		if(!isset($this->increment)){
			$this->increment = 0;
		}
		$key = date('l jS \of F Y h:i:s A').($this->increment === 0 ? '' : $this->increment);
		Mage::getModel('core/url_rewrite')
				->setIsSystem(0)
				->setStoreId($store)
				->setOptions('') // empty, R or RP
				->setIdPath($key) // Some unique key
				->setRequestPath($val['url'])
				->setTargetPath($val['route'])
				->save();
		$this->increment++;
	}
	
}
