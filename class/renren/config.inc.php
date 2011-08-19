<?php
require_once WEBPATH.'/class/renren/RESTClient.class.php';
require_once WEBPATH.'/class/renren/RenRenClient.class.php';
$renren_config = Swoole::$config['oauth']['renren'];
$config	= new stdClass;
$config->APIURL		= $renren_config['Server_url'];
$config->APIKey		= $renren_config['APP_KEY'];
$config->SecretKey	= $renren_config['Secret'];
$config->APIVersion	= '1.0';
$config->decodeFormat	= 'json';
$config->APIMapping		= array(
        		'admin.getAllocation' => '',
        		'connect.getUnconnectedFriendsCount' => '',
        		'friends.areFriends' => 'uids1,uids2',
        		'friends.get' => 'page,count',
        		'friends.getFriends' => 'page,count',
        		'notifications.send' => 'to_ids,notification',
        		'users.getInfo'	=> 'uids,fields');
$oauth = new RenRenClient($config);
$oauth->callback = WEBROOT.'/page/oauth_callback/';