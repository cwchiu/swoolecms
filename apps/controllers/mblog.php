<?php
require APPSPATH.'/controllers/FrontPage.php';
class mblog extends FrontPage
{
    function index()
    {
        $model = createModel('MicroBlog');
        $_user = createModel('UserInfo');

        $gets['select'] = $model->table.'.id as id,uid,sex,substring(content,1,170) as content,nickname,avatar,UNIX_TIMESTAMP(addtime) as addtime,reply_count';
        $gets['order'] = $model->table.'.id desc';
        $gets['leftjoin'] = array($_user->table,$_user->table.'.id='.$model->table.'.uid');
        $gets['page'] = empty($_GET['page'])?1:(int)$_GET['page'];
        $gets['pagesize'] =15;
        $pager = '';
        $list = $model->gets($gets,$pager);
        foreach($list as &$m)
        {
            $m['addtime'] = date('n月j日 H:i',strtotime($m['addtime']));
        }
        $this->swoole->tpl->assign('mblogs',$list);

        $pager->span_open = array();
        $pager = array('total'=>$pager->total,'render'=>$pager->render());

        $gets2['select'] = 'uid';
        $gets2['group'] = 'uid';
        $gets2['limit'] = 10;
        $gets2['order'] = 'id desc';
        $uids = $model->gets($gets2);
        foreach($uids as $u) $_uids[] = $u['uid'];

        $gets3['in'] = array('id',implode(',',$_uids));
        $gets3['select'] = 'id as uid,nickname,avatar,php_level';
        $gets3['limit'] = 10;
        $users = $_user->gets($gets3);
        $this->swoole->tpl->assign('users',$users);
        $this->swoole->tpl->assign('pager',$pager);
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
        $mblog['addtime'] = date('n月j日 H:i',strtotime($m['addtime']));
        $this->swoole->tpl->assign('mblog',$mblog);
        $this->swoole->tpl->assign('comments',$comments);
        $this->swoole->tpl->display();
    }
}
