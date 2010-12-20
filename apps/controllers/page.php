<?php
class page extends Controller
{
    function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    function flist()
    {
        //Error::dbd();
        //查询根分类
        $ftype = $this->swoole->model->CmsType->get($_GET['f'],'name')->get();

        //查询相关链接
        $param_rel['limit'] = 10;
        $param_rel['gfid'] = $ftype['id'];
        $rel_news = $this->swoole->model->CmsNews->gets($param_rel);
        $this->swoole->tpl->assign('rel_news',$rel_news);
        $this->swoole->tpl->assign('rel','rel');//标识为有相关链接的页面

        //查询新闻动态分类
        $param['gfid']  = $ftype['id'];
        $param['order'] = 'id asc';
        $type = $this->swoole->model->CmsType->gets($param);

        foreach($type as $key => &$val){
            $val['title'] = $val['typename'];
        }

        $gets['limit'] = 6;
        $gets['select'] = 'id,ftitle,addtime';
        foreach($type as $key => &$val)
        {
            $gets['tid'] = $val['id'];
            $val['list'] = $this->swoole->model->CmsNews->gets($gets);
            $val['stitle'] = $val['typename'];
            $val['tid'] = $val['id'];
        }
        $this->swoole->tpl->assign('ftype',$ftype);
        $this->swoole->tpl->assign('pagelist',$type);
        $this->swoole->tpl->assign('ltitle',$ftype['typename']);
        $this->swoole->tpl->display('page_news_index.html');
    }
    function detail()
    {
        $pagenews = $this->swoole->model->CmsNews->get((int)$_GET['d'])->get();
        //查询根分类
        $ftype = $this->swoole->model->CmsType->get($pagenews['gfid'])->get();
        $this->swoole->tpl->assign('ftype',$ftype);

        //查询相关链接
        $param_rel['limit'] = 10;
        $param_rel['gfid'] = $ftype['id'];
        $rel_news = $this->swoole->model->CmsNews->gets($param_rel);
        $this->swoole->tpl->assign('rel_news',$rel_news);
        $this->swoole->tpl->assign('rel','rel');//标识为有相关链接的页面

        //查询新闻动态分类
        $param['fid']  = $ftype['id'];
        $param['order'] = 'id asc';
        $type = $this->swoole->model->CmsType->gets($param);

        foreach($type as $key => &$val){
            $val['title'] = $val['typename'];
        }
        $this->swoole->tpl->assign('pagelist',$type);

        $this->swoole->tpl->assign('ftitle',$pagenews['ftitle']);
        #######为了兼容标题硬性修改##########
        $newst = $this->swoole->model->CmsType->get($pagenews['fid'])->get();
        $newst['title'] = $newst['typename'];
        $newst['content'] = $pagenews['content'];

        $news['title'] = $typename;
        $news['descript'] = $typename;
        $news['name'] = 'news';
        $this->swoole->tpl->assign('news',$news);
        $this->swoole->tpl->assign('page',$newst);
        #######################
        $this->swoole->tpl->display('page_news_detail.html');
    }

