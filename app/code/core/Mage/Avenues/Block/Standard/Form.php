<?php
class Mage_Avenues_Block_Standard_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('Avenues/standard/form.phtml');
        parent::_construct();
    }
}