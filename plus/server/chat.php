<?php
require '../../config.php';
import('#net.driver.SelectTCP');
import('#net.protocol.ChatServer');
import('#net.protocol.FlashPolicy');

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
    $protocol = new ChatServer;
    $server = new SelectTCP('localhost',$protocol->default_port);
    $server->setProtocol($protocol);
    $server->run();
}
else
{

}