<?php
class mblog extends Controller
{
    function index()
    {
        $model = createModel('MicroBlog');
        $_user = createModel('UserInfo');

        $gets['select'] = $model->table.'.id as id,uid,sex,content,nickname,avatar,UNIX_TIMESTAMP(addtime) as addtime,reply_count';
        $gets['order'] = $model->table.'.id desc';
        $gets['leftjoin'] = array($_user->table,$_user->table.'.id='.$model->table.'.uid');
        $gets['page'] = empty($_GET['page'])?1:(int)$_GET['page'];
        $gets['pagesize'] =15;
        $pager = '';
        $list = $model->gets($gets,$pager);
        $this->swoole->tpl->assign('list',$list);
        $pager->span_open = array();
        $pager = array('total'=>$pager->total,'render'=>$pager->render());
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
        $user = $_u->getInfo($mblog['uid']);
        $mblog['addtime'] = date('n月j日 H:i',$mblog['addtime']);

        $this->swoole->tpl->assign('mblog',$mblog);
        $this->swoole->tpl->assign('comments',$comments);
        $this->swoole->tpl->assign('user',$user);
        $this->swoole->tpl->display();
    }
}
