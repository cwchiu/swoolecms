<?php
require 'config.php';
require LIBPATH.'/system/Filter.php';
Filter::request();
$php->runAjax();

function ajax_comment()
{
    session();
    if(!$_SESSION['isLogin']) return 'nologin';   
    $uid = $_SESSION['user_id'];
    $post['aid'] = (int)$_POST['aid'];
    $post['app'] = $_POST['app'];
    $post['content'] = $_POST['content'];
    $post['uid'] = $uid;
    $post['uname'] = $_SESSION['user']['nickname'];
    if($post['app']==='mblog')
    {
        $m = createModel('MicroBlog');
        $entity = $m->get($post['aid']);
        $entity->reply_count ++;
        $entity->save();
        if($entity->uid!=$uid)
        {
        	Api::sendmail($entity->uid, $uid, "【系统】{$post['uname']}评论了你的微博", $post['content']);
        }
    }
    elseif($post['app']==='blog')
    {
        $m = createModel('UserLogs');
        $entity = $m->get($post['aid']);
        $entity->reply_count ++;
        $entity->save();
        if($entity->uid!=$uid)
        {
        	Api::sendmail($entity->uid, $uid, "【系统】{$post['uname']}评论了你的日志.({$entity['title']})", $post['content']);
        }
    }
    createModel('UserComment')->put($post);
    $return = array('id'=>$_SESSION['user']['id'],
                    'addtime'=>Swoole_tools::howLongAgo(date('Y-m-d H:i:s')),
                    'nickname'=>$_SESSION['user']['nickname']);
    if(empty($_SESSION['user']['avatar'])) $return['avatar'] = Swoole::$config['user']['default_avatar'];
    else $return['avatar'] = $_SESSION['user']['avatar'];
    return $return;
}

function ajax_ask_best()
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

function ajax_ask_vote()
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

function ajax_checklogin()
{
    session();
    if($_SESSION['isLogin']) return $_SESSION['user']['nickname'];
    else return false;
}