<?php
$swoole_plugin['name'] = 'fckeditor';
$swoole_plugin['copyright'] = 'Copyright (C) 2003-2007 Frederico Caldeira Knabben';
$swoole_plugin['version'] = '2.6';
$swoole_plugin['license '] = 'LGPL';
$dir = dirname(__FILE__);
require($dir."/fckeditor.php");
function editor($input_name, $input_value,$height="480",$upfile=true)
{
	$prefix = Auth::$session_prefix;
	$editor = new FCKeditor($input_name) ;
	$editor->BasePath   = WEBROOT."/swoole_plugin/fckeditor/"; //指定编辑器路径
	$editor->ToolbarSet = "Default"; //编辑器工具栏有Basic（基本工具）,Default（所有工具）选择
	$editor->Width      = "100%";
	$editor->Height     = $height;
	$editor->Value      = $input_value;
	$editor->Config['AutoDetectLanguage'] = true ;
	$editor->Config['DefaultLanguage']  = 'en';
	$FCKeditor = $editor->CreateHtml();
	$ext = <<<HTML
<script language="javascript">
function upfile_success(filepath)
{
	var fck = FCKeditorAPI.GetInstance("content");
	fck.InsertHtml("<img src='"+ filepath +"' />");
}
</script>
<iframe src="{$editor->BasePath}plus/upload_image.php?prefix={$prefix}" height="40" width="100%" frameborder="0" scrolling="no"></iframe>
HTML;
	if($upfile) $FCKeditor.=$ext;
	return $FCKeditor;

}
function resource($app,$aid='',$catid='')
{
	$resource = <<<HTML
	<script language="javascript">
	function fck_insert(html)
	{
		var fck = FCKeditorAPI.GetInstance("content");
		fck.InsertHtml(html);
	}
	</script>
	<iframe src="/swoole_plugin/fckeditor/plus/upfile.php?app=$app&aid=$aid&catid=$catid" height="100" width="100%" frameborder="0" scrolling="no" id='upload_resource'></iframe>
HTML;
	return $resource;
}
?>