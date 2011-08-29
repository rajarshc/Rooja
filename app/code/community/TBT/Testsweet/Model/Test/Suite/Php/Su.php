<?php

class TBT_Testsweet_Model_Test_Suite_Php_Su extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('PHP - Check if is run under a [Switch User] ');
    }

    public function getDescription() {
        return $this->__('System admins often times run PHP as a different user for extra protection.');
    }

    protected function generateSummary() {
      

        /*
         * TODO: remove cleanup this area...
         * 
          What user is PHP running as?
         * Helpfull for suexec and suphp to be suere files are chown correct
         * 
          > script executing the "id" command:
          >
          > <?php
          > system('id');
          > ?>
          >

          echo ‘Current script owner: ‘ . get_current_user(). ‘<br>’;
          echo ‘UID:’ . getmyuid() . ‘<br>’;
          echo ‘GID:’ . getmygid() . ‘<br>’;
          echo ‘PID:’ . getmypid() . ‘<br>’;
          echo ‘<br>’;
          echo “PHP runs under the user: [" . system('whoami') . "]<br>”
          echo “PHP runs under the user: [" . system('id') . "]<br>”


          mod_suphp

          Then browse everything and look where it says Server API. I believe if it the value says Apache then its not running PHP using suPHP.
         *  If it says CGI then I believe it is running suPHP.
         * 
         */
        
        ob_start();
        phpinfo();
        $phpinfo = ob_get_contents();
        ob_end_clean();
                
        
        // TODO: TEST suphp - suexec strripos($phpinfo, "suphp") these are guesses, I neede a system to find what dection works best
        // suphp
        if (strripos($phpinfo, "suphp")) {
            $this->addWarning($this->__("PHP might be running with suphp"), $this->__("filesystem might need to be: chown [user]:[group], chmod -R u=rwx,g=rx,o=rx ./[magento]/"));
        } else {
            $this->addPass($this->__("PHP does not look to be using suphp."));
        }

        // PHPsuexec
        $phpSuExec_found = false;
        if (strripos($phpinfo, "suexec") === true)
            $phpSuExec_found = true;

        // this worked for a client that had nothing in the phpinfo
        //$found = @exec("grep -R 'suexec' /etc/apache2/mods-available");
        $found = @exec("grep -R 'suexec' /etc/apache2/mods-enabled");
        if (strlen($found) > 5)
            $phpSuExec_found = true;

        if ($phpSuExec_found) {
            $this->addWarning($this->__("PHP might be running with suexec."), $this->__("filesystem might need to be: chown [user]:[group], chmod -R u=rwx,g=rx,o=rx ./[magento]/"));
        } else {
            $this->addPass($this->__("PHP does not look to be using suexec."));
        }
        
    }

}
