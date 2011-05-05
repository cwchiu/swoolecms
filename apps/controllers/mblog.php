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
        $mblog = $model->get($id);

        $this->swoole->tpl->display();
    }

    function post()
    {
        if(!empty($_POST['microblog']))
        {
            $model = createModel('MicroBlog');
            $in['content'] = trim($_POST['microblog']);
            $in['uid'] = $this->uid;
            $model->put($in);
            Swoole_js::js_goto('发布成功','/mblog/index/');
        }
    }
}
