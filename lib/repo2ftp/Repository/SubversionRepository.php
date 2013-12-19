<?php

namespace repo2ftp\Repository;

use repo2ftp\Job;
use repo2ftp\Repository;
use repo2ftp\Repository\RevisionException;

require_once 'repo2ftp/Job.php';
require_once 'repo2ftp/Repository.php';
require_once 'repo2ftp/Repository/RevisionException.php';

class SubversionRepository implements Repository {
    
    private $_base_local;
    private $_base_svn;
    
    public function __construct($base_local, $base_svn) {
        $this->_base_local = $base_local;
        $this->_base_svn = $base_svn . "/";
    }

    /**
     * 
     * @param string $revision revision range in the form of repository type
     * @throws \repo2ftp\Repository\RevisionException
     * @return \repo2ftp\Job
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
    
    /**
     * @param string $revision Revision range to parse and validate
     * @throws \repo2ftp\Repository\RevisionException
     */
    public function parseRevision($revision) {
        $opts = explode(':', $revision);
        
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
            throw new RevisionException();
        }
        
        return $revision;
    }
}
