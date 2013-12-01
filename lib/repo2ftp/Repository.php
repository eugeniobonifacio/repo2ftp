<?php

namespace repo2ftp;


interface Repository { 
    
    /**
     * @param string $revision Revision range to parse and validate
     * @throws \repo2ftp\Repository\RevisionException
     */
    public function parseRevision($revision);
    
    /**
     * 
     * @param string $revision revision range in the form of repository type
     * @throws \repo2ftp\Repository\RevisionException
     * @return \repo2ftp\Job
     */
    public function extract($revision);
}
