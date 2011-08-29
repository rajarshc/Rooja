<?php

class TBT_Rewards_Model_Test_Suite_Rewards_Database
        extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Check database');
    }

    public function getDescription() {
        return $this->__('Check Sweet Tooth database for required tables and columns');
    }

    protected function generateSummary() {

        $cr = Mage::getSingleton('core/resource');

        $tableChecks = array();

        $tableChecks[$cr->getTableName('rewards_currency')] = array(
            'rewards_currency_id',
            'caption',
            'value',
            'active',
            'image',
            'image_width',
            'image_height',
            'image_write_quantity',
            'font',
            'font_size',
            'font_color',
            'text_offset_x',
            'text_offset_y'
        );
        $tableChecks[$cr->getTableName('rewards_customer')] = array(
            'rewards_customer_id',
            'rewards_currency_id',
            'customer_entity_id'
        );
        $tableChecks[$cr->getTableName('rewards_special')] = array(
            'rewards_special_id',
            'name',
            'description',
            'from_date',
            'to_date',
            'customer_group_ids',
            'is_active',
            'conditions_serialized',
            'points_action',
            'points_currency_id',
            'points_amount',
            'website_ids',
            'is_rss',
            'sort_order',
        );
        $tableChecks[$cr->getTableName('rewards_store_currency')] = array(
            'rewards_store_currency_id',
            'currency_id',
            'store_id'
        );
        $tableChecks[$cr->getTableName('rewards_transfer')] = array(
            'rewards_transfer_id',
            'customer_id',
            'quantity',
            'comments',
            'effective_start',
            'expire_date',
            'status',
            'currency_id',
            'creation_ts',
            'reason_id',
            'last_update_ts',
            'issued_by',
            'last_update_by'
        );
        $tableChecks[$cr->getTableName('rewards_transfer_reference')] = array(
            'rewards_transfer_reference_id',
            'reference_type',
            'reference_id',
            'rewards_transfer_id',
            'rule_id'
        );
        $tableChecks[$cr->getTableName('catalogrule')] = array(
            'points_action',
            'points_currency_id',
            'points_amount',
            'points_amount_step',
            'points_amount_step_currency_id',
            'points_max_qty',
            'points_catalogrule_simple_action',
            'points_catalogrule_discount_amount',
            'points_catalogrule_stop_rules_processing',
            'points_uses_per_product',
        );
        $tableChecks[$cr->getTableName('catalogrule_product_price')] = array(
            'rules_hash'
        );

        $tableChecks[$cr->getTableName('salesrule')] = array(
            'points_action',
            'points_currency_id',
            'points_amount',
            'points_amount_step',
            'points_amount_step_currency_id',
            'points_qty_step',
            'points_max_qty'
        );

        if (Mage::helper('rewards/version')->isBaseMageVersionAtLeast('1.3.2')) {
            $tableChecks[$cr->getTableName('sales_flat_quote')] = array(
                'cart_redemptions',
                'applied_redemptions',
                'rewards_discount_amount',
                'rewards_base_discount_amount',
                'rewards_discount_tax_amount',
                'rewards_discount_base_tax_amount'
            );

            $tableChecks[$cr->getTableName('sales_flat_quote_item')] = array(
                'earned_points_hash',
                'redeemed_points_hash',
                'row_total_before_redemptions'
            );
        }

        if (Mage::helper('rewards/version')->isBaseMageVersionAtLeast('1.4.1')) {

            $tableChecks[$cr->getTableName('sales_flat_order')] = array(
                'rewards_discount_amount',
                'rewards_base_discount_amount',
                'rewards_discount_tax_amount',
                'rewards_base_discount_tax_amount'
            );

            $tableChecks[$cr->getTableName('sales_flat_order_item')] = array(
                'earned_points_hash',
                'redeemed_points_hash',
                'row_total_before_redemptions'
            );
        }

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        foreach ($tableChecks as $table => $columns) {
            $query = "SHOW COLUMNS FROM $table";
            $table_schema = $read->fetchAll($query);

            $table_columns = array();
            foreach ($table_schema as $column_schema) {
                $table_columns[] = $column_schema['Field'];
            }

            if (!empty($table_columns)) {
                $this->addPass($this->__("Table %s found", $table));
            } else {
                $this->addFail($this->__("Table %s is missing", $table));
            }

            foreach ($columns as $column) {
                if (in_array($column, $table_columns)) {
                    $this->addPass($this->__("Table %s has column %s", $table,
                                    $column));
                } else {
                    $this->addFail($this->__("Table %s is missing column %s",
                                    $table, $column));
                }
            }
        }
    }

}
