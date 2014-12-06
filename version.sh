#!/bin/bash

rev=`git rev-parse --abbrev-ref HEAD`
#rev="master"

versions=($(echo $rev | grep -o "[0-9]\+"))
length=${#versions[@]}

echo "Revision: $rev"

if [ ${length} -ne 3 ]
then
    versions=(0 0 0)
fi

sed -e "s/\(\$version_major = \)\(.*\)\(;\)/\1${versions[0]}\3/" \
    -e "s/\(\$version_minor = \)\(.*\)\(;\)/\1${versions[1]}\3/" \
    -e "s/\(\$version_fix = \)\(.*\)\(;\)/\1${versions[2]}\3/" lib/repo2ftp/Version.php > Version.txt

mv Version.txt lib/repo2ftp/Version.php

exit 0