    function index()
    {
        if(empty($_GET['p']) or $_GET['p']=='index')
        {
            $gets['select'] = 'id,title,substring(content,1,1000) as des,addtime';
            $gets['limit'] = 10;
            $gets['fid'] = 9;
            $model = createModel('News');
            $list = $model->gets($gets);
            foreach($list as &$l)
            {
                $l['des'] = mb_substr(strip_tags($l['des']),0,120);
            }
            $this->swoole->tpl->assign('list',$list);
            $this->swoole->tpl->display('index.html');
        }
        else
        {
            $page = $_GET['p'];
            $model = createModel('Cpage');
            $det = $model->get($page,'pagename');
            $this->swoole->tpl->assign('det',$det);
            $this->swoole->tpl->display('index_page.html');
        }
    }
    /**
     * 个人用户登录
     * @return unknown_type
     */
    function login()
    {
        session();
        $auth = new Auth($this->swoole->db,'user_login');
        $refer = isset($_GET['refer'])?$_GET['refer']:WEBROOT.'/person/index/';
        if($auth->isLogin())
        {
            header('location:'.$refer);
        }
        if(isset($_POST['username']) and $_POST['username']!='')
        {
            $_POST['username'] = strtolower(trim($_POST['username']));
            $_POST['password'] = trim($_POST['password']);

            $password = Auth::mkpasswd($_POST['username'],$_POST['password']);
            if($auth->login($_POST['username'],$password,isset($_POST['auto'])?1:0))
            {
                $userinfo = $this->swoole->model->UserInfo->get($_SESSION['user_id'])->get();
                $_SESSION['user'] = $userinfo;
                header('location:'.$refer);
            }
            else
            {
                Swoole_js::js_goto('用户名或密码错误！','/page/person_login/');
                exit;
            }
        }
        else {
            $this->swoole->tpl->display('page_person_login.html');
        }
    }
    function register()
    {
        if($_POST)
        {
            session();
            if(!isset($_POST['authcode']) or strtoupper($_POST['authcode'])!==$_SESSION['authcode'])
            {
                Swoole_js::js_back('验证码错误！');
                exit;
            }
            if($_POST['password']!==$_POST['repassword'])
            {
                Swoole_js::js_back('两次输入的密码不一致！');
                exit;
            }
            if(empty($_POST['nickname']))
            {
                Swoole_js::js_back('名称不能为空！');
                exit;
            }
            if(empty($_POST['realname']))
            {
                Swoole_js::js_back('名称不能为空！');
                exit;
            }
            $userInfo = createModel('UserInfo');
            $login['email'] = trim($_POST['email']);

            if($userInfo->exists($login['email']))
            {
                Swoole_js::js_back('已存在此用户，同一个Email不能注册2次！');
                exit;
            }

            $login['password'] = Auth::mkpasswd($login['email'],$_POST['password']);
            $login['username'] = $login['email'];
            $login['reg_ip'] = Swoole_client::getIp();
            $login['mobile'] = $_POST['mobile'];
            $login['realname'] = $_POST['nickname'];
            $login['realname'] = $_POST['realname'];
            $login['lastlogin'] = date('Y-m-d h:i:s');
            $uid = $userInfo->put($login);
            $_SESSION['isLogin'] = true;
            $_SESSION['user_id'] = $uid;
            $_SESSION['user'] = $login;
            Swoole_js::js_goto('注册成功！','/person/index/');
        }
        else
        {
            $this->swoole->tpl->display();
        }
    }

    /**
     * 忘记密码
     * @return unknown_type
     */
    function forgot()
    {
        if($_POST)
        {
            $gets['realname'] = $_POST['realname'];
            $gets['username'] = $_POST['email'];
            $gets['mobile'] = $_POST['mobile'];
            $gets['select'] = 'id';
            $ul = $this->model->UserInfo->gets($gets);
            if(count($ul)!=0)
            {
                $password = Func::randomkeys(6);
                $this->model->UserInfo->set($ul[0]['id'],array('password'=>Auth::mkpasswd($gets['username'],$password)));
                Func::success('找回成功！','您的新密码是 <span style="color:#fe7e00;">'.$password.'</a>');
            }
        }
        else
        {
            $this->swoole->tpl->display();
        }
    }

    function guestbook()
    {
        if($_POST)
        {
            if(empty($_POST['realname']))
            {
                Swoole_js::js_back('姓名不能为空！');
                exit;
            }
            if(empty($_POST['mobile']))
            {
                Swoole_js::js_back('电话不能为空！');
                exit;
            }
            unset($_POST['x'],$_POST['y']);
            $_POST['product'] = implode(',',$_POST['product']);
            $_POST['source'] = implode(',',$_POST['source']);
            $php->model->Guestbook->put($_POST);
            Swoole_js::js_goto('注册成功！','guestbook.php');
        }

        if(!empty($_GET['id']))
        {
            $gb = $php->model->Guestbook->get($_GET['id'])->get();
            $php->tpl->assign('gb',$gb);
            $php->tpl->display('guestbook_detail.html');
        }
        else
        {
            require 'dict/forms.php';
            $pager = null;
            $gets['page'] = empty($_GET['page'])?1:$_GET['page'];
            $gets['pagesize'] =  12;
            $gets['select'] = "id,username,title,addtime";
            $gets['where'][] = "reply!=''";
            $list = $php->model->Guestbook->gets($gets,$pager);

            $_forms['title'] = Form::radio('title',$forms['title'],null,true,array('empty'=>'请选择称谓'));
            $_forms['age'] = Form::select('age',$forms['age'],null,true,array('empty'=>'请选择年龄阶段'));
            $_forms['ctime'] = Form::select('ctime',$forms['ctime'],null,true,array('empty'=>'请选择方便沟通的时间'));
            $_forms['product'] = Form::checkbox('product',$forms['product'],null,true);
            $_forms['source'] = Form::checkbox('source',$forms['source'],null,true);

            $pager = array('total'=>$pager->total,'render'=>$pager->render());
            $php->tpl->assign('pager',$pager);
            $php->tpl->assign('forms',$_forms);
            $php->tpl->assign("list",$list);
            $php->tpl->display('guestbook.html');
        }
    }
}
