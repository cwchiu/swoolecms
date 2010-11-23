<?php
class page extends Controller
{
    private $model;

    function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    function sitepage()
    {
        $pagename = substr($_GET['p'],0,16);
        $page = $this->model->SiteChannel->getPage($pagename);
        $this->swoole->tpl->assign('page',$page);
        $news = $this->swoole->model->SiteChannel->get($page['fid'])->get();
        $pagelist = $this->model->SiteChannel->getnews($news['id']);
        $this->swoole->tpl->assign('pagelist',$pagelist);
        $this->swoole->tpl->assign('news',$news);
        $this->swoole->tpl->display('page_channel.html');
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
        if(isset($_GET['p'])) $this->cms_page();
        elseif(isset($_GET['f']) and $_GET['cid']=='index') $this->cms_list();
        elseif(isset($_GET['f'])) $this->cms_child();
        elseif(isset($_GET['d'])) $this->cms_detail();
        else $this->cms_index();
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
            header('Cache-Control: no-cache, must-revalidate');
            $code = $_POST['code'];             //从POST获取邀请码
            $prid = $_POST['prid'];
            $param['invitecode'] = $code;
            $param['prid'] = $prid;
            $codes = $this->swoole->model->AdminInvite->gets($param);
            if(empty($codes))
            {                  //检查获取的邀请码是否有效
                Swoole_js::js_goto('您没有接受到邀请！','/');
                exit;
            }
            if($_POST['password']!==$_POST['repassword'])
            {
                Swoole_js::js_back('两次输入的密码不一致！');
                exit;
            }
            if(empty($_POST['realname']))
            {
                Swoole_js::js_back('真实姓名不能为空！');
                exit;
            }
            if($this->model->UserInfo->exists($_POST['email']))
            {
                Swoole_js::js_back('已存在此用户，同一个用户不能注册2次！');
                exit;
            }

            $login['username'] = strtolower(trim($_POST['email']));
            $login['password'] = Auth::mkpasswd($login['username'],$_POST['password']);
            $login['realname'] = trim($_POST['realname']);
            $login['prid'] = $prid;
            $login['sex'] = $_POST['sex'];
            $login['reg_ip'] = Swoole_client::getIp();
            $login['mobile'] = $_POST['mobile'];
            $this->model->UserInfo->put($login);
            //修改该应聘者状态
            $p_resume['test_status'] = 2;
            $this->swoole->model->PersonResume->set($prid,$p_resume,'id');
            session();
            $auth = new Auth($this->swoole->db,'user_login');
            $auth->login($login['username'],$login['password'],0);
            $_SESSION['user']['realname'] = $login['realname'];

            $params['issend'] = 3;
            $this->swoole->model->AdminInvite->set($codes[0]['id'],$params,'id');
            Swoole_js::location('/person/start_test/');
        }
        else
        {
            session_cache_limiter('private');
            $code = isset($_GET['code'])?$_GET['code']:'';             //从url获取邀请码
            $prid = isset($_GET['prid'])?$_GET['prid']:0;
            if(!empty($code)){                  //检查获取的邀请码是否有效
                $param['invitecode'] = $code;
                $param['prid'] = $prid;
                $codes = $this->swoole->model->AdminInvite->gets($param);  //获取数据库已发送未注册的邀请码
                if(empty($codes)){
                    Swoole_js::js_goto('不合法的邀请码！','/');
                    exit;
                }
                if($codes[0]['issend'] == 3){
                    Swoole_js::js_goto('该邀请码已经失效！','/');
                    exit;
                }
                $userinfo = $this->swoole->model->PersonResume->get($prid)->get();
                $this->swoole->tpl->assign('userinfo',$userinfo);
                $this->swoole->tpl->assign('code',$code);
                $this->swoole->tpl->assign('prid',$prid);
                $this->swoole->tpl->display();
            }else{
                Swoole_js::js_goto('您没有接受到邀请！','/');
                exit;
            }
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
