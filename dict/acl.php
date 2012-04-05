<?php
$ugroups = array(
'root'=>'超级管理员',
'user'=>'普通用户'
);

$access = array(
'news'=>'user'
);

function checkAB($op)
{
	global $access;
	$group = $_SESSION['admin_user']['ugroup'];
	if($group=='root') return true;
	$acl = explode(',',$access[$op]);
	return in_array($group,$acl);
}

function checkAG($op)
{
    global $access;
    $group = $_SESSION['admin_user']['ugroup'];
    if($group=='root') return true;
    $acl = explode(',',$access[$op]);
    return in_array($group,$acl);
}