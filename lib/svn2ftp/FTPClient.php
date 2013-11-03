<?php

namespace svn2ftp;

require_once 'FTPClient/ConnectionException.php';
require_once 'FTPClient/LoginException.php';
require_once 'FTPClient/CommandException.php';

use svn2ftp\FTPClient\ConnectionException;
use svn2ftp\FTPClient\LoginException;
use svn2ftp\FTPClient\CommandException;

class FTPClient {

    private $_ftp_handle;
    private $_path;
    
    public function open($host, $port = 21) {
        $this->_ftp_handle = ftp_connect($host, $port);
        
        if($this->_ftp_handle === false) {
            throw new ConnectionException();
        }
    }
    
    public function login($username, $password) {
        if(@ftp_login($this->_ftp_handle, $username, $password) === false) {
            throw new LoginException();
        }
    }
    
    public function close() {
        ftp_close($this->_ftp_handle);
    }
    
    public function chdir($path) {
        if(!@ftp_chdir($this->_ftp_handle, $path)) {
            throw new CommandException();
        }
        
        $this->_path = $path;
    }
    
    public function put($file, $base_local, $base_ftp) {
        
        $this->chdir($base_ftp);
        
        $path = explode('/', $file);
        array_shift($path);

        $conn_id = $this->_ftp_handle;
        
        $path_local = $base_local;
        $path_remote = $base_ftp;
        
        $path_remote_full = $base_ftp . $file;

        foreach($path as $dir) {
            $path_remote_prev = $path_remote;

            $path_local .= '/' . $dir;
            $path_remote .= '/' . $dir;

            if(is_dir($path_local)) {
                $ls = ftp_nlist($conn_id, $path_remote_prev);

                if(!in_array($path_remote, $ls)) {
                    ftp_mkdir($conn_id, $dir);
                }
                else {
                    ftp_chdir($conn_id, $dir);
                }
            }
            elseif($path_remote == $path_remote_full) {
                ftp_put($conn_id, $path_remote, $path_local, FTP_BINARY);
            }
        }

        sleep(1);
    }
    
    public function delete($file, $base_ftp) {
        
        $this->chdir($base_ftp);
        
        $conn_id = $this->_ftp_handle;
        
        $path = explode('/', $file);
        array_shift($path);

        $path_remote = $base_ftp;

        $is_file = false;
        foreach($path as $dir) {

            $ls = ftp_rawlist($conn_id, $path_remote);

            $dirs = array();
            $files = array();
            foreach($ls as $entry) {
                preg_match('/^(.){1}(?:.* [0-9]{2}:[0-9]{2})\s(.*)$/', $entry, $matched);
                if($matched[1] == 'd') {
                    $dirs[] = $matched[2];
                }
                else {
                    $files[] = $matched[2];
                }
            }

            $path_remote .= '/' . $dir;

            if(in_array($dir, $dirs)) {
                ftp_chdir($conn_id, $dir);
            }
            else {
                $is_file = true;
                break;
            }
        }

        if($path_remote == $base_ftp . $file_remote) {
            if($is_file) {
                ftp_delete($conn_id, $path_remote);
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
            preg_match('/^(.){1}(?:.* [0-9]{2}:[0-9]{2})\s(.*)$/', $entry, $matched);
            if($matched[1] == 'd') {
                $dirs[] = $matched[2];
            }
            else {
                $files[] = $matched[2];
            }
        }

        if(count($files)) {
            foreach($files as $file) {
                ftp_delete($conn_id, $file);
            }
        }

        if(count($dirs)) {
            foreach($dirs as $dir) {
                ftp_rmdir_recursive($conn_id, $path . '/' . $dir);
            }
        }

        ftp_cdup($conn_id);
        ftp_rmdir($conn_id, $path);
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