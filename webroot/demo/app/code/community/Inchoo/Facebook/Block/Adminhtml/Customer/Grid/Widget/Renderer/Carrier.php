<?php


class Inchoo_Facebook_Block_Adminhtml_Customer_Grid_Widget_Renderer_Carrier extends  Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select
{
    public function render(Varien_Object $row)
    {
        $formattedDate = '';
		
		
		 
      $customId = $row->getEntityId();
		
		$facebookid=$row->getFacebookUid();
		if($facebookid!=""){
			$label=$this->__("Facebook");	
		}else{
			$label=$this->__("Register");	
		}
		 
		 
		return $label;  /**/
	 
      
    }

    /*
    * Return dummy filter.
    */
    public function getFilter()
    {
        return false;
    }
}