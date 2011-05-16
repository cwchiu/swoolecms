<?php
require APPSPATH.'/controllers/FrontPage.php';
class blog extends FrontPage
{
    function index()
    {

    }

    function detail()
    {
        $id = (int)$_GET['id'];
        $_blog = createModel('UserLogs');
        $blog = $_blog->get($id);
        if(empty($_COOKIE['look']) and $_COOKIE['look']!=$id)
        {
            $blog->look_count++;
            $blog->save();
            setcookie('look',$id,time()+3600);
        }

        $uid = $blog['uid'];
        $this->userinfo($uid);
        $_c = createModel('UserComment');
        $comments = $_c->getByAid('blog',$id);
        $this->swoole->tpl->assign('comments',$comments);
        $this->swoole->tpl->assign('blog',$blog->get());
        return $this->swoole->tpl->fetch('blog_detail.html');
    }

    function category()
    {
        if(empty($_GET['cid'])) error(409);
        $cid = (int)$_GET['cid'];
        $_blog = createModel('UserLogs');
        $_cate = createModel('UserLogCat');

        $cate = $_cate->get($cid)->get();
        $uid = $cate['uid'];
        $this->userinfo($uid);

        $gets1['uid'] = $uid;
        $gets1['c_id'] = $cid;
        $gets1['select'] = 'title,id,substring(content,1,1000) as des,addtime,reply_count,look_count';
        $gets1['page'] = empty($_GET['page'])?1:(int)$_GET['page'];
        $gets1['pagesize'] = 10;
        $blogs = $_blog->gets($gets1,$pager);
        foreach($blogs as &$m)
        {
            $m['addtime'] = date('n月j日 H:i',strtotime($m['addtime']));
            $m['des'] = mb_substr(strip_tags($m['des']),0,120);
        }
        $this->swoole->tpl->assign('cate',$cate);
        $this->swoole->tpl->assign('blogs',$blogs);
        $pager->span_open = array();
        $pager = array('total'=>$pager->total,'render'=>$pager->render());
        $this->swoole->tpl->assign('pager',$pager);
        return $this->swoole->tpl->fetch('blog_category.html');
    }
}