<?php
/**
 * 后台文章管理系统模块
 * @author guoerbo
 * 2009-08-06
 */
require_once('../config.php');
require_once 'check.php';
require_once 'func.php';
require_once WEBPATH.'/dict/apps.php';

class admin extends GeneralView
{
	public $app;
	public $uid;
	public $catelog;

	function __construct($swoole)
	{
		parent::__construct($swoole);
		$this->uid = $_SESSION['admin_user_id'];
	}

    function admin_dict()
	{
		$dictname = "sitedict";
	    $filename =  WEBPATH.'/gold/sitedict'.date('Ymd').'.php';
	    $options = array('销售价（元/盎司）','销售价（元/克）','回购价（元/盎司）','回购价（元/克）');
	    if(!is_file($filename))
	    {
	        file_put_contents($filename,"<?php\n\${$dictname}=".var_export(array(),true).';');
	    }
	    require $filename;

		if(isset($_GET['del']))
		{
			unset($sitedict[$_GET['del']]);
			file_put_contents($filename,"<?php\n\${$dictname}=".var_export($sitedict,true).';');
			Swoole_js::js_goto('删除成功','admin.php?action=dict');
		}
		elseif(isset($_GET['id']))
		{
		    if($_POST)
    		{
    			$sitedict[$_GET['id']] = $_POST;
    			file_put_contents($filename,"<?php\n\${$dictname}=".var_export($sitedict,true).';');
    			Swoole_js::js_goto('修改成功','admin.php?action=dict');
    		}
    		else
    		{
    		     $this->swoole->tpl->assign('dict',$sitedict[$_GET['id']]);
		         $this->swoole->tpl->display('admin_dict_detail.html');
    		}

		}
		else
		{
    		if($_POST)
    		{
    			$sitedict[] = $_POST;
    			file_put_contents($filename,"<?php\n\${$dictname}=".var_export($sitedict,true).';');
    			Swoole_js::js_goto('添加成功','admin.php?action=dict');
    		}
    		foreach($sitedict as $d)
    		{
    		    unset($options[array_search($d['title'],$options)]);
    		}
    		$opt = false;
    		if(!empty($options))
    		{
    		    $opt = Form::select('title',$options,null,true,array('empty'=>'请选择项目'));
    		    $this->swoole->tpl->assign('opt',$opt);
    		}
		    $this->swoole->tpl->assign('sitedict',$sitedict);
		    $this->swoole->tpl->display('admin_dict.html');
		}

	}

	private function _check_cms()
	{
		if(empty($_REQUEST['app'])) exit('App名称不能为空');
		$this->app = $_REQUEST['app'];
		$this->catelog = $this->app.'Catelog';
		$this->swoole->tpl->assign('app',$this->app);
	}

	function admin_list()
	{
		$this->_check_cms();
		if(!empty($_GET['fid'])) $gets['fid'] = (int)$_GET['fid'];
		if(!empty($_GET['cid'])) $gets['cid'] = (int)$_GET['cid'];
		$gets['page'] = empty($_GET['page'])?1:$_GET['page'];
		$gets['pagesize'] = 10;
		$pager=null;
		$model = createModel($this->app);
		$list = $model->gets($gets,$pager);

		$cates = getCategorys($this->app);
		$tags = getTags($this->app);

		$this->swoole->tpl->assign('cates',$cates);
		$this->swoole->tpl->assign('tags',$tags);
		$pager = array('total'=>$pager->total,'render'=>$pager->render());
		$this->swoole->tpl->assign('pager',$pager);
		$this->swoole->tpl->assign('list',$list);
		$this->swoole->tpl->display('admin_'.strtolower($this->app).'_list.html');
	}

	function admin_del()
	{
		$this->_check_cms();
		if(!empty($_GET['del']))
		{
			$model = createModel($this->app);
			$gets['id'] = (int)$_GET['del'];
			$gets['limit'] = 1;
			$model->dels($gets);
			Swoole_js::js_back('删除成功');
		}
	}

