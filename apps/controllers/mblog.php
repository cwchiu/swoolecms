<?php
class mblog extends FrontPage
{
    function index()
    {
        //微博客列表
        $this->getMblogs();
        $gets3['select'] = 'uid,nickname,avatar,php_level,addtime';
        $gets3['order'] = 'user_microblog.addtime desc';
        $gets3['leftjoin'] = array('user_login','user_login.id=uid');
        $gets3['limit'] = 10;
        $gets3['group'] = 'uid';
        $gets3['where'] = 'user_login.id = uid';
        $users = createModel('MicroBlog')->gets($gets3);
        $this->swoole->tpl->assign('users',$users);
        $this->swoole->tpl->display();
    }

    function detail()
    {
        if(!isset($_GET['id'])) exit;
        $id = (int)$_GET['id'];

        $model = createModel('MicroBlog');
        $_u = createModel('UserInfo');
        $_c = createModel('UserComment');

        $comments = $_c->getByAid('mblog',$id);
        $mblog = $model->get($id)->get();
        $this->userinfo($mblog['uid']);
        $mblog['addtime'] = date('n月j日 H:i',strtotime($mblog['addtime']));
        $this->swoole->tpl->assign('mblog',$mblog);
        $title = strip_tags(Func::mblog_link(0,$mblog['content'],32,true));
        $this->swoole->tpl->assign('title',$title);
        $this->swoole->tpl->assign('comments',$comments);
        $this->swoole->tpl->display();
    }
}
