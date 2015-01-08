<?php

namespace repo2ftp;

class Version {

    protected static $version_major = 2;
    protected static $version_minor = 3;
    protected static $version_fix = 0;
    
    public static function toString() {
        
        $v = self::$version_major . '.' . self::$version_minor . '.' . self::$version_fix;
        
        return $v;
    }
}
