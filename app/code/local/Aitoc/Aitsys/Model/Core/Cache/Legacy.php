<?php

class Aitoc_Aitsys_Model_Core_Cache_Legacy extends Aitoc_Aitsys_Model_Core_Cache
{
    public function __construct($options = array())
    {
        if (!isset($options['prefix'])) {
            $this->_idPrefix = md5(dirname(Mage::getRoot()));
        }
        else {
            $this->_idPrefix = $options['prefix'];
        }
        
        $backend = '';
        if (isset($options['backend']))
        {
            $backend = strtolower($options['backend']);
        }
        $backendType = '';
        if (extension_loaded('apc') && ini_get('apc.enabled') && $backend == 'apc') 
        {
            $backendType = 'Apc';
            $backendAttributes = array(
                'cache_prefix' => $this->_idPrefix
            );
        } 
        elseif (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable') && $backend=='eaccelerator') 
        {
            $backendType = 'Eaccelerator';
            $backendAttributes = array(
                'cache_prefix' => $this->_idPrefix
            );
        } 
        elseif ('memcached' == $backend && extension_loaded('memcache')) 
        {
            $backendType = 'Memcached';
            $memcachedConfig = $options['memcached'];
            $backendAttributes = array(
                'compression'               => isset($memcachedConfig['compression'])            ? $memcachedConfig['compression'] : false,
                'cache_dir'                 => isset($memcachedConfig['cache_dir'])              ? $memcachedConfig['cache_dir'] : '',
                'hashed_directory_level'    => isset($memcachedConfig['hashed_directory_level']) ? $memcachedConfig['hashed_directory_level'] : '',
                'hashed_directory_umask'    => isset($memcachedConfig['hashed_directory_umask']) ? $memcachedConfig['hashed_directory_umask'] : '',
                'file_name_prefix'          => isset($memcachedConfig['file_name_prefix'])       ? $memcachedConfig['file_name_prefix'] : '',
                'servers'                   => array(),
            );
            foreach ($memcachedConfig['servers'] as $serverConfig) 
            {
                $backendAttributes['servers'][] = array(
                    'host'          => $serverConfig['host'],
                    'port'          => $serverConfig['port'],
                    'persistent'    => $serverConfig['persistent'],
                );
            }
        }
        
        if (!$backendType) {
            $backendType = $this->_defaultBackend;
            $backendAttributes = $this->_defaultBackendOptions;
            $backendAttributes['cache_dir'] = dirname(Mage::getRoot()).DS.'var'.DS.'cache';
        }
        
        if (isset($options['lifetime'])) 
        {
            $lifetime = (int) $options['lifetime'];
        }
        else 
        {
            $lifetime = self::DEFAULT_LIFETIME;
        }
        
        $this->_frontend = Zend_Cache::factory(
            'Core',
            $backendType,
            array(
                'caching'                   => true,
                'lifetime'                  => $lifetime,
                'automatic_cleaning_factor' => 0,
            ),
            $backendAttributes,
            false,
            false,
            true
        );
    }
}