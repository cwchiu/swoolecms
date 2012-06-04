<?php
require '../../config.php';
import('#net.driver.SelectTCP');
//import('#net.protocol.ChatSever');
import('#net.protocol.FlashPolicy');
require WEBPATH.'/test/websocket.php';

if($argv[1]=='flash')
{
    echo "Flash Policy Server",NL;
    $protocol = new FlashPolicy;
    $server = new SelectTCP('localhost',$protocol->default_port);
    $server->setProtocol($protocol);
    $server->run();
}
elseif($argv[1]=='chat')
{
    $protocol = new WebSocket;
    $server = new SelectTCP('localhost',8080);
    $server->setProtocol($protocol);
    $server->run();
}