	function admin_add()
	{
		$this->_check_cms();
		$model = createModel($this->app);
		if($_POST)
		{
			$this->proc_upfiles();
			if(isset($_POST['cid']))
			{
				$cid = (int)$_POST['cid'];
				$cate = getCategory($cid);
				$_POST['cname'] = $cate['name'];
				$_POST['fid'] = $cate['fid'];
				$fcate = getCategory($cate['fid']);
				$_POST['fname'] = $fcate['name'];
			}
			elseif(isset($_POST['fid']))
			{
				$fid = (int)$_POST['fid'];
				$_POST['fid'] = $fid;
				$fcate = getCategory($fid);
				$_POST['fname'] = $fcate['name'];
			}

			if(!empty($_POST['id']))
			{
				//如果得到id，说明提交的是修改的操作
				$id = (int)$_POST['id'];
				UNSET($_POST['id']);
				$model->set($id,$_POST);

				Swoole_js::js_back('修改成功',-2);
				if($php->config->cms['html_static'])
				{
					if(!empty($_POST['pagename'])) $this->updatePage($_POST['pagename']);
					else $this->updateDetail($id);
				}
			}
			else
			{
				//如果没得到id，说明提交的是添加操作
				//if(!isset($_POST['cid']) and isset($_GET['fid'])) $_POST['cid'] = $_GET['fid'];
				$_POST['uid'] = $_SESSION['admin_user_id'];
				$_POST['uname'] = $_SESSION['admin_user']['realname'];
				$id = $model->put($_POST);
				Swoole_js::js_back('添加成功');

				if($php->config->cms['html_static'])
				{
					if(!empty($_POST['pagename'])) $this->updatePage($_POST['pagename']);
					else $this->updateDetail($id);
				}
			}
		}
		else
		{
			//Error::dbd();
			if(empty($_GET['fid']) and empty($_GET['cid']))
			{
				$cates = getCategorys($this->app);
				$this->swoole->tpl->assign('cates',$cates);
			}
			$this->swoole->plugin->load('fckeditor');
			if(isset($_GET['id']))
			{
				$id = $_GET['id'];
				$det = $model->get($id)->get();
				$editor = editor("content",$det['content'],480);
				$this->swoole->tpl->assign('det',$det);
				$this->swoole->tpl->assign('cates',false);
			}
			else
			{
				$editor = editor("content",'',480);
			}
			$this->swoole->tpl->assign('editor',$editor);
			$this->swoole->tpl->display('admin_'.strtolower($this->app).'_detail.html');
		}
	}

	function admin_category()
	{
		$this->_check_cms();
		$this->swoole->tpl->assign('act_name','category');
		$config['tpl.add'] = 'admin_catelog_add.html';
		$config['tpl.list'] = 'admin_catelog_list.html';
		if(isset($config['limit']) and $config['limit']===true) $this->swoole->tpl->assign('limit',true);
		else $this->swoole->tpl->assign('limit',false);

		$_model = createModel('Catelog');
		if(isset($_GET['add']))
		{
			if(!empty($_POST['name']))
			{
				$data['name'] = trim($_POST['name']);
				$data['pagename'] = trim($_POST['pagename']);
				$data['fid'] = intval($_POST['fid']);
				$data['intro'] = trim($_POST['intro']);
				$data['keywords'] = trim($_POST['keywords']);
				#增加
				if(empty($_POST['id']))
				{
					$data['app'] = $this->app;
					$_model->put($data);
					Swoole_js::js_back('增加成功！');
				}
				#修改
				else
				{
					$_model->set((int)$_POST['id'],$data);
					Swoole_js::js_back('修改成功！');
				}
			}
			else
			{
				if(!empty($_GET['id']))
				{
					$data = $_model->get((int)$_GET['id'])->get();
					$this->swoole->tpl->assign('data',$data);
				}
				$this->swoole->tpl->display($config['tpl.add']);
			}
		}
		else
		{
			if(!empty($_GET['del']))
			{
				$del_id = intval($_GET['del']);
				$cate = $_model->get($del_id);
				if($cate->fid==0 and getChildCount($del_id)>0)
				{
					Swoole_js::js_back('该分类下还有子分类，请删除全部子分类后重试！');
					exit;
				}
				if($cate->fid!=0 and getContentCount($this->app,$del_id,'cid')>0)
				{
					Swoole_js::js_back('该分类下内容，请删除分类下的所有内容后重试！');
					exit;
				}
				$_model->del($del_id);
				Swoole_js::js_back('删除成功！');
			}
			//Error::dbd();
			$get['fid']  = empty($_GET['fid'])?0:(int)$_GET['fid'];
			$get['page'] = empty($_GET['page'])?1:(int)$_GET['page'];
			$get['app'] = $this->app;
			$get['pagesize'] = 15;
			$pager = null;
			$list = $_model->gets($get,$pager);
			$this->swoole->tpl->assign('list',$list);
			$this->swoole->tpl->assign('pager', array('total'=>$pager->total,'render'=>$pager->render()));
			$this->swoole->tpl->display($config['tpl.list']);
		}
	}

