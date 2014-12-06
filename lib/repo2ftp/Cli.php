<?php

namespace repo2ftp;

require_once('Locale.php');
require_once('Config.php');
require_once('Version.php');

use repo2ftp\Config\MissingOptionException;
use repo2ftp\Config\OptionNotFoundException;

class Cli {  
    
    private $_info = "REPO2FTP %s - Eugenio Bonifacio http://www.eugeniobonifacio.com";
    
    /**
     *
     * @var Locale
     */
    protected $_locale;
    protected $_config;
    protected $_project;
    protected $_module;
    protected $_revision;
    
    protected $_debug;
    
    protected $_base_path;
    
    protected $_upload_action;
    protected $_delete_action;
    
    public function __construct($base_path) {
        
        // EXTRACT CLI OPTIONS
        $opts = 'l:r:c:m:d:u:e';
        $cli_options = getopt($opts);
        
        // DEBUG
        $this->_debug = array_key_exists('d', $cli_options);
        
        // LANGUAGE
        if(array_key_exists('l', $cli_options)) {
            $this->_locale = new Locale($cli_options['l']);
        }
        else {
            $this->_locale = new Locale();
        }
        
        // MODULE
        if(array_key_exists('m', $cli_options)) {
            $this->_module = $cli_options['m'];
        }
        else {
            $this->_module = 'base';
        }
        
        $this->_upload_action = true;
        $this->_delete_action = true;
        
        if(array_key_exists('u', $cli_options)) {
            $this->_upload_action = true;
            $this->_delete_action = false;
        }
        
        if(array_key_exists('e', $cli_options)) {
            $this->_upload_action = false;
            $this->_delete_action = true;
        }
        
        // WELCOME MESSAGE
        $welcome_message = $this->getInfo();
        $line = str_pad('', strlen($welcome_message), '*');
        
        $this->outputnl(":");
        $this->outputnl(":" . $line);
        $this->outputnl(":" . $welcome_message);
        $this->outputnl(":" . $line);
//        $this->outputnl(":");
        
        $this->_base_path = $base_path;

        $this->outputnl("cli.module.name", $this->_module);
        
        if(array_key_exists('c', $cli_options)) {
            $this->_project = $cli_options['c'];

            $config_path = realpath($this->_base_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $this->_project . '.ini');

            $this->outputnl("cli.config.file", $config_path);

            try {
                $this->_config = new Config($this->_module, $config_path);
            }
            catch(MissingOptionException $ex) {
                $this->error("cli.config.option.missing", $ex->getOption());
            }

            $this->outputnl("cli.ok");
        }
        else {
            $this->error("cli.config.missing");
        }
        
        if(!array_key_exists('r', $cli_options)) {
            $this->error("cli.repository.revision.missing");            
        }
        
        $this->_revision = $cli_options['r'];
        
//        $this->outputnl("cli.repository.revision", $this->_revision);
    }
    
    public function getProject() {
        return $this->_project;
    }
    
    public function getModule() {
        return $this->_module;
    }
    
    public function getRevision() {
        return $this->_revision;
    }
    
    public function haveToUpload() {
        return $this->_upload_action;
    }
    
    public function haveToDelete() {
        return $this->_delete_action;
    }
    
    public function isDebug() {
        return $this->_debug;
    }
    
    /**
     * 
     * @return Config;
     */
    public function getConfig() {
        return $this->_config;
    }
    
    public function prompt() {
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        return trim($line);
    }
    
    public function confirm() {
        $this->outputnl("cli.confirm.continue", array($this->message('cli.confirm.answer.yes.short'), $this->message('cli.confirm.answer.no.short')));
        return strcasecmp($this->prompt(), $this->message('cli.confirm.answer.yes.short')) === 0;
    }
    
    public function message($message, $args = array()) {
        $m = "";
        if($message[0] == ":") {
            $m = substr($message, 1);
        }
        else {           
            $m = $this->_locale->get($message, $args);
        }
        
        return $m;
    }
    
    public function output($message, $args = array()) {
        echo $this->message($message, $args);
    }
    
    public function outputnl($message, $args = array()) {
        echo $this->message($message, $args) . "\n";
    }
    
    public function error($message, $args = array()) {
        $this->outputnl($message, $args);
        $this->outputnl(":");
        exit();
    }
    
    public function getInfo() {
        return sprintf($this->_info, $this->getVersion());
    }
    
    public function getVersion() {
        return $this->_locale->get("cli.version", Version::toString());
    }
}

