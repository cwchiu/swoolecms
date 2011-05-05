<?php
require WEBPATH.'/apps/controllers/UserBase.php';
class myblog extends UserBase
{
    function write()
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

    function index()
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
}