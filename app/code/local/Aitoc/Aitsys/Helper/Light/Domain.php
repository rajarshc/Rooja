<?php
class Aitoc_Aitsys_Helper_Light_Domain extends Aitoc_Aitsys_Abstract_Helper
{    
    protected $_subZones = array( // in alphabetical order 
        'co',
        'com',
        'net',
        'org',
    );
        
    public function parseUrl( $url ) 
    {
        $domain = parse_url($url);
        if((false === $domain) || !isset($domain['host']))
        {
            $this->tool()->testMsg('Incorrect url ['.$url.'] given to parser.');
            $domain['host'] = '';
        }
        $host = explode('.', $domain['host']);
        
        //check if domain starts with www.
        if('www' == $host[0]) {
            array_shift($host);
        }
        
        //check if it's a double zone domain
        $domain['host_size'] = count($host);
        if($domain['host_size'] > 2 && in_array($host[$domain['host_size']-2], $this->_subZones))
        {
            $host[$domain['host_size']-2] .= '.' . array_pop($host);
            $domain['host_size'] -- ;
        }
        $domain['host_array'] = $host;
        //saving old host (can be with www)
        $domain['base_host'] = $domain['host'];
        //saving real host path (without www);
        $domain['host'] = implode('.', $host);
        //retrieve domain zone
        $domain['zone'] = array_pop($host);
        
        //break url into an array and removing '/' at the beginning and at the end to prevend empty values
        $path = trim(isset($domain['path'])? $domain['path'] : '','/');
        
        $domain['folder'] = '';
        $domain['admin'] = '';
        if($path != '') {
            $path = explode('/', $path);
            
            //retrieve admin path
            $domain['admin'] = array_pop($path);
            
            if(count($path)>0) {
                //check if url have a .php file
                if(false !== strpos($path[count($path)-1], '.php')) {
                    $domain['php_file'] = array_pop($path);
                }
                
                //join folders info into a string
                if(count($path)>0)
                    $domain['folder'] = '/' . join('/', $path);
            }
        }
        $domain['full_path'] = $domain['path'];
        //full path to admin without ant php file
        $domain['path'] = $domain['folder'] . '/' . $domain['admin'];
        $domain['admin_url'] = $domain['host'] . $domain['path'];
        
        return $domain;
    }
    
    public function getAdminUrl( $url ) {
        $domain = $this->parseUrl( $url );
        return $domain['admin_url'];
    }
    
    public function cleanDomain( $url, $data=null)
    {
        $host = parse_url($url, PHP_URL_HOST);
        if(false === $host || null === $host) 
        {
            Mage::throwException($this->__('Failed to parse url: '.$url.''));
        }
        if ('www.' == substr($host,0,4))
        {
            $host = substr($host,4);
        }
        
        return $host;
    }
    
}