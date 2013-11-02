# svn2ftp

A PHP CLI tool for uploading subversion changesets to an FTP remote server. This is quite helpful when you make changesets to a project under SVN and want to upload only changed files by revision number or range.

You can manage several projects with the same tool.


### Usage: 

svn2ftp -c project_config_name -r rev_start:rev_end

- all files marked as (M)odified or (A)dded will be uploaded
- all files marked as (D)eleted will be deleted from the remote side


### Multi-project setup:

Create a folder named "config" at the same level of svn2ftp. Inside create an ".ini" file with the name of your project, don't use spaces! Inside the ini file put these options:

> ftp.host = "ftp.server.net"
> ftp.username = "username"
> ftp.password = "password"
>
> path.base.local = "/the/absolute/path/of/the/project"
> path.base.svn = "/trunk"
> path.base.ftp = "/httpdocs/project"

#### Example:

you have a project named "Hello Word" with the following:

##### 1. the project folder is under SVN version control

the absolute path of the project is "/home/user/work/hello_world" and contains the following

> .
> ..
> .svn
> css/
> images/
> index.php


##### 2. the project is hosted in svn://my_subversion_server.com/projects/hello_world/trunk/

##### 3. the FTP server is ftp.myftpserver.com

> httpdocs/ <- your project goes here
> httpsdocs/
> logs/


your ini file should look like this:

> ftp.host = "ftp.myftpserver.com"
> ftp.username = "your_username"
> ftp.password = "your_password"
> 
> path.base.local = "/home/user/work/hello_world"
> path.base.svn = "/trunk"
> path.base.ftp = "/httpdocs"
