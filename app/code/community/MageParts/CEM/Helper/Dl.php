<?php 

class MageParts_CEM_Helper_Dl extends Mage_Core_Helper_Abstract {
    var $tempFolder;
    var $tempFiles = array();

    public function __destruct () {
        foreach ($this->tempFiles as $file) {
            unlink($file['temp']);
        }
    }
    
    function __construct ()
    {
        $this->tempFolder = 'var/tmp/';
    }
    
    public function dl_tmp_file ($url) {
        array_unshift($this->tempFiles, array(
            'extension'=> array_pop(explode('.', $url)),
            'original'=> basename($url),
            'temp'=> $this->tempFolder . md5(microtime()),
        ));
        $ch = curl_init($url);
        $fp = @fopen($this->tempFiles[0]['temp'], 'w+');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return $this->tempFiles[0]['temp'];
    }
    
    
    public function fopen ($url, $mode) {
        $fn = $this->dl_tmp_file($url);
        return @fopen($fn, $mode);
    }
    
    public function file_get_contents ($url) {
        $fn = $this->dl_tmp_file($url);
        return file_get_contents($fn);
    }
    public function read ($index = 0) {
        return file_get_contents($this->tempFiles[$index]['temp']);
    }
    
    public function readArray ($index = 0)
    {
        return file($this->tempFiles[$index]['temp']);
    }
    
    public function listFiles () {
        return $this->tempFiles;
    }
    
    public function save ($path, $index = 0) {
        copy($this->tempFiles[$index]['temp'], (is_dir($path) ? $path . $this->tempFiles[$index]['original'] : $path));
    }
}