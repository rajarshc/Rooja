<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Filesystem extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - Filesystem');
    }

    public function getDescription() {
        return $this->__('Check that Magento has read/write on filesystem.');
    }

    protected function generateSummary() {

        $check_writeable = array(
            Mage::getBaseDir('tmp'),
            Mage::getBaseDir('log'),
            Mage::getBaseDir('etc'),
            Mage::getBaseDir('upload'),
        );

        foreach ($check_writeable as $path) {
            if (is_readable($path) && is_writable($path))
                $this->addPass($this->__('Seems accessable %s', $path));
            else
                $this->addFail($this->__('Check filesystem access on %s', $path));
        }

        /*
         * 
          base	Mage::getBaseDir()
          Mage::getBaseDir('base')	/var/www/magento/
          app	Mage::getBaseDir('app')	/var/www/magento/app/
          code	Mage::getBaseDir('code')	/var/www/magento/app/code
          design	Mage::getBaseDir('design')	/var/www/magento/app/design/
          etc	Mage::getBaseDir('etc')	/var/www/magento/app/etc
          lib	Mage::getBaseDir('lib')	/var/www/magento/lib
          locale	Mage::getBaseDir('locale')	/var/www/magento/app/locale
          media	Mage::getBaseDir('media')	/var/www/magento/media/
          skin	Mage::getBaseDir('skin')	/var/www/magento/skin/
          var	Mage::getBaseDir('var')	/var/www/magento/var/
          tmp	Mage::getBaseDir('tmp')	/var/www/magento/var/tmp
          cache	Mage::getBaseDir('cache')	/var/www/magento/var/cache
          log	Mage::getBaseDir('log')	/var/www/magento/var/log
          session	Mage::getBaseDir('session')	/var/www/magento/var/session
          upload	Mage::getBaseDir('upload')	/var/www/magento/media/upload
          export	Mage::getBaseDir('export')	/var/www/magento/var/export
         */


        $target = Mage::getBaseDir('base');
        // dont folow symlinks because this could cause a recursive loop
        //if (version_compare(phpversion(), '5.2.11', '>='))
        //    $directory = new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        //else
        $directory = new RecursiveDirectoryIterator($target);
        $iterator = new RecursiveIteratorIterator($directory);

        $not_readable_count = 0;
        foreach ($iterator as $fullpath => $file) {
            // ignore cache directory in unix style path
            if (strpos($fullpath, '/var/cache/'))
                continue;

            if (!is_readable($fullpath)) {
                $this->addNotice($this->__('Not accessable %s', $fullpath));
                $not_readable_count++;
            }
            if ($not_readable_count > 50) {
                $this->addWarning($this->__('More than 50 none accessible files... skipping test.'));
                break;
            }
        }
    }

}
