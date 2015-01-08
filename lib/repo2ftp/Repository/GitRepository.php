<?php

namespace repo2ftp\Repository;

use repo2ftp\Job;
use repo2ftp\Repository;

require_once 'repo2ftp/Job.php';
require_once 'repo2ftp/Repository.php';


class GitRepository implements Repository {
    
    private $_base_local;
    private $_base_repo;
    
    public function __construct($base_local, $base_repo) {
        $this->_base_local = $base_local;
        $this->_base_repo = $base_repo;
    }
    
    /**
     * 
     * @param string $revision revision range in the form of `repo log` command `-r` option
     * @return \repo2ftp\Job
     */
    public function extract($revision, $exclude = array()) {
        $base_local = $this->_base_local;
        $base_repo = $this->_base_repo;
        
        $repo_command = "git --git-dir={$base_local}/.git --work-tree={$base_local} log $revision --name-status --reverse";
        
        $output = shell_exec($repo_command);

        $lines = explode("\n", $output);

        $job = new Job();
        $subtree = "";
        for($i = 0; $i < count($lines); $i++) {
            
            if(preg_match('/^commit ([a-z0-9]{40})$/', $lines[$i], $matched)) {
                $subtree = "";
            }
        
            if(preg_match('/^(?:\s+)git-subtree-dir: (.*)$/', $lines[$i], $matched)) {
                $subtree = $matched[1] . DIRECTORY_SEPARATOR;
            }
            
            if(preg_match('/^([AMD]{1})(?:\s+)([^\(\)]*)(?: \(.*\))?$/', $lines[$i], $matched)) {

                $file = $matched[2];

                $file_relative_path = $subtree;
                if(empty($base_repo)) {
                    $file_relative_path .= $file;
                }
                elseif(strpos($file, $base_repo) !== 0) {
                    $file_relative_path .= substr($file, strlen($base_repo));
                }
                else {
                    continue;
                }                
                
                $skip = false;
                foreach($exclude as $excluded) {
                    if(preg_match($excluded, $file_relative_path)) {
                        $skip = true;
                        break;
                    }
                }
                
                if($skip) {
                    continue;
                }
                
                if($matched[1] == 'A' || $matched[1] == 'M') {
                    $job->addToUpload($file_relative_path);
                }
                elseif($matched[1] == 'D') {
                    $job->addToDelete($file_relative_path);
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
        return $revision;
    }

    public function get($file, $revision = 'HEAD') {
        $base_local = $this->_base_local;
        $base_repo = $this->_base_repo;
        
        if($revision !== null) {
            $file = $revision . ':' . $file;
        }
        
        $repo_command = "git --git-dir={$base_local}/.git --work-tree={$base_local} show $file";
        $output = shell_exec($repo_command);
        
        return $output;
    }
}
