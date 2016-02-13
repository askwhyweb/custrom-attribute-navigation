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
class Orvisoft_Latest_Block_Categories extends Mage_Core_Block_Template {
	function getCategories(){
		return Mage::getModel('orvisoftlatest/data')->getCategories();
	}
	
	function getProductsByCategory($catId){
		$store = Mage::app()->getStore()->getId();
		$products_limit = Mage::getModel('orvisoftlatest/data')->getProductsLimit();
		$_today = date('Y-m-d');
		$collection =  Mage::getModel('catalog/product')->setStoreId($store)
                ->getCollection()  
                ->addCategoryFilter(Mage::getModel('catalog/category')->load($catId))
                ->addAttributeToSelect('*')     
				->addFinalPrice()				
                ->addAttributeToFilter('news_from_date',array('date' => true, 'lteq' => $_today))
                ->addAttributeToFilter('news_to_date', array('date' => true, 'gteq' => $_today))
				//->addAttributeToFilter('is_in_stock', 1)
				->addAttributeToFilter('status', 1)
				->addAttributeToFilter('visibility', 4)
				->addAttributeToSort('updated_at','asc')->setOrder('updated_at', 'asc')
				->setPageSize($products_limit)
				->setCurPage(1)
                ;
		return $collection;
	}
	
	function getNewProductsCountByCategory($catId){
		$store = Mage::app()->getStore()->getId();
		$_today = date('Y-m-d');
		$totals =  Mage::getModel('catalog/product')->setStoreId($store)
                ->getCollection()  
                ->addCategoryFilter(Mage::getModel('catalog/category')->load($catId))
                ->addAttributeToSelect('*')     
				->addFinalPrice()				
                ->addAttributeToFilter('news_from_date',array('date' => true, 'lteq' => $_today))
                ->addAttributeToFilter('news_to_date', array('date' => true, 'gteq' => $_today))
				//->addAttributeToFilter('is_in_stock', 1);
				->addAttributeToFilter('status', 1)
				->addAttributeToFilter('visibility', 4)
				->getSize();
		return $totals;
	}
	
	function getProductsCountByCategory($catId){
		$store = Mage::app()->getStore()->getId();
		$_today = date('Y-m-d');
		$totals =  Mage::getModel('catalog/product')->setStoreId($store)
                ->getCollection()  
                ->addCategoryFilter(Mage::getModel('catalog/category')->load($catId))
                ->addAttributeToSelect('*')     
				->addFinalPrice()
				->addAttributeToFilter('status', 1)	
				->addAttributeToFilter('visibility', 4)				
                //->addAttributeToFilter('is_in_stock', 1);
				->getSize();
		
		return $totals;
	}
	function getLatestProduct($catId){
		$store = Mage::app()->getStore()->getId();
		$_today = date('Y-m-d');
		$product =  Mage::getModel('catalog/product')->setStoreId($store)
                ->getCollection()  
                ->addCategoryFilter(Mage::getModel('catalog/category')->load($catId))
                ->addAttributeToSelect('*')     
				->addFinalPrice()
				->addAttributeToFilter('status', 1)	
				->addAttributeToFilter('visibility', 4)
				->addAttributeToSort('updated_at','desc')->setOrder('updated_at', 'desc')
				->addAttributeToFilter('news_from_date',array('date' => true, 'lteq' => $_today))
                ->addAttributeToFilter('news_to_date', array('date' => true, 'gteq' => $_today))
				->setPageSize(1);
		return $product;
	}
	
	function generateData(){
		$_categories = $this->getCategories();
		$output = array();
		foreach($_categories as $categorie):
			$catid= $categorie->getId();
			$total_new = $this->getNewProductsCountByCategory($catid);
			
			if($total_new == 0)
				continue;
			$_products = $this->getProductsByCategory($catid);
			$total_products = $this->getProductsCountByCategory($catid);
			foreach($this->getLatestProduct($catid) as $latest): 
				$last_update =  $latest->getUpdatedAt();
			endforeach;
			
			$output[$catid] = array(
				'category_id' => $catid,
				'category_name' => $categorie->getName(),
				'category_url' => $categorie->getUrl(),
				'total_new' => $total_new,
				'total' => $total_products,
				'last_update' => $last_update,
			);
			foreach ($_products as $product):
				$output[$catid]['products'][] = array(
					'id' => $product->getId(),
					'sku' => $product->getSku(),
					'name' => $product->getName(),
					'url' => $product->getProductUrl(),
					'image' => (string)Mage::helper('catalog/image')->init($product, 'small_image')->resize(135),
					'price' => round($product->getFinalPrice()),
					//'last_update' => $product->getUpdatedAt()
				);
			endforeach;
		
		endforeach;
		return $output;
	}
	
	function getDesignCategory($select = '*'){
		$categoryId = (int) $this->getRequest()->getParam('attrcat');
		if($categoryId == 0){
			return false;
		}
		$category = Mage::getModel('catalog/category')->load($categoryId);
		if((int)$category->getId() > 0){
			return $category;
		}
		return false;
	}
	
