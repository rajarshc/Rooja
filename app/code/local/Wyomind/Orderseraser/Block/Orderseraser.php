<?php

class Wyomind_Orderseraser_Block_Orderseraser extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
    	
     //on indique ou va se trouver le controller
     $this->_controller = 'erasergrid';
	 $this->_blockGroup = 'orderseraser';
	
     //le texte du header qui s’affichera dans l’admin
     $this->_headerText = "<strike style='color:white;'><span style='color:#EB5E00;'>".$this->__('Orders Eraser')."</span></strike>";
     //le nom du bouton pour ajouter une un contact
     parent::__construct();
	  $this->removeButton('add');
    }
}