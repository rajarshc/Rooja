<?php

class Magestore_Affiliateplus_Block_Adminhtml_Field_Clone extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_affiliateConfigData;
    protected $_affiliateConfigRoot;
    
    protected function _construct()
    {
        parent::_construct();
        $this->_affiliateConfigRoot = Mage::getConfig()->getNode(null,
            $this->getScope(),
            $this->getScopeCode()
        );
        $this->_affiliateConfigData = Mage::getModel('adminhtml/config_data')
            ->setSection($this->getRequest()->getParam('section', ''))
            ->setWebsite($this->getRequest()->getParam('website', ''))
            ->setStore($this->getRequest()->getParam('store', ''))
            ->load();
    }
    
    public function getScope()
    {
        $scope = $this->getData('scope');
        if (is_null($scope)) {
            if ($this->getRequest()->getParam('store', '')) {
                $scope = Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES;
            } elseif ($this->getRequest()->getParam('website', '')) {
                $scope = Mage_Adminhtml_Block_System_Config_Form::SCOPE_WEBSITES;
            } else {
                $scope = Mage_Adminhtml_Block_System_Config_Form::SCOPE_DEFAULT;
            }
            $this->setData('scope', $scope);
        }
        return $scope;
    }
    
    public function getScopeCode()
    {
        $scope = $this->getData('scope_code');
        if (is_null($scope)) {
            if ($this->getRequest()->getParam('store', '')) {
                $scope = $this->getRequest()->getParam('store', '');
            } elseif ($this->getRequest()->getParam('website', '')) {
                $scope = $this->getRequest()->getParam('website', '');
            } else {
                $scope = '';
            }
            $this->setData('scope_code', $scope);
        }
        return $scope;
    }
    
    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $fieldConfig = $element->getFieldConfig();
        $clonePath  = (string) $fieldConfig->clone_path;
        $clonePaths = explode('/', $clonePath);
        
        // Prepare value for cloned element
        $name       = 'groups['.$clonePaths[1].'][fields]['.$clonePaths[2].'][value]';
        if (isset($this->_affiliateConfigData[$clonePath])) {
            $data = $this->_affiliateConfigData[$clonePath];
            $inherit = false;
        } else {
            $data = $this->_affiliateConfigRoot->descend($clonePath);
            $inherit = true;
        }
        if ($fieldConfig->backend_model) {
            $model = Mage::getModel((string)$fieldConfig->backend_model);
            if (!$model instanceof Mage_Core_Model_Config_Data) {
                Mage::throwException('Invalid config field backend model: '.(string)$fieldConfig->backend_model);
            }
            $model->setPath($clonePath)->setValue($data)->afterLoad();
            $data = $model->getValue();
        }
        
        $element->setName($name)
            ->setValue($data)
            ->setInherit($inherit);
        
        // Render Element to HTML
        $html = parent::render($element);
        
        // Prepare Javascript for cloned element
        $cloneId = $element->getHtmlId();
        $origId  = implode('_', $clonePaths);
        $html .= "<script type='text/javascript'>
Event.observe(window, 'load', function() {
    $('$cloneId').observe('change', function(){
        Form.Element.setValue($('$origId'), Form.Element.getValue($('$cloneId')));
    });
    $('$origId').observe('change', function(){
        Form.Element.setValue($('$cloneId'), Form.Element.getValue($('$origId')));
    });";
        if ($element->getCanUseWebsiteValue() || $element->getCanUseDefaultValue()) {
            $html .= "
    $('{$cloneId}_inherit').observe('click', function(){
        var el = $('{$origId}_inherit');
        el.checked = $('{$cloneId}_inherit').checked;
        toggleValueElements(el, Element.previous(el.parentNode));
    });
    $('{$origId}_inherit').observe('click', function(){
        var el = $('{$cloneId}_inherit');
        el.checked = $('{$origId}_inherit').checked;
        toggleValueElements(el, Element.previous(el.parentNode));
    });";
        }
        $html .= "
});
</script>";
        
        return $html;
    }
}
