<?php

namespace svn2ftp;

require_once 'Job.php';

use svn2ftp\Job;

class SubversionRepository {
    
    private $_base_local;
    private $_base_svn;
    
    public function __construct($base_local, $base_svn) {
        $this->_base_local = $base_local;
        $this->_base_svn = $base_svn;
    }
    
    /**
     * 
     * @param string $revision revision range in the form of `svn log` command `-r` option
     * @return \svn2ftp\Job
     */
    public function extract($revision) {
        $base_local = $this->_base_local;
        $base_svn = $this->_base_svn;
        
        $svn_command = "svn log -r $revision -v " . $base_local;
        
        $output = shell_exec($svn_command);

        $lines = explode("\n", $output);

        $job = new Job();
        for($i = 3; $i < count($lines); $i++) {
            if(preg_match('/^   ([A-Z]{1}) (\/[^\(\)]*)(?: \(.*\))?$/', $lines[$i], $matched)) {                
                if($matched[1] == 'A' || $matched[1] == 'M') {

                    $file = $matched[2];
                    if(strpos($file, $base_svn) === 0) {

                        $file_relative_path = substr($file, strlen($base_svn));
                        $job->addToUpload($file_relative_path);
                    }
                }
                elseif($matched[1] == 'D') {
                    $file = $matched[2];
                    if(strpos($file, $base_svn) === 0) {

                        $file_relative_path = substr($file, strlen($base_svn));
                        $job->addToDelete($file_relative_path);
                    }
                }
            }
        }

        return $job;
    }
}
