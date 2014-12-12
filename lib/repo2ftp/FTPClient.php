<?php

namespace repo2ftp;

require_once 'FTPClient/ConnectionException.php';
require_once 'FTPClient/LoginException.php';
require_once 'FTPClient/CommandException.php';
require_once 'FTPClient/FileNotFoundException.php';
require_once 'FTPClient/UnableToDeleteException.php';

use repo2ftp\FTPClient\ConnectionException;
use repo2ftp\FTPClient\LoginException;
use repo2ftp\FTPClient\CommandException;
use repo2ftp\FTPClient\FileNotFoundException;
use repo2ftp\FTPClient\UnableToDeleteException;

class FTPClient {

    private $_ftp_handle;
    private $_path;
    
    public function open($host, $port = 21) {
        $this->_ftp_handle = ftp_connect($host, $port);
        
        if($this->_ftp_handle === false) {
            throw new ConnectionException("Cannot open connection");
        }
    }
    
    public function login($username, $password) {
        if(@ftp_login($this->_ftp_handle, $username, $password) === false) {
            throw new LoginException("Wrong username or password");
        }
    }
    
    public function close() {
        ftp_close($this->_ftp_handle);
    }
    
    public function chdir($path) {
        if(!@ftp_chdir($this->_ftp_handle, $path)) {
            throw new CommandException("Cannot chdir to $path");
        }
        
        $this->_path = $path;
    }
    
    public function mkdir($path_relative, $path_base = '.') {
        
        $this->chdir($path_base);
        
        $path = explode('/', $path_relative);
        
        $conn_id = $this->_ftp_handle;
        
        $path_remote = $path_base;
        
        foreach($path as $dir) {
            $path_remote_prev = $path_remote;

            $path_remote .= '/' . $dir;

            $ls = ftp_nlist($conn_id, null);

            if($ls === false) {
                throw new CommandException("Cannot list folder content $path_remote_prev");
            }

            if(!in_array($dir, $ls)) {
                if(false === ftp_mkdir($conn_id, $dir)) {
                    throw new CommandException("Cannot create folder `$dir`");
                }
            }
            
            if(false === ftp_chdir($conn_id, $dir)) {
                throw new CommandException("Cannot chdir to $dir");
            }
        }
    }
    
    public function put($file, $base_local, $base_ftp, $name_ftp = null) {
       
        $conn_id = $this->_ftp_handle;
        
        $path_local = $base_local . DIRECTORY_SEPARATOR . $file;
        $path_remote = $base_ftp . DIRECTORY_SEPARATOR . ($name_ftp == null ? $file : $name_ftp);

        if(file_exists($path_local)) {
            ftp_put($conn_id, $path_remote, $path_local, FTP_BINARY);
        }
        else {
            throw new FileNotFoundException("File not found on local path $path_local");
        }
    }
    
    public function delete($file, $base_ftp) {
        $this->chdir($base_ftp);
        $conn_id = $this->_ftp_handle;
        
        $path = explode('/', $file);

        $path_remote = $base_ftp;

        $is_file = false;
        foreach($path as $dir) {
            $ls = ftp_rawlist($conn_id, $path_remote);

            $dirs = array();
            $files = array();
            foreach($ls as $entry) {
                $matched = preg_split("/[\s]+/", $entry, 9, PREG_SPLIT_NO_EMPTY);
                if($matched[0][0] == 'd') {
                    $dirs[] = $matched[8];
                }
                else {
                    $files[] = $matched[8];
                }
            }

            $path_remote .= '/' . $dir;

            if(in_array($dir, $dirs)) {
                if(false === ftp_chdir($conn_id, $dir)) {
                    throw new CommandException("Cannot chdir to $dir");
                }
            }
            else {
                $is_file = true;
                break;
            }
        }

        if($path_remote == $base_ftp . '/' . $file) {
            if($is_file) {
                
                if(!in_array($file, $files)) {
                    throw new UnableToDeleteException("File not found $file");
                }
                
                if(@ftp_delete($conn_id, $path_remote) === false) {
                    throw new UnableToDeleteException("Cannot delete file $path_remote");
                }
            }
            else {
                $this->rmdir($path_remote);
            }
        }

        sleep(3);
    }
    
    public function rmdir($path) {
        $this->chdir($path);
        
        $conn_id = $this->_ftp_handle;

        $ls = ftp_rawlist($conn_id, $path);

        $dirs = array();
        $files = array();
        foreach($ls as $entry) {
            $matched = preg_split("/[\s]+/", $entry, 9, PREG_SPLIT_NO_EMPTY);
            if($matched[0][0] == 'd') {
                $dirs[] = $matched[8];
            }
            else {
                $files[] = $matched[8];
            }
        }

        if(count($files)) {
            foreach($files as $file) {
                if(@ftp_delete($conn_id, $path . '/' . $file) === false) {
                    throw new UnableToDeleteException('Cannot delete file ' . ($path . '/' . $file));
                }
            }
        }

        if(count($dirs)) {
            foreach($dirs as $dir) {
                $this->rmdir($path . '/' . $dir);
            }
        }

        ftp_cdup($conn_id);

        if(@ftp_rmdir($conn_id, $path) === false) {
            throw new UnableToDeleteException('Cannot delete folder ' . $path);
        }
    }

    protected function isDir($dir) {
        
        $conn_id = $this->_ftp_handle;
        
        $original_directory = ftp_pwd($conn_id);
        if(@ftp_chdir($conn_id, $dir)) {
            ftp_chdir($conn_id, $original_directory);
            return true;
        }

        return false;
    }
}