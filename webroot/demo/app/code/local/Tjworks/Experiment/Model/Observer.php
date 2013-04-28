<?php

class Tjworks_Experiment_Model_Observer extends Varien_Event_Observer
{
    public function __construct()
    {
    }
    public function onEvent($observer){
      $event = $observer->getEvent();
      Mage::log("Got event: ". $event->getType(),null, 'test.log', true);
       Mage::log("BAD GIRL!", null, 'test.log', true);
      
      return $this;
    }
}
?>