<?php
require WEBPATH.'/apps/controllers/UserBase.php';
class person extends UserBase
{
    function question()
    {
        if($_POST)
        {
            if(empty($_POST['title']) or empty($_POST['content']))
            {
                Swoole_js::js_back('标题和内容不能为空！');
                exit;
            }
            $q['gold'] = (int)$_POST['gold'];
            if($q['gold']>200)
            {
                Swoole_js::js_back('金币不得超过200');
                exit;
            }
            $category = createModel('AskCategory')->get((int)$_POST['category']);
            $user = createModel('UserInfo')->get($this->uid);
            if($q['gold']>$user->gold)
            {
                Swoole_js::js_back('您没有足够的金币');
                exit;
            }
            $q['title'] = $_POST['title'];
            $q['cid'] = $category['id'];
            $q['cname'] = $category['name'];
            $q['gold'] = (int)$_POST['gold'];
            $q['expire'] = time()+1296000;
            $q['uid'] = $this->uid;

            $cont['aid'] = createModel('AskSubject')->put($q);
            $cont['content'] = $_POST['content'];

            createModel('AskContent')->put($cont);
            $user->gold -= $q['gold'];
            $user->save();
            Swoole_js::js_goto('发布成功！','/ask/index/');
        }
        else
        {
            $user = createModel('UserInfo')->get($this->uid)->get();
            $forms = createModel('AskSubject')->getForms();
            $this->swoole->tpl->assign('user',$user);
            $this->swoole->tpl->assign('forms',$forms);
            $this->swoole->tpl->display();
        }
    }
    function myquestion()
    {

    }
    function comment()
    {

    }
    function profile()
    {
        if($_POST)
        {
            if(empty($_POST['nickname']))
            {
                Swoole_js::js_back('昵称不能为空！');
                exit;
            }
            if(empty($_POST['mobile']))
            {
                Swoole_js::js_back('手机号码不能为空！');
                exit;
            }
            import_func("file");
            if(!empty($_FILES['avatar']['name']))
            {
                $set['avatar'] = file_upload('avatar','/static/uploads/avatar');
                Image::thumbnail(WEBPATH.$set['avatar'],WEBPATH.$set['avatar'],120,90);
                $_SESSION['user']['avatar'] = $set['avatar'];
            }
            if(!empty($_FILES['certificate']['name'])) $set['certificate'] = file_upload('certificate','/static/uploads/certificate');

            $set['nickname'] = $_POST['nickname'];
            $set['company'] = $_POST['company'];
            $set['blog'] = $_POST['blog'];
            $set['mobile'] = $_POST['mobile'];
            $set['sex'] = (int)$_POST['sex'];
            $set['education'] = (int)$_POST['education'];
            $set['skill'] = implode(',',$_POST['skill']);
            $set['php_level'] = (int)$_POST['php_level'];

            $u = createModel('UserInfo');
            $u->set($this->uid,$set);
            $_SESSION['user']['realname'] = $set['realname'];
            $_SESSION['user']['mobile'] = $set['mobile'];
            Swoole_js::js_back('修改成功！');
        }
        else
        {
            require WEBPATH.'/dict/forms.php';
            $_u = createModel('UserInfo');
            $u = $_u->get($this->uid)->get();

            $_skill = createModel('UserSkill')->getMap();
            $_forms['sex'] = Form::radio('sex',$forms['sex'],$u['sex']);
            $_forms['education'] = Form::select('education',$forms['education'],$u['education']);
            $_forms['skill'] = Form::checkbox('skill',$_skill,$u['skill']);
            $_forms['level'] = Form::radio('php_level',$forms['level'],$u['php_level']);

            $this->swoole->tpl->assign('user',$u);
            $this->swoole->tpl->assign('forms',$_forms);
            $this->swoole->tpl->display();
            //$this->view->showTrace();
        }
    }
    function index()
    {
        $this->mails();
    }
    function readmail()
    {
        //Error::dbd();
        if(empty($_GET['mid'])) die();
        $id = (int)$_GET['mid'];
        $_m = createModel('UserMail');
        $ms = $_m->get($id);
        if($ms->tid!=$this->uid and $ms->fid!=$this->uid) die('Access deny!');
        else
        {
            if($ms->mstatus==0)
            {
                $ms->mstatus = 1;
                $ms->save();
            }

            $_e = createModel('UserInfo');
            $_e->select = 'id,realname';
            $fuser = $_e->get($ms->fid)->get();
            $this->swoole->tpl->assign('ftype','user');
            $this->swoole->tpl->assign('fuser',$fuser);
            $this->swoole->tpl->assign('mail',$ms->get());
            $this->swoole->tpl->display();
        }
    }

