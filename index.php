<?php
require('config.php');
require 'admin/func.php';
$php->runMVC('mvc');

function url_process_mvc()
{
	$array = array('controller'=>'page','view'=>'index','segs'=>'');
	if(!empty($_GET["c"])) $array['controller']=$_GET["c"];
	if(!empty($_GET["v"])) $array['view']=$_GET["v"];
	if(!empty($_GET['q']))
	{
		$request = explode('/',$_GET['q'],3);
		if(count($request)!==3) Error::info('URL Error',"HTTP 404!Page Not Found!<P>Error request:<B>{$_SERVER['REQUEST_URI']}</B>");
		$array['controller']=$request[1];
		$array['view']=$request[2];
	}
	return $array;
}