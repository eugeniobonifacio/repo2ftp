<?php

namespace repo2ftp;

class Job {
    private $_to_upload = array();
    private $_to_delete = array();
    
    public function addToUpload($file) {
        $k = md5($file);

        if(!array_key_exists($k, $this->_to_upload) && !array_key_exists($k, $this->_to_delete)) {
            $this->_to_upload[$k] = $file;
            asort($this->_to_upload);
        }
    }

    public function addToDelete($file) {
        $k = md5($file);

        if(array_key_exists($k, $this->_to_upload)) {
            unset($this->_to_upload[$k]);
        }
        
        if(!array_key_exists($k, $this->_to_delete)) {
            $this->_to_delete[$k] = $file;
            asort($this->_to_delete);
        }     
    }
    
    public function getFilesToUpload() {
        return $this->_to_upload;
    }
    
    public function getFilesToDelete() {
        return $this->_to_delete;
    }
}

