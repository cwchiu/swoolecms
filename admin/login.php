<?php
require '../config.php';
$php->tpl->template_dir = WEBPATH.'/admin/templates';
session();
//$php->db->debug = true;
$table = 'st_admin';
Auth::$session_prefix = 'admin_';
Auth::$login_url = '/admin/login.php?';
$auth = new Auth($php->db,$table);
$refer = isset($_GET['refer'])?$_GET['refer']:WEBROOT.'/admin/index.php';
if($auth->isLogin()) header('location:'.$refer);

if(isset($_POST['username']) and $_POST['username']!='')
{
	$password = Auth::mkpasswd($_POST['username'],$_POST['password']);
	if($auth->login($_POST['username'],$password,isset($_POST['auto'])?1:0))
	{
		$admin_id =  $_SESSION['admin_user_id'];
		$_SESSION['admin_user'] = $php->db->query("select * from $table where id=$admin_id")->fetch();
		header('location:'.$refer);
	}
	else
	{
		Swoole_js::js_back('用户名或密码错误！');
		exit;
	}
}
else
{
	$php->tpl->display('admin_login.html');
}
if(isset($_GET['logout'])) $auth->logout();
?>