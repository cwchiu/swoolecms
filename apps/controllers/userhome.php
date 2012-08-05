<?php
class userhome extends FrontPage
{
    function photo()
    {
        if(!isset($_GET['uid'])) return Error('错误的请求');
        $uid = (int)$_GET['uid'];

        $this->userinfo($uid);

        if(!empty($_GET['id']))
        {
            $pid = (int)$_GET['id'];
            Widget::photoDetail($pid,$uid);
            $this->swoole->tpl->display('userhome_photo_detail.html');
        }
        else
        {
            $gets['uid'] = $uid;
            $gets['select'] = 'id,imagep';
            $gets['page'] = empty($_GET['page'])?1:(int)$_GET['page'];
            $gets['pagesize'] =15;

            $photo = $this->swoole->model->UserPhoto->gets($gets,$pager);
            $this->swoole->tpl->assign('photo',$photo);
            $this->swoole->tpl->assign('count',$pager->total);
            $this->swoole->tpl->assign('pager',$pager->render());
            $this->swoole->tpl->display();
        }
    }

    function link()
    {

    }

}