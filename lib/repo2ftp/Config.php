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
        'path.base.local', 
        'path.base.repository', 
        'path.base.ftp'
    );
    
    private $options_optional = array(
        'config.name', 
        'config.description'
    );
    
    private $_options = array();

    public function __construct($path) {
        $config_options = parse_ini_file($path);   

        // READING
        $this->_options = array();
        foreach ($this->options_mandatory as $option) {
            if(!array_key_exists($option, $config_options)) {
                throw new MissingOptionException($option);
            }
            
            $this->_options[$option] = $config_options[$option];
        }
        
        foreach ($this->options_optional as $option) {
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
}

