1、checkout 代码<br />
2、下载最新版swoole框架，将libs目录复制到目录中<br />
3、修改数据库用户名密码等配置<br />
4、下载数据库结构和基础数据，swoolecms.sql和data.sql（从download页面下载），并导入MySQL，建立所有的数据表<br />
5、配置Apache虚拟主机和URLRewrite<br />
首先配置本地hosts文件，增加一个本机域名，解析到127.0.0.1<br />
需要注意，Apache需要启用urlrewrite模块，配置示例
http://code.google.com/p/swoolecms/wiki/VirtualHostSettings<br />
6、在浏览器中运行<br />
http://yourhost/