<?php
class BeeShopy_BeeSocial_Block_Iframe
    extends Mage_Core_Block_Abstract
    implements Mage_Widget_Block_Interface
{

    protected function _toHtml()
    {
      $product = Mage::registry('current_product');
      $domain = urlencode(Mage::getBaseURL());
      //$url =  urlencode(Mage::helper('core/url')->getCurrentUrl());
      $url =  urlencode($product->getUrlInStore(array('_ignore_category' => true, '_nosid' => true)));

      if($product){
        $html = "
          <div id='beesocial' class='product-view' style='text-align:center;'".
          "  data-domain='".$domain.
          "' data-product-id='".$product->getId(). 
          "' data-url='".$url.
          "' data-fb-comment-style='".Mage::getStoreConfig('beesocial_options/general/color').
          "' data-css-style='".Mage::getStoreConfig('beesocial_options/general/style').
          "' data-disable-like='".Mage::getStoreConfig('beesocial_options/general/disable_fb_like').
          "' data-disable-twitter='".Mage::getStoreConfig('beesocial_options/general/disable_twitter').
          "' data-disable-comment='".Mage::getStoreConfig('beesocial_options/general/disable_comments').
          "' data-disable-plusone='".Mage::getStoreConfig('beesocial_options/general/disable_plusone').
          "' data-fb-comment-num-post='".Mage::getStoreConfig('beesocial_options/fb_comments/number').
          "' data-comment-width='".Mage::getStoreConfig('beesocial_options/fb_comments/width').
          "' platform='magento'".
          "' data-twitter-text='".Mage::getStoreConfig('beesocial_options/twitter/text').
          "' ></div> <script type='text/javascript' src='//www.beetailer.com/javascripts/beetailer.js?v=120'></script>
        <style>#beesocial iframe{width:".(Mage::getStoreConfig('beesocial_options/fb_comments/width') + 10)."px;}</style>";
        return $html;
      }
    }
} 
