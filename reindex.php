<?php
set_time_limit(0);

define('PATH_TO_MAGE', '/lib/Mage');
require_once realpath(PATH_TO_MAGE . '/app/Mage.php');

if (empty($argv[1])) {
    echo "Usage: php " . __FILE__ . " [all|<index>[,<index>[,...]]]\n";
    echo "\tIndexes:\n";
    echo "\t\tcataloginventory_stock\n";
    echo "\t\tcatalogsearch_fulltext\n";
    echo "\t\tcatalog_category_flat\n";
    echo "\t\tcatalog_category_product\n";
    echo "\t\tcatalog_product_attribute\n";
    echo "\t\tcatalog_product_flat\n";
    echo "\t\tcatalog_product_price\n";
    echo "\t\tcatalog_url\n";
    echo "\t\ttag_summary\n";
}

if ($argv[1] != 'all') {
    $indexes = explode(',', $argv[1]);
}

Mage::app('default', 'store')->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

echo "Reindexing:\n";
$processors = Mage::getSingleton('index/indexer')->getProcessesCollection();
foreach ($processors as $processor) {
    if (empty($indexes) || in_array($processor->getIndexerCode(), $indexes)) {
        echo "\t{$processor->getIndexer()->getName()}... ";
        $processor->reindexEverything();
        echo " [OK]\n";
    }
}
echo "Done\n";