    function delmail()
    {
        if(empty($_GET['mid'])) die();
        $id = (int)$_GET['mid'];
        $_m = createModel('UserMail');
        $ms = $_m->get($id);
        //发信人
        if($ms->fid==$this->uid)
        {
            if($ms->mstatus==5) $ms->delete();
            else
            {
                $ms->mstatus=4;
                $ms->save();
            }
            Swoole_js::js_back('删除成功');
        }
        //收信人
        elseif($ms->tid==$this->uid)
        {
            if($ms->mstatus==4) $ms->delete();
            else
            {
                $ms->mstatus=5;
                $ms->save();
            }
            Swoole_js::js_back('删除成功');
        }
        else exit('Error!');
    }
    function logs()
    {
        $_m = createModel('UserLogs');
        $_l = createModel('UserLogCat');
        if($_POST)
        {
            //如果没得到id，说明提交的是添加操作
            if(empty($_POST['title']))
            {
                Swoole_js::js_back('标题不能为空！');
                exit;
            }
            if(!empty($_POST['id']))
            {
                //如果得到id，说明提交的是修改的操作
                $id = $_POST['id'];
                //debug($_POST['c_id']);
                $_POST['uid'] = $this->uid;
                $_m->set($id,$_POST);
                Swoole_js::js_back('修改成功',-2);
            }
            else
            {
                $_POST['uid'] = $this->uid;
                //debug($_POST['c_id']);
                $_m->put($_POST);
                Swoole_js::js_back('添加成功');
            }
        }
        else
        {
            $this->swoole->plugin->load('fckeditor');
            $cat = $_l->gets(array('uid'=>$this->uid));
            if(!empty($_GET['id']))
            {
                $id = $_GET['id'];
                $det = $_m->get($id)->get();
                foreach($cat as &$c)
                {
                    $date[$c['id']] = $c['name'];
                }
                $form = Form::radio('c_id',$date,$det['c_id']);

                $this->swoole->tpl->assign('det',$det);
                Filter::safe($det['content']);
                $editor = editor("content",$det['content'],480);
            }
            else
            {

                foreach($cat as &$c)
                {
                    $date[$c['id']] = $c['name'];
                }
                $form = Form::radio('c_id',$date);
                $editor = editor("content",'',480);
            }
            $this->swoole->tpl->assign('form',$form);
            $this->swoole->tpl->assign('editor',$editor);
            $this->swoole->tpl->display();
        }
    }
    function logcat()
    {
        $_l= createModel('UserLogCat');
        if(isset($_GET['del']))
        {
            $del = $_l->get((int)$_GET['del']);
            if($del->uid!=$this->uid) die('Access deny');
            $del->delete();
            Swoole_js::js_back('删除成功！');
            exit;
        }
        if(isset($_POST['name']))
        {
            $data['uid'] = $this->uid;
            $data['name'] = $_POST['name'];
            $_l->put($data);
            Swoole_js::js_back('添加成功！');
            exit;
        }
        $gets['uid'] = $this->uid;
        $gets['page'] = empty($_GET['page'])?1:(int)$_GET['page'];
        $gets['pagesize'] =15;
        $pager = '';
        $list = $_l->gets($gets,$pager);
        $this->swoole->tpl->assign('list',$list);
        $pager = array('total'=>$pager->total,'render'=>$pager->render());
        $this->swoole->tpl->assign('pager',$pager);
        $this->swoole->tpl->display();
    }

    function logmanage()
    {
        $_m = createModel('UserLogs');
        if(isset($_GET['del']))
        {
            $_m->del((int)$_GET['del']);
            Swoole_js::js_back('删除成功！');
        }

        $gets['uid'] = $this->uid;
        $gets['page'] = empty($_GET['page'])?1:(int)$_GET['page'];
        $gets['pagesize'] =15;
        $pager = '';
        $list = $_m->gets($gets,$pager);
        $this->swoole->tpl->assign('list',$list);
        $pager = array('total'=>$pager->total,'render'=>$pager->render());
        $this->swoole->tpl->assign('pager',$pager);
        $this->swoole->tpl->display();
    }
    function mails()
    {
        //Error::dbd();
        $_m = createModel('UserMail');
        if(isset($_GET['act']) and $_GET['act']=='send')
        {
            $gets['fid'] = $this->uid;
            $gets['where'][] = 'mstatus!=4';
        }
        else
        {
            $gets['tid'] = $this->uid;
            $gets['where'][] = 'mstatus!=5';
        }
        $gets['pagesize'] = 12;
        $gets['page'] = isset($_GET['page'])?(int)$_GET['page']:1;
        $list = $_m->gets($gets,$pager);
        $pager = array('total'=>$pager->total,'render'=>$pager->render());
        $this->swoole->tpl->assign('pager',$pager);
        $this->swoole->tpl->assign('list',$list);
        $this->swoole->tpl->display();
    }

    function sendmail()
    {
        if($_POST)
        {
            if(empty($_POST['tid']) or empty($_POST['title']) or empty($_POST['content'])) die('错误的请求');
            $post['fid'] = $this->uid;
            $post['title'] = mb_substr($_POST['title'],0,48);
            $post['content'] = mb_substr($_POST['content'],0,300);
            $post['tid'] = $_POST['tid'];
            $_m = createModel('UserMail');
            $_m->put($post);
            Swoole_js::js_goto('发送成功','/person/mails/?act=send');
        }
        else
        {
            if($_GET['to'])
            {
                $u = createModel('UserInfo')->get((int)$_GET['to'])->get();
                $this->swoole->tpl->assign('to',$u);
            }
            $this->swoole->tpl->display();
        }
    }

}