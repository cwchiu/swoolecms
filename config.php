<?php
define('DEBUG','on');
define("WEBPATH",str_replace("\\","/",dirname(__FILE__)));
@define("WEBROOT",'http://'.$_SERVER['SERVER_NAME']);
//Database Driver，可以选择PdoDB , MySQL, MySQL2(MySQLi) , AdoDb(需要安装adodb插件)
define('DBTYPE','MySQL');
define('DBENGINE','MyISAM');
define("DBMS","mysql");
define("DBHOST","localhost");
define("DBUSER","root");
define("DBPASSWORD","root");
define("DBNAME","swoolecms");
define("DBCHARSET","utf8");
define("DBSETNAME",true);

//OAuth
define( "WeiBo_AKEY" , '1418646107' );
define( "WeiBo_SKEY" , '8b1fed32df42548d71acac00dddb05bb');

define('HTML',WEBPATH.'/html');
define('HTML_URL_BASE','/html');
define('HTML_FILE_EXT','.html');

define("TABLE_PREFIX",'st');
define("SITENAME",'Swoole_PHP开发社区');

//define("TPL_DIR",WEBPATH.'/site/'.SITENAME.'/templates');
//模板目录

//上传文件的位置
define('UPLOAD_DIR','/static/uploads');

//缓存系统
define('FILECACHE_DIR',WEBPATH.'/cache/filecache');
#define('CACHE_URL','memcache://127.0.0.1:11211');
define('CACHE_URL','file://localhost#site_cache');
//define('SESSION_CACHE','memcache://192.168.11.26:11211');
//define('KDB_CACHE','memcache://192.168.11.26:11211');
//define('KDB_ROOT','cms,user');

//Login登录用户配置
define('LOGIN_TABLE','user_login');

require_once WEBPATH.'/libs/lib_config.php';

//所有全局对象都改为动态延迟加载，也就是说你的app代码中没用到数据库或者模板系统，Swoole将不会连接数据库或加载smarty
//如果希望启动加载,请使用Swoole::load()函数
$php->autoload('db','cache','tpl');
//$php->loadConfig();
//动态配置系统
//$php->tpl->assign('_site_','/site/'.SITENAME);
//指定国际编码的方式
mb_internal_encoding('utf-8');