	function admin_lot()
	{
		$this->_check_cms();
		if(!empty($_POST['job']) )
		{
			if(empty($_POST['ids']))
			{
				Swoole_js::js_alert('没有选中操作的对象！');
				exit;
			}
			switch($_POST['job'])
			{
				case 'push':
					$set['tagid'] = (int)$_POST['push'];
					$get['in'] = array('id',implode(',',$_POST['ids']));
					$model = createModel($this->app);
					$model->sets($set,$get);
					break;
				case 'cate':
					$cid = (int)$_POST['cate'];
					$cate = getCategory($cid);
					$set['cid'] = $cid;
					$set['cname'] = $cate['name'];
					$set['fid'] = $cate['fid'];

					$fcate = getCategory($cate['fid']);
					$set['fname'] = $fcate['name'];

					$get['in'] = array('id',implode(',',$_POST['ids']));
					$model = createModel($this->app);
					$model->sets($set,$get);
					break;
				default:
					break;
			}
			Swoole_js::js_parent_reload('操作成功！');
		}
	}

	function admin_html()
	{
		$this->_check_cms();
		$dir = WEBPATH.'/'.$this->app;
		if(!is_dir($dir)) mkdir($dir,0777,true);

		if(!empty($_GET['id']))
		{
			$this->updateDetail((int)$_GET['id']);
		}
		if(!empty($_GET['pagename']))
		{
			$this->updatePage($_GET['pagename']);
		}
		elseif(!empty($_GET['all']) and !empty($_POST['detail']))
		{
			$_model = createModel($this->app);
			$gets['select'] = 'id,pagename';
			$all = $_model->gets($gets);

			foreach($all as $a)
			{
				if($a['pagename']) $this->updatePage($a['pagename']);
				else $this->updateDetail($a['id']);
			}
		}
		elseif(!empty($_GET['all']) and !empty($_POST['catelog']))
		{
			$fcates = getChildCategory($this->app);
			foreach($fcates as $fc)
			{
				$this->updateCatelog($fc['id']);
				$cl = getChildCategory($this->app,$fc['id']);
				foreach($cl as $cc)
				{
					$this->updateCatelog($cc['id']);
				}
			}
		}
		elseif(!empty($_GET['cate_id']))
		{
			$cate_id = (int)$_GET['cate_id'];
			$this->updateCatelog($cate_id);
		}
		Swoole_js::js_back('更新成功！');
	}

	function admin_attachment()
	{
		$this->_check_cms();
		import_func('file');
		import_func('js');
		$model = createModel('Attachment');
		$entity = createModel($this->app);

		if(empty($_GET['aid']))
		{
			$res = $entity->getStatus();
			$id = $res['Auto_increment'];
		}
		else
		{
			$id = (int)$_GET['aid'];
		}
		if(isset($_GET['del']))
		{
			$model->del((int)$_GET['del']);
		}
		if(isset($_FILES['media']))
		{
			if(empty($_POST['title'])) $_POST['title']= $_FILES['media']['name'];
			$_POST['url'] = file_upload('media');
			if(!empty($_POST['url']))
			{
				$_POST['filetype'] = file_gettype($_FILES['media']['type']);
				$_POST['filesize'] = $_FILES['media']['size'];
				$_POST['user_id'] = $this->uid;
				$_POST['app'] = $this->app;
				$model->put($_POST);
			}
		}
		$list = $model->gets(array('aid'=>$id,'app'=>$this->app));
		include "templates/admin_attachment.html";
	}

	function admin_users()
	{
		global $ugroups;
		checkAB('ch0');
		$php = $this->swoole;
		$table = 'st_admin';
		$admin_id =  $_SESSION['admin_user_id'];

		$user = $php->db->query("select * from $table where id=$admin_id")->fetch();
		$php->tpl->assign('user',$user);
		$php->tpl->assign('groups',$ugroups);
		if(!checkAG('st_admin')) exit;
		if(isset($_POST['username']))
		{
			$res=$php->db->query("select count(id) as cc from $table where username='{$_POST['username']}'")->fetch();
			if($res['cc']==0)
			{
				$_POST["password"]=Auth::mkpasswd($_POST["username"],$_POST["password"]);
				$php->db->insert($_POST,$table);
			}
			else
			{
				Swoole_js::js_back("已存在此用户");
			}
		}
		if(isset($_GET['del']) and $_GET['del']!="")
		{
			$php->db->query('delete from '.$table.' where id='.$_GET['del']);
			Swoole_js::location("/admin/admin_news.php?action=users");
		}
		else
		{
			$pagesize=10;

			$res=$php->db->query("select count(id) as cc from $table")->fetch();
			$num=$res['cc'];
			require(LIBPATH.'/code/page.php');
			$res=$php->db->query("select * from $table limit $offset,$pagesize");
			$php->tpl->assign("list",$res->fetchall());
			$php->tpl->display("admin_adminuser.html");
		}
	}

