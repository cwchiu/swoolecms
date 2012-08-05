<?php
if($_SERVER['HTTP_HOST']=="www.taopukeji.loc")
{
	require('apps/taopukeji/config.php');
}
else
{
	require('config.php');
}
require_once LIBPATH.'/system/Swoole_tools.php';
require 'admin/func.php';
$php->runMVC('mvc');

function url_process_mvc()
{
	$array = array('controller'=>'page','view'=>'index','segs'=>'');
	if(!empty($_GET["c"])) $array['controller']=$_GET["c"];
	if(!empty($_GET["v"])) $array['view']=$_GET["v"];
	if(!empty($_GET["a"])) $array['app']=$_GET["a"];

	if(!empty($_GET['_q']))
	{
		$request = explode('/',$_GET['_q'],3);
		unset($_GET['_q']);
		$array['controller']=$request[0];
		$array['view']=$request[1];
		if(is_numeric($request[2])) $_GET['id'] = $request[2];
		else
		{
		    Swoole_tools::$url_key_join = '-';
        	Swoole_tools::$url_param_join = '-';
        	Swoole_tools::$url_add_end = '.html';
        	Swoole_tools::$url_prefix = WEBROOT."/{$request[0]}/$request[1]/";
        	Swoole_tools::url_parse_into($request[2],$_GET);
		}
	}
	return $array;
}