<?php
class FrontPage extends Controller
{
    protected function userinfo($uid)
    {
        $_user = createModel('UserInfo');
        $_cate = createModel('UserLogCat');
        $user = $_user->getInfo($uid);
        if(empty($user)) return false;

        $user['skill_info'] = implode('、',$user['skill']);
        $gets2['select'] = 'name,id,num';
        $gets2['uid'] = $uid;
        $gets2['order'] = 'id';
        $gets2['limit'] = 15;
        $blog_cates = $_cate->gets($gets2);
        $this->swoole->tpl->assign('user',$user);
        $this->swoole->tpl->assign('blog_cates',$blog_cates);
    }
}