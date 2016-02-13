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
class Orvisoft_Latest_IndexController extends Mage_Core_Controller_Front_Action {

	protected function _fparameter(){
		Mage::dispatchEvent('catalog_controller_category_init_before', array('controller_action' => $this));

		$key = 'design'; // hardcoded value of key. For live site, this should be design.
		$val = $this->getRequest()->getParam($key);
		$page = 1;
		if(isset($_GET['page'])){
			$page = (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
		}
		$baseSize = Mage::helper('orvisoftlatest/data')->getPageLimit();
		$AllSizes = Mage::helper('orvisoftlatest/data')->getPagesLimit();
		$size = $baseSize;
		if(isset($_GET['limit']) && in_array($_GET['limit'], $AllSizes)){
			$size = (int)$_GET['limit'];
		}
		$sorting = $this->_getSorting();
		$products = false;
		if(strlen($val) > 1){
			$categoryId = (int) $this->getRequest()->getParam('id', false);
			$designCat = (int) $this->getRequest()->getParam('attrcat');
			if($designCat != 0){
				$category = Mage::getModel('catalog/category')->load($designCat);
				if(!(int)$category->getId() > 0){
					$category = Mage::getModel('catalog/category')->load($categoryId); // failover. load back the main cat.
				}
			}
			$activeFilters = Mage::helper('orvisoftlatest/data')->applyFilter($category->getId());

			$products = Mage::getResourceModel('catalog/product_collection')
				->addCategoryFilter($category) //$category->getProductCollection()
				->addAttributeToSelect('*') // add all attributes - optional
				->addAttributeToSelect($key)
				->addAttributeToFilter('status', 1) // enabled
				->addAttributeToFilter('visibility', 4) //visibility in catalog,search
				->setStoreId(Mage::app()->getStore()->getId())
				->addStoreFilter(Mage::app()->getStore()->getId())
			;
			$list = array();
			if(is_array($activeFilters) && count($activeFilters)){
				foreach($activeFilters as $K => $vals){
					$products->addAttributeToFilter($K,array('eq' => $vals));
					//echo count($products);
				}
			}else{
				//$products->addAttributeToFilter($key, array('eq' => Mage::getResourceModel('catalog/product')->getAttribute($key)->getSource()->getOptionId($val)));
			}
			$products->load();
			count($products);
			$products
				->setPageSize($size)
				->setCurPage($page)
				->setOrder($sorting['order'], $sorting['dir']); //sets the order by price
			//echo $products->getSelect();
			//exit;
			//$designid = (int) $this->getRequest()->getParam('attrcat');
			$layer = Mage::getSingleton("catalog/layer")->setCurrentCategory($category);

			//$catOrg = Mage::helper('orvisoftlatest/data')->getCategoryMethod($categoryId, $designid);
			//if($catOrg){
				//Mage::register('current_category', $catOrg);
				//Mage::app()->getRequest()->setParam('design', $designid); // thus our design is selected in the filter.
			//}
		}
		$totalpages = $products->getLastPageNumber();
		if($page > $totalpages){
			$page = $totalpages;
			$products->setCurPage($totalpages);	// Thus user cant go beyond the maximum limit.
		}
		Mage::register('current_entity_key', $category->getPath()); // extra addition.
		Mage::register('current_category', $category);
		Mage::register('current_page_number', $page);
		Mage::register('max_page_limit', $totalpages);
		Mage::register('page_size', $size);
		Mage::register('page_order', $sorting['order']);
		Mage::register('page_dir', $sorting['dir']);
		try {
			Mage::dispatchEvent(
				'catalog_controller_category_init_after',
				array(
					'category' => $category,
					'controller_action' => $this
				)
			);
		} catch (Mage_Core_Exception $e) {
			Mage::logException($e);
		}
		return $products;
	}
	protected function _initLatestOrviSoft(){
		$category = $this->_fparameter();
        return $category;
	}
    public function indexAction() {
        // Set the appropriate content-type
        // $this->getResponse()->setHeader('Content-type', 'text/javascript');
        // Loads and renders the layout file we will create soon
         $this->loadLayout();
         $this->renderLayout();
		 //Zend_Debug::dump($this->getLayout()->getUpdate()->getHandles());
		// echo 'XYZ. We are in.';
    }

	public function categoryAction(){
		$this->loadLayout();

		$url = Mage::helper('core/url')->getCurrentUrl();
		$urlpath = substr(strrchr($url, "/"), 1);
		if($category = $this->_initLatestOrviSoft()) {
			Mage::register('category_products_latest', $category);
            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');

            $this->generateLayoutXml()->generateLayoutBlocks();

            if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('orvisoft-latest-' . $urlpath);
            }
            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');

			$this->renderLayout();
			//Zend_Debug::dump($this->getLayout()->getUpdate()->getHandles());
        }
        elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
	}

	protected function _getSorting(){
		$result = Mage::helper('orvisoftlatest/data');
		$sorting = $result->getSorting();
		if(!isset($sorting['order'])){
			$sorting['order'] = 'created_at';
		}
		if(!isset($sorting['dir'])){
			$sorting['dir'] = 'desc';
		}
		return $sorting;
	}
}
