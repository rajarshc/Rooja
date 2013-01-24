<?php

class Aitoc_Aitsys_Model_Core_Cache
{
    const DEFAULT_LIFETIME  = 7200;
    protected $_idPrefix    = '';
    protected $_frontend    = null;
    
    protected $_defaultBackend = 'File';
    protected $_defaultBackendOptions = array(
        'hashed_directory_level'    => 1,
        'hashed_directory_umask'    => 0777,
        'file_name_prefix'          => 'mage',
    );
    
    /**
     * DB resource
     *
     * @var Aitoc_Aitsys_Model_Core_Resource
     */
    protected $_resource;
    
    public function __construct($options = array(), $dbSettings = array())
    {
        $this->_defaultBackendOptions['cache_dir'] = dirname(Mage::getRoot()).DS.'var'.DS.'cache';
        
        // Initialize id prefix
        $this->_idPrefix = isset($options['id_prefix']) ? $options['id_prefix'] : '';
        if (!$this->_idPrefix && isset($options['prefix'])) {
            $this->_idPrefix = $options['prefix'];
        }
        if (empty($this->_idPrefix)) {
            $etcDir = Mage::getRoot().DS.'etc';
            $this->_idPrefix = substr(md5($etcDir), 0, 3).'_';
        }
        
        // init database resource
        $this->_resource = new Aitoc_Aitsys_Model_Core_Cache_Resource($dbSettings);
        
        // collect cache options
        $backend    = $this->_getBackendOptions($options);
        $frontend   = $this->_getFrontendOptions($options);
        
        $this->_frontend = Zend_Cache::factory(
            'Varien_Cache_Core', 
            $backend['type'], 
            $frontend, 
            $backend['options'],
            true, true, true
        );
    }
    
    protected function _getBackendOptions(array $cacheOptions)
    {
        $enable2levels = false;
        $type   = isset($cacheOptions['backend']) ? $cacheOptions['backend'] : $this->_defaultBackend;
        if (isset($cacheOptions['backend_options']) && is_array($cacheOptions['backend_options'])) {
            $options = $cacheOptions['backend_options'];
        } else {
            $options = array();
        }

        $backendType = false;
        switch (strtolower($type)) {
            case 'sqlite':
                if (extension_loaded('sqlite') && isset($options['cache_db_complete_path'])) {
                    $backendType = 'Sqlite';
                }
                break;
            case 'memcached':
                if (extension_loaded('memcache')) {
                    if (isset($cacheOptions['memcached'])) {
                        $options = $cacheOptions['memcached'];
                    }
                    $enable2levels = true;
                    $backendType = 'Memcached';
                }
                break;
            case 'apc':
                if (extension_loaded('apc') && ini_get('apc.enabled')) {
                    $enable2levels = true;
                    $backendType = 'Apc';
                }
                break;
            case 'xcache':
                if (extension_loaded('xcache')) {
                    $enable2levels = true;
                    $backendType = 'Xcache';
                }
                break;
            case 'eaccelerator':
            case 'varien_cache_backend_eaccelerator':
                if (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable')) {
                    $enable2levels = true;
                    $backendType = 'Varien_Cache_Backend_Eaccelerator';
                }
                break;
            case 'database':
                $backendType = 'Varien_Cache_Backend_Database';
                $options = $this->getDbAdapterOptions();
                break;
            default:
                if ($type != $this->_defaultBackend) {
                    try {
                        if (class_exists($type, true)) {
                            $implements = class_implements($type, true);
                            if (in_array('Zend_Cache_Backend_Interface', $implements)) {
                                $backendType = $type;
                            }
                        }
                    } catch (Exception $e) {
                    }
                }
        }

        if (!$backendType) {
            $backendType = $this->_defaultBackend;
            foreach ($this->_defaultBackendOptions as $option => $value) {
                if (!array_key_exists($option, $options)) {
                    $options[$option] = $value;
                }
            }
        }

        $backendOptions = array('type' => $backendType, 'options' => $options);
        if ($enable2levels) {
            $backendOptions = $this->_getTwoLevelsBackendOptions($backendOptions, $cacheOptions);
        }
        return $backendOptions;
    }

    public function getDbAdapterOptions()
    {
        $options['adapter_callback'] = array($this, 'getDbAdapter');
        $options['data_table']  = $this->_resource->getTableName('core/cache');
        $options['tags_table']  = $this->_resource->getTableName('core/cache_tag');
        return $options;
    }
    
    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter()
    {
        return $this->_getResource()->getConnection();
    }
    
    /**
     * @return Aitoc_Aitsys_Model_Core_Resource
     */
    protected function _getResource()
    {
        return $this->_resource;
    }
    
    protected function _getTwoLevelsBackendOptions($fastOptions, $cacheOptions)
    {
        $options = array();
        $options['fast_backend']                = $fastOptions['type'];
        $options['fast_backend_options']        = $fastOptions['options'];
        $options['fast_backend_custom_naming']  = true;
        $options['fast_backend_autoload']       = true;
        $options['slow_backend_custom_naming']  = true;
        $options['slow_backend_autoload']       = true;

        if (isset($cacheOptions['slow_backend'])) {
            $options['slow_backend'] = $cacheOptions['slow_backend'];
        } else {
            $options['slow_backend'] = $this->_defaultBackend;
        }
        if (isset($cacheOptions['slow_backend_options'])) {
            $options['slow_backend_options'] = $cacheOptions['slow_backend_options'];
        } else {
            $options['slow_backend_options'] = $this->_defaultBackendOptions;
        }
        if ($options['slow_backend'] == 'database') {
            $options['slow_backend'] = 'Varien_Cache_Backend_Database';
            $options['slow_backend_options'] = $this->getDbAdapterOptions();
        }

        $backend = array(
            'type'      => 'TwoLevels',
            'options'   => $options
        );
        return $backend;
    }

    protected function _getFrontendOptions(array $cacheOptions)
    {
        $options = isset($cacheOptions['frontend_options']) ? $cacheOptions['frontend_options'] : array();
        if (!array_key_exists('caching', $options)) {
            $options['caching'] = true;
        }
        if (!array_key_exists('lifetime', $options)) {
            $options['lifetime'] = isset($cacheOptions['lifetime']) ? $cacheOptions['lifetime'] : self::DEFAULT_LIFETIME;
        }
        if (!array_key_exists('automatic_cleaning_factor', $options)) {
            $options['automatic_cleaning_factor'] = 0;
        }
        $options['cache_id_prefix'] = $this->_idPrefix;
        return $options;
    }
    
    /**
     * Get cache frontend API object
     *
     * @return Zend_Cache_Core
     */
    public function getFrontend()
    {
        return $this->_frontend;
    }
    
    public function flush()
    {
        $res = $this->getFrontend()->clean();
        return $res;
    }
    
}
