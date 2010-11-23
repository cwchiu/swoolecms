<?php
require('../../../config.php');
session();
Auth::$session_prefix = $_GET['prefix'];
Auth::login_require();

//$php->db->debug = true;
import_func('file');
import_func('content');
import_func('js');

$table = TABLE_PREFIX.'_'.$_GET['app'];
if(empty($_GET['aid']))
{
	$res = $php->db->query("show table status from ".DBNAME." where name='$table'")->fetch();
	$id = $res['Auto_increment'];
}
else
{
	$id = $_GET['aid'];
}

if(isset($_GET['del']))
{
	$php->db->query('delete from chq_resource where id='.$_GET['del']);
}
if(isset($_FILES['media']))
{
 	if(empty($_POST['title'])) $_POST['title']= $_FILES['media']['name'];
	$_POST['url'] = file_upload('media');
	$_POST['filetype'] = file_gettype($_FILES['media']['type']);
	$_POST['filesize'] = $_FILES['media']['size'];
	if(!empty($_GET['catid'])) $_POST['catid'] = $_GET['catid'];
	$php->db->insert($_POST,'chq_resource');
}
$list = $php->db->query('select * from chq_resource where aid='.$id)->fetchall();
$php->tpl->assign('list',$list);
$php->tpl->assign('aid',$id);
$php->tpl->display(ADMIN_SKIN.'/admin_upfile.html');
?>