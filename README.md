# repo2ftp

A PHP CLI tool for uploading version changesets to an FTP remote server. This is quite helpful when you make changesets to a project under version control and want to upload only changed files by revision number or range.

You can manage several projects with the same tool.


### Usage: 

repo2ftp -c project_config_name [-m module] -r rev_range

- all files marked as (M)odified or (A)dded will be uploaded
- all files marked as (D)eleted will be deleted from the remote side
- 'rev_range' is in the same form used by the VCS
- if 'module' option is not provided, 'base' is assumed

### Multi-project setup:

Create a folder named "config" at the same level of repo2ftp. Inside create an ".ini" file with the name of your project, don't use spaces! Inside the ini file put these options:

> ftp.host = "ftp.server.net"  
> ftp.username = "username"  
> ftp.password = "password"  
>  
> module.base.type = "svn" ; or "git"
> module.base.path.local = "/the/absolute/path/of/the/project"  
> module.base.path.repository = "/trunk"  
> module.base.path.ftp = "/httpdocs/project"  
> module.base.path.exclude[] = "/^example\//"  
> module.base.path.exclude[] = "/^tmp\//"  

#### Example:

you have a project named "Hello Word" with the following:

##### 1. the project folder is under SVN version control

the absolute path of the project is "/home/user/work/hello_world" and contains the following

> .  
> ..  
> .svn  
> css/  
> images/  
> uploads/  
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
> module.base.type = "svn"
> module.base.path.local = "/home/user/work/hello_world"  
> module.base.path.repository = "/trunk"  
> module.base.path.ftp = "/httpdocs"  
> module.base.path.exclude[] = "/^uploads\//"  
