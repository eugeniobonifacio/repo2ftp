<?php

namespace repo2ftp;

require_once 'Job.php';
require_once 'Repository.php';

use repo2ftp\Job;
use repo2ftp\Repository;

class GitRepository implements Repository {
    
    private $_base_local;
    private $_base_git;
    
    public function __construct($base_local, $base_repo) {
        $this->_base_local = $base_local;
        $this->_base_repo = $base_repo;
    }
    
    /**
     * 
     * @param string $revision revision range in the form of `repo log` command `-r` option
     * @return \repo2ftp\Job
     */
    public function extract($revision) {
        $base_local = $this->_base_local;
        $base_repo = $this->_base_repo;
        
        $repo_command = "git --git-dir={$base_local}/.git --work-tree={$base_local} log --name-status $revision";
        
        $output = shell_exec($repo_command);

        $lines = explode("\n", $output);

        $job = new Job();
        for($i = 3; $i < count($lines); $i++) {
            if(preg_match('/^([A-Z]{1})       (\/[^\(\)]*)(?: \(.*\))?$/', $lines[$i], $matched)) {                
                if($matched[1] == 'A' || $matched[1] == 'M') {

                    $file = $matched[2];
                    if(strpos($file, $base_repo) === 0) {

                        $file_relative_path = substr($file, strlen($base_repo));
                        $job->addToUpload($file_relative_path);
                    }
                }
                elseif($matched[1] == 'D') {
                    $file = $matched[2];
                    if(strpos($file, $base_repo) === 0) {

                        $file_relative_path = substr($file, strlen($base_repo));
                        $job->addToDelete($file_relative_path);
                    }
                }
            }
        }

        return $job;
    }
}
