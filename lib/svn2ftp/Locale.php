<?php

namespace svn2ftp;

use \Exception;

class Locale {
    
    const LOCALE_FOLDER = 'locale';
    const LOCALE_EXT = 'php';
    
    protected $_text = null;
    
    public function __construct($locale = null) {
        
        if($locale == null) {
            $locale = $this->getDefaultLocale();
        }
        
        $locale_file_path = realpath(self::LOCALE_FOLDER . DIRECTORY_SEPARATOR . $locale . '.' . self::LOCALE_EXT);
        
        if(!file_exists($locale_file_path)) {
            throw new Exception("Unable to locate a translation file!", 0, null);
        }
        
        $m = array();
        require($locale_file_path);
        
        $this->_text = $m;
    }
    
    public function get($key, $args = array()) {
        if(array_key_exists($key, $this->_text)) {
            
            $m = $this->_text[$key];
            
            if(is_scalar($args)) {
                $args = array($args);
            }
            
            if(is_array($args) && count($args)) {
                $m = vsprintf($m, $args);
            }
            
            return $m;
        }
        
        return $key;
    }
    
    protected function getDefaultLocale() {
        
        $matched = array();
        
        if(!preg_match('/(?:([a-z]+)_[A-Z]+)(\..+)?/', $_SERVER['LANG'], $matched)) {
            throw new Exception("Unable to identify a correct language code", 0, null);
        }
        
        return $matched[1];
    }
}
