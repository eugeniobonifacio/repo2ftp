<?php

namespace repo2ftp;

require_once('Locale.php');
require_once('Config.php');

use repo2ftp\Config\MissingOptionException;
use repo2ftp\Config\OptionNotFoundException;

class Cli {  
    
    private $_info = "REPO2FTP %s - Eugenio Bonifacio http://www.eugeniobonifacio.com";
    
    private $_version_major = 2;
    private $_version_minor = 0;
    private $_version_fix   = 0;

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
    
    public function __construct($base_path) {
        
        // EXTRACT CLI OPTIONS
        $opts = 'l:r:c:m:d';
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
        
        // WELCOME MESSAGE
        $welcome_message = $this->getInfo();
        $line = str_pad('', strlen($welcome_message), '*');
        
        $this->output(":");
        $this->output(":" . $line);
        $this->output(":" . $welcome_message);
        $this->output(":" . $line);
//        $this->output(":");
        
        $this->_base_path = $base_path;

        $this->output("cli.module.name", $this->_module);
        
        if(array_key_exists('c', $cli_options)) {
            $this->_project = $cli_options['c'];

            $config_path = realpath($this->_base_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $this->_project . '.ini');

            $this->output("cli.config.file", $config_path);

            try {
                $this->_config = new Config($this->_module, $config_path);
            }
            catch(MissingOptionException $ex) {
                $this->error("cli.config.option.missing", $ex->getOption());
            }

            $this->output("cli.ok");
        }
        else {
            $this->error("cli.config.missing");
        }
        
        if(!array_key_exists('r', $cli_options)) {
            $this->error("cli.repository.revision.missing");            
        }
        
        $this->_revision = $cli_options['r'];
        
//        $this->output("cli.repository.revision", $this->_revision);
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
        $this->output("cli.confirm.continue", array($this->message('cli.confirm.answer.yes.short'), $this->message('cli.confirm.answer.no.short')));
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
        echo $this->message($message, $args) . "\n";
    }
    
    public function error($message, $args = array()) {
        $this->output($message, $args);
        $this->output(":");
        exit();
    }
    
    public function getInfo() {
        return sprintf($this->_info, $this->getVersion());
    }
    
    public function getVersion() {
        $v = $this->_version_major . '.' . $this->_version_minor . '.' . $this->_version_fix;
        return $this->_locale->get("cli.version", $v);
    }
}

