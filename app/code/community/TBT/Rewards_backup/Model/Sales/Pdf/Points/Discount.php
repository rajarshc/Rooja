<?php

class TBT_Rewards_Model_Sales_Pdf_Points_Discount extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $order = $this->getOrder();
        $rewards_discount_amount = $order->getStore()->formatPrice($order->getRewardsDiscountAmount(), false);
        return array(array(
            'amount' => $rewards_discount_amount,
            'label' => Mage::helper('rewards')->__("Item Discounts"),
            'font_size' => $this->getFontSize() ? $this->getFontSize() : 7
        ));
    }
}
