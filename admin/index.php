<?php
require '../config.php';
require 'check.php';
require 'func.php';
$apps = getApps();
$php->tpl->assign("apps",$apps);
if(isset($_GET['debug']))
{
	Error::sessd();
}
if(isset($_GET['page']))
{
	$php->tpl->display("admin_index_{$_GET['page']}.html");
}
else $php->tpl->display('admin_index.html');
?>