	function admin_chpasswd()
	{
		//Error::dbd();
		//Error::sessd();
		$php = $this->swoole;
		$table = 'st_admin';
		$admin_id =  $_SESSION['admin_user_id'];
		$user = $php->db->query("select * from $table where id=$admin_id")->fetch();
		$php->tpl->assign('user',$user);

		if(isset($_POST['password']))
		{
			$oldpasswd = Auth::mkpasswd($user['username'],$_POST['oldpasswd']);
			if($_POST["password"]!=$_POST["repassword"])
			{
				Swoole_js::js_back('密码不一致！');
				exit;
			}

			$res=$php->db->query("select count(id) as cc from $table where id={$_POST['id']} and password='$oldpasswd'")->fetch();
			if($res['cc']==1)
			{
				$_POST["password"]=Auth::mkpasswd($user['username'],$_POST["password"]);
				$php->db->update($_POST["id"],array('password'=>$_POST["password"]),$table);
				Swoole_js::js_back('修改成功！',-2);
			}
			else
			{
				Swoole_js::js_back('旧密码错误！');
			}
		}
		else
		{
			$php->tpl->display("admin_adminuser_chpasswd.html");
		}
	}

	function admin_reply()
	{
		if(empty($_GET['id'])) die('No ID!');
		if(empty($_POST['reply']))
		{
			$gb = $this->swoole->model->Guestbook->get($_GET['id'])->get();
			$this->swoole->tpl->assign('gb',$gb);
			$this->swoole->tpl->display('admin_reply.html');
		}
		else
		{
			$this->swoole->model->Guestbook->set($_GET['id'],$_POST);
			Swoole_js::js_back('添加成功');
		}
	}

	function admin_guestbook()
	{
		if(isset($_GET['id']))
		{
			$det = $this->swoole->model->Guestbook->get((int)$_GET['id'])->get();
			$this->swoole->tpl->assign('det',$det);
			$this->swoole->tpl->display('admin_guestbook_detail.html');
		}
		else
		{
			if(isset($_GET['del']))
			{
				$this->swoole->model->Guestbook->del((int)$_GET['del']);
			}
			$gets['page'] = empty($_GET['page'])?1:$_GET['page'];
	        $gets['select'] = "*";
	        $gets['pagesize'] = 10;
	        $pager=null;
	        $list = $this->swoole->model->Guestbook->gets($gets,$pager);
	        $pager = array('total'=>$pager->total,'render'=>$pager->render());
	        $this->swoole->tpl->assign('pager',$pager);
	        $this->swoole->tpl->assign('list',$list);
	        $this->swoole->tpl->display('admin_guestbook.html');
		}
	}

	private function updateCatelog($cate_id)
	{
		$dir = WEBPATH.'/'.$this->app;
		if(!is_dir($dir)) mkdir($dir,0777,true);

		$cate = getCategory($cate_id);
		if(empty($cate)) exit("不存在的分类");

		if($cate['fid']==0) $level = 'fid';
		else $level = $cid;
		$c = getContentCount($this->app,$cate_id,'fid');
		$pager = new Pager(array('total'=>$c,'perpage'=>$this->swoole->config->cms['pagesize']));
		for($i=1;$i<=$pager->totalpage;$i++)
		{
			$html = getHtmlList($this->app,$cate_id,$i,$level);
			$filename = $dir.'/list_'.$cate_id.'_'.$i.'.html';
			file_put_contents($filename,$html);
		}
	}

	private function updateDetail($id)
	{
		$dir = WEBPATH.'/'.$this->app;
		if(!is_dir($dir)) mkdir($dir,0777,true);
		$content = getHtmlDetail($this->app,$id);
		file_put_contents($dir.'/'.$id.'.html',$content);
	}

	private function updatePage($pagename)
	{
		$filename = WEBPATH.'/'.$pagename.'.html';
		$dir = dirname($filename);
		if(!is_dir($dir)) mkdir($dir,0777,true);
		file_put_contents($filename,$content);

		$url = WEBROOT.'/index.php?p='.$pagename;
		file_put_contents($filename,file_get_contents($url));
	}
}

$act = new admin($php);
$act->run();