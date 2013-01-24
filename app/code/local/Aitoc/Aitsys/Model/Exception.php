<?php
class Aitoc_Aitsys_Model_Exception extends Exception
{
    static public function setErrorException()
    {
        $a = set_error_handler(create_function('$a, $b, $c, $d', 'throw new ErrorException($b, 0, $a, $c, $d);'), E_ALL);
    }
    
    static public function restoreErrorHandler()
    {
        restore_error_handler();
    }
}