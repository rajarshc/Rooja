<?php
/**
 * @category   MagentoPycho
 * @package    MagentoPycho_Customer
 * @author     developer@magepsycho.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoPycho_Customer_Block_System_Config_Extension
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<div style="background:#EAF0EE;border:1px solid #CCCCCC;margin-bottom:10px;padding:10px 5px 5px 10px;">
    <h4>About Custom Login Redirect</h4>
    <p><a href="http://www.magentocommerce.com/magento-connect/MagePsycho/extension/3763/custom_login_redirect" target="_blank">Custom Login Redirect</a> allows customer to redirect to custom page after login.
</p>
<br />
<h4>Configuration</h4>
<p>Go to: System >> Configuration >> Customers >> Custom Login Redirect >> Configure your settings here.</p>
</div>';
        
        return $html;
    }
}
