<?php
require 'config.php';

$php->runAjax();

function comment()
{
    session();
    if(!$_SESSION['isLogin']) return 'nologin';
    if(!isset($_POST['authcode']) or strtoupper($_POST['authcode'])!==$_SESSION['authcode']) return 'noauth';
    $post['aid'] = $_POST['aid'];
    $post['app'] = $_POST['app'];
    $post['content'] = $_POST['content'];
    $post['uid'] = $_SESSION['user_id'];
    $post['uname'] = $_SESSION['user']['nickname'];
    createModel('UserComment')->put($post);
    return 'ok';
}

function checklogin()
{
    session();
    if($_SESSION['isLogin']) return $_SESSION['user']['nickname'];
    else return false;
}