	function getLayersList($attr = 'design'){
		$layer = Mage::getModel("catalog/layer");
		$category = Mage::registry('current_category');
		$layer->setCurrentCategory($category);
		$attributes = $layer->getFilterableAttributes();
		$data = array();

		foreach ($attributes as $attribute) {
			if($attribute->getAttributeCode() == $attr){
				$filterBlockName = 'catalog/layer_filter_attribute';
			//if ($attribute->getAttributeCode() == 'price') {
			//	$filterBlockName = 'catalog/layer_filter_price';
			//} elseif ($attribute->getBackendType() == 'decimal') {
			//	$filterBlockName = 'catalog/layer_filter_decimal';
			} else {
				continue;
			}
			
			$result = $this->getLayout()->createBlock($filterBlockName)->setLayer($layer)->setAttributeModel($attribute)->init();

			foreach($result->getItems() as $option) {
				$data[] = array(
					'label' => $option->getLabel(),
					'value' => $option->getValue(),
					'count' => $option->getCount()
				);
				//break;
			}
			//break;
		}
		//echo '<pre>'.print_r($data,true).'</pre>';
		return $data;
	}
	
	function getProductByFilter($val, $key='design'){
		$category = Mage::registry('current_category');
		$product = Mage::getModel('catalog/category')->load($category->getId())
						 ->getProductCollection()
						 ->addAttributeToSelect('*') // add all attributes - optional
						 ->addAttributeToSelect($key)
						 ->addAttributeToFilter('status', 1) // enabled
						 ->addAttributeToFilter('visibility', 4) //visibility in catalog,search
						 ->addAttributeToFilter($key,array(
												 'eq' => Mage::getResourceModel('catalog/product')
																					->getAttribute($key)
																					->getSource()
																					->getOptionId($val)
																)
												)
						->setPageSize(1)
						//->setCurPage(1)
						->setOrder('created_at', 'DESC'); //sets the order by price
		$totalSize = $product->getLastPageNumber();
		$randomPageNumber = mt_rand(1, $totalSize);
		return $product->setCurPage($randomPageNumber)->getFirstItem();
	}
	
	function getProductUrl($category, $attributeTitle, $end='rugs'){
		$category = str_replace(' ', '-', trim(str_replace($end,'',strtolower($category))));
		return Mage::getUrl().strtolower($category).'-'.strtolower($attributeTitle).'-'.$end.'.html';
	}
	
	function getPaginationHtml(){
		$url = $this->getPreparedUrlClone('page', true);
		$currentPage = Mage::registry('current_page_number'); //current_page_number
		$maxPages = Mage::registry('max_page_limit'); //max_page_limit
		$output = '';
		$total = 0;
		if($currentPage > 2){
			$output .= '<a rel="prev" href="'.$url.'page='.($currentPage-1).'" class="btn">'.$this->__('Prev').'</a>';
			$total++;
		}
		if($currentPage != 1){
			$output .= '<a href="'.$url.'" class="btn">1</a>';
			$total++;
		}else{
			$output .= '<a href="#" class="btn active">'.($currentPage).'</a>';
			$total++;
		}
		
		if($currentPage > 2){
			$output .= '<a class="btn" href="#">...</a>';
			$total++;
		}
		if($currentPage != 2 && $currentPage > 2){
			$output .= '<a href="'.$url.'page='.($currentPage-1).'" class="btn">'.($currentPage-1).'</a>';
			$total++;
		}
		if($currentPage != 1){
			$output .= '<a href="#" class="btn active">'.($currentPage).'</a>';
			$total++;
		}
		if($currentPage < $maxPages){
			$output .= '<a href="'.$url.'page='.($currentPage+1).'" class="btn">'.($currentPage+1).'</a>';
			$total++;
			if($total ==2 || $currentPage ==1 || $currentPage == 2){
				$output .= '<a href="'.$url.'page='.($currentPage+2).'" class="btn">'.($currentPage+2).'</a>';
				$total++;
			}
			if(($maxPages-2) > $currentPage){
				$output .= '<a class="btn" href="#">...</a>';
				$total++;
			}
			if($maxPages != ($currentPage+1)){
				$output .= '<a href="'.$url.'page='.($maxPages).'" class="btn">'.($maxPages).'</a>';// rel="next" class="btn"
				$total++;
			}
			$output .= '<a href="'.$url.'page='.($currentPage+1).'"rel="next" class="btn">'.$this->__('Next').'</a>';
			$total++;
		}
		return $output;
	}
	
	function getPreparedUrlClone($skip = '', $addNextPossibility = FALSE){
		$basicUrl = strtok(Mage::helper('core/url')->getCurrentUrl(), '?');
		$n = false;
		if(isset($_GET) && count($_GET) > 0){
			foreach($_GET as $key => $val){
				if($key == $skip){
					continue;
				}
				if($n){
					$basicUrl .='&'.$key.'='.urlencode($val);
				}else{
					$basicUrl .='?'.$key.'='.urlencode($val);
					$n = true;
				}
			}
		}
		if($addNextPossibility){
			if($n){
				$basicUrl .= '&';
			}else{
				$basicUrl .= '?';
			}
		}
		return $basicUrl;
	}
	
	function getOrderByUrl($attr, $dr='asc'){
		$basicUrl = strtok(Mage::helper('core/url')->getCurrentUrl(), '?');
		if($attr == 'random'){
			$basicUrl .= '?order='.$attr;
		}else{
			$basicUrl .= '?order='.$attr.'&dir='.$dr;
		}
		
		if(isset($_GET['page'])){
			$basicUrl .= '&page='.$_GET['page'];
		}
		return $basicUrl;
	}
	
	function getOrderBySelection($order, $dir='desc', $strict=false){
		$pageorder = Mage::registry('page_order');
		$pagedir = Mage::registry('page_dir');
		if(!isset($_GET['order']) || $_GET['order'] == 'random'){
			$pageorder = 'random';
			$pagedir = 'desc';
			$dir = 'desc';
		}
		
		if($pageorder == $order && $pagedir == $dir){
			return true;
		}
		
		return false;
	}
}
