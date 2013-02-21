<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shopperslideshow_Block_Slideshow extends Mage_Core_Block_Template
{
	protected function _beforeToHtml()
	{
		$config = Mage::getStoreConfig('shopperslideshow', Mage::app()->getStore()->getId());
		$route = Mage::app()->getFrontController()->getRequest()->getRouteName();
		$action = Mage::app()->getFrontController()->getRequest()->getActionName();
		if ($config['config']['enabled']) {
			$show = true;
			if ($config['config']['show'] == 'home') {
				$show = false;
				$page = Mage::app()->getFrontController()->getRequest()->getRouteName();

				if ($page == 'cms') {
					$cmsSingletonIdentifier = Mage::getSingleton('cms/page')->getIdentifier();
					$homeIdentifier = Mage::app()->getStore()->getConfig('web/default/cms_home_page');
					if ($cmsSingletonIdentifier === $homeIdentifier) {
						$show = true;
					}
				}
			}
			if ($show && ($route == 'customer' && ($action == 'login' || $action == 'forgotpassword' || $action == 'create'))) {
				$show = false;
			}
			if ($show) {
				$this->setTemplate('queldorei/' . $config['config']['slider'] . '.phtml');
			}
		}

		return $this;
	}

	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	public function getSlideshow()
	{
		if (!$this->hasData('shopperslideshow')) {
			$this->setData('shopperslideshow', Mage::registry('shopperslideshow'));
		}
		return $this->getData('shopperslideshow');

	}

	public function getSlides()
	{
		$config = Mage::getStoreConfig('shopperslideshow', Mage::app()->getStore()->getId());
		if ( $config['config']['slider'] == 'flexslider' ) {
			$model = Mage::getModel('shopperslideshow/shopperslideshow');
		} else {
			$model = Mage::getModel('shopperslideshow/shopperrevolution');
		}
		$slides = $model->getCollection()
			->addStoreFilter(Mage::app()->getStore())
			->addFieldToSelect('*')
			->addFieldToFilter('status', 1)
			->setOrder('sort_order', 'asc');
		return $slides;
	}

}