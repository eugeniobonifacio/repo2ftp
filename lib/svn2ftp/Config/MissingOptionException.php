<?php

namespace svn2ftp\Config;

use \Exception;

class MissingOptionException extends Exception {
    
    private $_option = null;
    
    public function __construct($option) {
        parent::__construct("", 0, null);
        
        $this->_option = $option;
    }
    
    public function getOption() {
        return $this->_option;
    }
}