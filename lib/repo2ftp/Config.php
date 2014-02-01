<?php

namespace repo2ftp;

require_once 'Config/MissingOptionException.php';
require_once 'Config/OptionNotFoundException.php';

use repo2ftp\Config\MissingOptionException;
use repo2ftp\Config\OptionNotFoundException;

class Config {
    
    private $options_mandatory = array(
        'ftp.host', 
        'ftp.username', 
        'ftp.password', 
        'module.%module%.type', 
        'module.%module%.path.local', 
        'module.%module%.path.repository', 
        'module.%module%.path.ftp'
    );
    
    private $options_optional = array(
        'config.name', 
        'config.description',
        'module.%module%.path.exclude'
    );
    
    private $_options = array();

    public function __construct($module, $path) {
        $config_options = parse_ini_file($path);   

        // READING
        $this->_options = array();
        foreach ($this->options_mandatory as $option) {
            
            $option = str_replace('%module%', $module, $option);
            
            if(!array_key_exists($option, $config_options)) {
                throw new MissingOptionException($option);
            }
            
            $this->_options[$option] = $config_options[$option];
        }
        
        foreach ($this->options_optional as $option) {
            
            $option = str_replace('%module%', $module, $option);
            
            if(array_key_exists($option, $config_options)) {
                $this->_options[$option] = $config_options[$option];
            }
        }
    }
    
    public function get($key) {
        if(!array_key_exists($key, $this->_options)) {
            throw new OptionNotFoundException();
        }
        
        return $this->_options[$key];
    }
    
    public function exists($key) {
        return array_key_exists($key, $this->_options);
    }
}

