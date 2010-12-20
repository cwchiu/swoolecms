<?php
class person extends Controller
{
	public $uid;

	function __construct($swoole)
	{
	    //Error::dbd();
		parent::__construct($swoole);
		session();
		Auth::$login_url = '/page/login/?';
		Auth::login_require();
		$this->uid = $_SESSION['user_id'];
	}
	function profile()
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

			$set['realname'] = $_POST['realname'];
			$set['mobile'] = $_POST['mobile'];
			$set['education'] = $_POST['education'];

			$u = createModel('UserInfo');
			$u->set($this->uid,$set);

			$_SESSION['user']['realname'] = $set['realname'];
			$_SESSION['user']['mobile'] = $set['mobile'];
			Swoole_js::js_back('修改成功！');
		}
		else
		{
			$_u = createModel('UserInfo');
			$u = $_u->get($this->uid)->get();
			$this->swoole->tpl->assign('user',$u);
			$this->swoole->tpl->display();
		}
	}
	function bidding()
	{
		//Error::dbd();
		$_area = createModel('MArea');
		if($_POST)
		{
			if(empty($_POST['school']))
			{
				Swoole_js::js_back('期望学校不能为空！');
				exit;
			}
			if(empty($_POST['major']))
			{
				Swoole_js::js_back('期望专业不能为空！');
				exit;
			}
			$_m = createModel('UserBidding');
			$carea = $_area->get($_POST['area_id']);
			$_POST['area'] = $carea['name'];

			$_POST['country_id'] = $carea['fid'];
			$farea = $_area->get($carea['fid']);
			$_POST['country'] = $farea['name'];
			$_POST['sid'] = date('Y-m').'-'.rand(100001,999999);
			$_POST['uid'] = $this->uid;
			$_POST['uname'] = $_SESSION['user']['realname'];
			$_m->put($_POST);
			Swoole_js::js_goto('修改成功！','/person/index/');
		}
		else
		{
			//$u = createModel('UserInfo');
			require WEBPATH.'/dict/bid.php';
			$_area->select = 'id,name,fid';
			$area = $_area->all()->fetchall();
			$new = array();
			foreach($area as $m)
			{
				if($m['fid']!=0) $new[$m['fid']]['child'][] = $m;
				else
				{
					$new[$m['id']]['id'] = $m['id'];
					$new[$m['id']]['name'] = $m['name'];
				}
			}
			$this->swoole->tpl->assign('budget',Form::select('budget',$bid['budget'],null,null,array('empty'=>'请填写期望费用')));
			$this->swoole->tpl->assign('area',$new);
			$u = $_SESSION['user'];
			$this->swoole->tpl->assign('user',$u);
			$this->swoole->tpl->display();
		}
	}
	function mybid()
	{
		$_m = createModel('UserBidding');
		$gets['uid'] = $this->uid;
		$gets['stat'] = isset($_GET['stat'])?(int)$_GET['stat']:0;
		$gets['pagesize'] = 12;
		$gets['page'] = isset($_GET['page'])?(int)$_GET['page']:1;
		$list = $_m->gets($gets,$pager);
		$pager = array('total'=>$pager->total,'render'=>$pager->render());
		$this->swoole->tpl->assign('pager',$pager);
		$this->swoole->tpl->assign('list',$list);
		$this->swoole->tpl->display();
	}
	function bid_list()
	{
		//Error::dbd();
		if(empty($_GET['id'])) die();
		$id = (int)$_GET['id'];

		$_ent = createModel('EntBid');
		$_em = createModel('EntInfo');
		$_pm = createModel('UserBidding');

		$gets['leftjoin'] = array($_em->table,$_ent->table.'.eid='.$_em->table.'.id');
		$gets['select'] = 'eid,logo,enterprisename,enter_intro';
		$gets['bid'] = $id;
		$gets['order'] = $_em->table.'.id desc';
		$gets['pagesize'] = 12;
		$gets['page'] = isset($_GET['page'])?(int)$_GET['page']:1;
		$list = $_ent->gets($gets,$pager);
		$pager = array('total'=>$pager->total,'render'=>$pager->render());
		$this->swoole->tpl->assign('pager',$pager);
		$this->swoole->tpl->assign('list',$list);
		$this->swoole->tpl->display();
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
			if($ms->mtype==1 or $ms->mtype==3)
			{
				$_e = createModel('EntInfo');
				$_e->select = 'id,enterprisename';
				$fuser = $_e->get($ms->fid)->get();
				$this->swoole->tpl->assign('ftype','ent');
				$this->swoole->tpl->assign('fuser',$fuser);
			}
			else
			{
				$_e = createModel('UserInfo');
				$_e->select = 'id,realname';
				$fuser = $_e->get($ms->fid)->get();
				$this->swoole->tpl->assign('ftype','user');
				$this->swoole->tpl->assign('fuser',$fuser);
			}
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
		if($ms->tid==$this->uid)
		{
			$ms->delete();
			Swoole_js::js_goto('删除成功','/person/index/');
		}
		elseif($ms->fid==$this->uid)
		{
			$ms->mstatus = 4;
			$ms->save();
			Swoole_js::js_goto('删除成功','/person/index/');
		}
		else
		{
			die('Access deny!');
		}
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
		if($_GET['act']=='send')
		{
			$gets['fid'] = $this->uid;
			$gets['where'][] = 'mtype in(2,4)';

		}
		else
		{
			$gets['tid'] = $this->uid;
			$gets['where'][] = 'mtype in(1,4)';
		}

		$gets['where'][] = 'mstatus!=4';
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
			if(empty($_POST['tid'])) die();
			$_POST['fid'] = $this->uid;
			$_m = createModel('UserMail');
			$_m->put($_POST);
			Swoole_js::js_goto('发送成功','/person/mails/?act=send');
		}
		else
		{
			$this->swoole->tpl->display();
		}
	}

}