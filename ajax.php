<?php
require 'config.php';
require LIBPATH.'/system/Filter.php';
Filter::request();
$php->runAjax();

function comment()
{
    session();
    if(!$_SESSION['isLogin']) return 'nologin';
    if(!isset($_POST['authcode']) or strtoupper($_POST['authcode'])!==$_SESSION['authcode']) return 'noauth';
    $post['aid'] = (int)$_POST['aid'];
    $post['app'] = $_POST['app'];
    $post['content'] = $_POST['content'];
    $post['uid'] = $_SESSION['user_id'];
    $post['uname'] = $_SESSION['user']['nickname'];
    if($_POST['app']=='mblog')
    {
        $_m = createModel('MicroBlog');
        $_m->set($post['aid'],array('reply_count'=>'`reply_count`+1'));
    }
    createModel('UserComment')->put($post);
    return 'ok';
}

function ask_best()
{
    session();
    if(!$_SESSION['isLogin']) return 'nologin';
    $reid = (int)$_POST['reid'];
    $reply = createModel('AskReply')->get($reid);
    $ask = createModel('AskSubject')->get($reply['aid']);

    if($ask->uid!=$_SESSION['user_id']) return 'notowner';
    //已有最佳答案
    if($ask->mstatus == 2) return 'nobest';

    //设置为最佳答案
    $reply->best = 1;
    $reply->save();

    $user = createModel('UserInfo')->get($reply['uid']);
    $user->gold += 20; //采纳为最佳答案+20分
    $user->gold += $ask->gold; //另外加悬赏分数
    $user->save();

    //设置为已有最佳答案
    $ask->mstatus = 2;
    $ask->save();
    return 'ok';
}

function ask_vote()
{
    global $php;
    session();
    if(!$_SESSION['isLogin']) return 'nologin';
    $reid = (int)$_POST['reid'];
    $reply = createModel('AskReply')->get($reid);
    $reply->vote+=1;
    $reply->save();
    $put['uid'] = $_SESSION['user_id'];
    $put['aid'] = $reply['aid'];
    $put['reply_id'] = $reid;
    $php->db->insert($put,'ask_vote');
    return 'ok';
}

function checklogin()
{
    session();
    if($_SESSION['isLogin']) return $_SESSION['user']['nickname'];
    else return false;
}