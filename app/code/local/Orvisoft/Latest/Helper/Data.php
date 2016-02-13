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
class Orvisoft_Latest_Helper_Data extends Mage_Core_Helper_Abstract
{
	private $name;
	private $end;
	function processIndexes(){
		$this->name = 'occasion';
		$this->end = 'rugs';
		$data = array();
		$attribute = $this->getAttributeValues($this->name);
		$cats = $this->getCategories();

		foreach($cats as $id => $urlkey){
			$data[] = $this->getArrayMerge($urlkey, $attribute,  $id);
		}
		return $data;
	}
	
	function getArrayMerge($cat, $attribute, $id=0){
		$data = array();
		$i = 0;
		$end = $this->end;
		$name = $this->name;
		
		foreach ($attribute as $key => $val){
			$designCategory = Mage::getResourceModel('catalog/category_collection')
								->addFieldToFilter('name', $val)
								->getFirstItem(); // The child category
			$designCategory = (int)$designCategory->getId();
			if(strlen($end) > 0){
				$data[$i]['url'] = strtolower($cat).'-'.strtolower($val).'-'.$end.'.html';
			}else{
				$data[$i]['url'] = strtolower($cat).'-'.strtolower($val).'.html';
			}
			$data[$i]['route'] = "latest/index/category/id/$id/$name/$val/attrcat/$designCategory";
			$i++;
		}
		return $data;
	}
	
	function getAllAttributes(){
		$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
		$attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection')
													->addFieldToFilter('is_filterable', true)
													->getItems();
		return $attributeCollection;
	}
	
	function getAttributeValues($name){
		$attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter($name)->getFirstItem();
		$attributeId = $attributeInfo->getAttributeId();
		$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
		$attributeOptions = $attribute ->getSource()->getAllOptions(false); 
		$data = array();
		if(is_array($attributeOptions) && count($attributeOptions) > 0){
			foreach($attributeOptions as $attribute_values){
				$data[$attribute_values['value']] = $attribute_values['label'];
			}
		}
		return $data;
	}
	
	function getCategories($attribute = '*'){
		$collection = Mage::getModel('catalog/category')->getCategories(2); // 2 is the root category.
		/**
			->getCollection()
			->addFieldToFilter('is_active',array("in"=>array('1')))
			->addFieldToFilter('include_in_menu',array("eq"=>array('1')))
			->addAttributeToSelect($attribute);
			/**/
		$data = array();
		foreach ($collection as $category){
			$key = $category->getName();
			if(strlen(trim($key)) > 0){
				$data[$category->getId()] = str_replace(' ', '-', trim(str_replace($this->end,'',strtolower($key))));
			}
		}
		return $data;
	}
	
	function getSorting(){
		$sortBy = '';
		$allowed = array('created_at', 'price', 'base_size');
		$direction = 'asc';
		if(isset($_GET['dir'])){
			$direction = strtolower($_GET['dir']);
			if($direction !== 'asc' && $direction !== 'desc'){
				$direction = 'desc';
			}
		}
		if(isset($_GET['order']) && in_array(strtolower(trim($_GET['order'])), $allowed)){
			$sortBy = strtolower(trim($_GET['order']));
		}elseif(isset($_GET['order']) && $_GET['order']=='random'){
			$sortBy = 'created_at';
			$direction = 'desc';
		}
		return array('order' => $sortBy, 'dir' => $direction);
	}
	
	function getPageLimit(){
		$key = 'catalog/frontend/grid_per_page';
		$configValue = Mage::getStoreConfig($key, Mage::app()->getStore());
		return $configValue;
	}
	
	function getPagesLimit(){
		$key = 'catalog/frontend/grid_per_page_values';
		$configValue = Mage::getStoreConfig($key, Mage::app()->getStore());
		$data = explode(',', $configValue);
		$output = array();
		foreach($data as $k){
			$output[] = trim($k);
		}
		return $output;
	}
	
	protected $code;
	function setCode($code){
		$this->code = $code;
	}
	
	function getCode(){
		return $this->code;
	}
	
	function getDesignUrl($category, $attributeTitle, $end='rugs'){
		$category = str_replace(' ', '-', trim(str_replace($end,'',strtolower($category))));
		return Mage::getUrl().strtolower($category).'-'.strtolower($attributeTitle).'-'.$end.'.html';
	}
	
	function getCategoryMethod($catid = 0, $categoryId = 0){
			
			if($categoryId == 0){
				$categoryId = $catid;
			}
			$category = Mage::getModel('catalog/category')->load($categoryId);
			if((int)$category->getId() > 0){
				return $category;
			}
			return false;
			//die('I am sorry, I am not able to find the requested Category..! Last request: '.$catid.', First Request: '.$categoryId);
	}
	
	function getApplicableFilters($categoryid){
		$layer = Mage::getModel("catalog/layer");
		$category = Mage::getModel("catalog/category")->load($categoryid);
		$layer->setCurrentCategory($category);
		$attributes = $layer->getFilterableAttributes();
		$output = array();
		foreach ($attributes as $attribute) {
			$output[] = $attribute->getAttributeCode();
		}
		return $output;
	}
	
	function applyFilter($categoryid){
		$attributes = $this->getApplicableFilters($categoryid);
		$output =array();
		if(isset($_GET) && count($_GET)){
			foreach($_GET as $key => $vals){
				if(in_array($key, $attributes)){
					$output[$key]= $vals;
				}
			}
		}
		return $output;
	}
	
}