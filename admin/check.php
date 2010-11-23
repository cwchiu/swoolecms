<?php
$php->tpl->template_dir = WEBPATH.'/admin/templates';
session();
Auth::$session_prefix = 'admin_';
Auth::$login_url = '/admin/login.php?';
Auth::login_require();

$access = array();
require "../dict/acl.php";
?>