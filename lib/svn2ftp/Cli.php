<?php

namespace svn2ftp;

require_once('Locale.php');
require_once('Config.php');

use svn2ftp\Config\MissingOptionException;
use svn2ftp\Config\OptionNotFoundException;

class Cli {  
    
    private $_info = "SVN2FTP %s - Eugenio Bonifacio http://www.eugeniobonifacio.com";
    
    private $_version_major = 1;
    private $_version_minor = 0;
    private $_version_fix   = 0;

    /**
     *
     * @var Locale
     */
    protected $_locale;
    protected $_config;
    protected $_project;
    protected $_revision;
    
    protected $_base_path;
    
    public function __construct($base_path) {
        
        // EXTRACT CLI OPTIONS
        $opts = 'l:r:c:';
        $cli_options = getopt($opts);
        
        // LANGUAGE
        if(array_key_exists('l', $cli_options)) {
            $this->_locale = new Locale($cli_options['l']);
        }
        else {
            $this->_locale = new Locale();
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

        if(array_key_exists('c', $cli_options)) {
            $this->_project = $cli_options['c'];

            $config_path = realpath($this->_base_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $this->_project . '.ini');

            $this->output("cli.config.file", $config_path);

            try {
                $this->_config = new Config($config_path);
            }
            catch(MissingOptionException $ex) {
                $this->error("cli.config.option.missing", $ex->getOption());
            }
            
            $this->output("cli.ok");
        }
        else {
            $this->error("cli.config.missing");
        }
        
        $revision = '';
        $opts = array();
        if(array_key_exists('r', $cli_options)) {
            $opts = explode(':', $cli_options['r']);
        }

        $rev_error = false;
        
        $rev_start = strtoupper($opts[0]) == 'HEAD' ? 'HEAD' : (int)$opts[0];
        
        if($rev_start == 'HEAD') {
            $revision = $rev_start;
        }
        elseif(is_numeric($rev_start)) {
            $rev_end = strtoupper($opts[1]) == 'HEAD' ? 'HEAD' : (int)$opts[1];
            
            if($rev_end == 'HEAD' || (is_numeric($rev_end) && $rev_end > $rev_start)) {
                $revision = $rev_start . ':' . $rev_end;
            }
            else {
                $rev_error = true;
            }
        }
        else {
            $rev_error = true;
        }
        
        if($rev_error) {
            $this->error("cli.subversion.revision.invalid");
        }
        
        $this->_revision = $revision;
        
        $this->output("cli.subversion.revision", $revision);
    }
    
    public function getProject() {
        return $this->_project;
    }
    
    public function getRevision() {
        return $this->_revision;
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

