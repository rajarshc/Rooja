<?php

/**
 * @nelkaake 22/01/2010 3:54:41 AM : points expiry
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */

require_once("AbstractController.php");
class TBT_Rewards_Debug_InstallController extends TBT_Rewards_Debug_AbstractController
{
    const SECS_IN_DAY = 86400;
    const SECS_IN_HOUR = 3600;
    const SECS_IN_MIN = 60;
    
    public function indexAction()
    {
        echo "This tests installation things. <BR />";
        echo "<a href='". Mage::getUrl('rewards/debug_install/reinstallRewardsDb') ."'>Reinstall Core ST DB</a> - Forces DB re-install. <BR />";
        
        exit;
    }


    public function reinstallRewardsDbAction() {
    	
    	$this->resetCoreResourceDb();
        Mage::app()->getCacheInstance()->flush();
        echo(Mage::helper('adminhtml')->__("The cache storage has been flushed."));
    	$url = Mage::getUrl('rewards/debug_install/index');
        echo(Mage::helper('adminhtml')->__("Redirecting..."));
        echo "
	        <script type=\"text/javascript\">
			<!--
			window.location = '{$url}'
			//-->
			</script>
        ";
        exit;
    	//$this->indexAction();
    }
    

    protected function resetCoreResourceDb() {
        $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $conn->beginTransaction();
        
        $this->_clearDbInstallMemory($conn, 'rewards_setup');
        	
        
        $conn->commit();
    	return $this;
    	
    }
    
    public function _clearDbInstallMemory($conn, $code) {
    	
        $table_prefix = Mage::getConfig()->getTablePrefix();
        $conn->query("
			DELETE FROM    `{$table_prefix}core_resource`  
			WHERE    `code` = '{$code}'
			;
        ");
        echo "Resource DB for {$code} has been cleared";
        
        return $this;
    }
    
}