<?php

namespace repo2ftp;


interface Repository {   
    /**
     * 
     * @param string $revision revision range in the form of repository type
     * @return \repo2ftp\Job
     */
    public function extract($revision);
}
