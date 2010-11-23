<?php
$ugroups = array(
'root'=>'超级管理员',
'qihua'=>'企划部',
'it'=>'IT部',
'xingzheng'=>'行政部',
'shichang'=>'市场部',
'renshi'=>'人事部',
'qita'=>'客服部',
);

$access = array(
'qihua'=>'qihua',
'it'=>'it',
'xingzheng'=>'xingzheng',
'shichang'=>'shichang',
'renshi'=>'renshi',
'qita'=>'qita');

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