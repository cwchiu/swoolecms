<?php
class FrontPage extends Controller
{
	/**
	 * 展示用户信息
	 * @param $uid
	 * @return unknown_type
	 */
	protected function userinfo($uid)
	{
		$_user = createModel('UserInfo');
		$_cate = createModel('UserLogCat');
		$_pic = createModel('UserPhoto');
		$user = $_user->getInfo($uid);
		if(empty($user)) return false;

		$user['skill_info'] = implode('、',$user['skill']);
		$gets2['select'] = 'name,id,num';
		$gets2['uid'] = $uid;
		$gets2['order'] = 'id';
		$gets2['limit'] = 15;
		$blog_cates = $_cate->gets($gets2);
		if($user['sex']=='女') $ta = '她';
		else if($user['sex']=='男') $ta = '他';
		$this->swoole->tpl->assign('user',$user);
		$this->swoole->tpl->assign('ta',$ta);
		$this->swoole->tpl->assign('blog_cates',$blog_cates);

		$gets3['uid'] = $uid;
		$c = $_pic->count($gets3);
		$this->swoole->tpl->assign('myphoto_count',$c);
		if($c>0)
		{
			$gets3['limit'] = 1;
			$gets3['select'] = 'imagep';
			$pic = $_pic->gets($gets3);
			$this->swoole->tpl->assign('myphoto',$pic[0]);
		}
	}
	/**
	 * 获取微博客内容列表
	 * @param $pagesize
	 * @return unknown_type
	 */
	protected function getMblogs($pagesize=10,$uid=0)
	{
		$_mblog = createModel('MicroBlog');
		$_user = createModel('UserInfo');
		$_photo = createModel('UserPhoto');
		$_link = createModel('UserLink');

		$gets1['select'] = $_mblog->table.'.id as id,uid,pic_id,url_id,sex,substring(content,1,170) as content,nickname,avatar,UNIX_TIMESTAMP(addtime) as addtime,reply_count';
		$gets1['order'] = $_mblog->table.'.id desc';
		if(!empty($uid)) $gets1['uid'] = $uid;
		$gets1['leftjoin'] = array($_user->table,$_user->table.'.id='.$_mblog->table.'.uid');
		$gets1['page'] = empty($_GET['page'])?1:(int)$_GET['page'];
		$gets1['pagesize'] = $pagesize;

		$mblogs_atta = array();
		$mblogs = $_mblog->gets($gets1,$pager);
		$pager->span_open = array();
		$pager = array('total'=>$pager->total,'render'=>$pager->render());

		foreach($mblogs as &$m)
		{
			$m['content'] = Func::mblog_link($m['id'],$m['content']);
			$m['addtime'] = date('n月j日 H:i',$m['addtime']);
			if(!empty($m['url_id'])) $mblogs_atta['url'][] = $m['url_id'];
			if(!empty($m['pic_id'])) $mblogs_atta['pic'][] = $m['pic_id'];
		}
		if(!empty($mblogs_atta['pic']))
		{
			$pics = $_photo->getMap(array('select'=>'id,imagep,picture','in'=>array('id',implode(',',$mblogs_atta['pic']))));
			$this->swoole->tpl->assign('pics',$pics);
		}
		if(!empty($mblogs_atta['url']))
		{

			$urls = $_link->getMap(array('select'=>'id,title,url','in'=>array('id',implode(',',$mblogs_atta['url']))));
			$this->swoole->tpl->assign('urls',$urls);
		}
		$this->swoole->tpl->assign('mblogs',$mblogs);
		$this->swoole->tpl->assign('pager',$pager);
	}
	
	function getActiveUsers($num = 10)
	{
		$_uids = array();
		$_mblog = createModel('MicroBlog');
		$_user = createModel('UserInfo');
		$table = $_mblog->table;
		$uids = $this->swoole->db->query("select uid from $table order by id desc limit 500")->fetchall();
		foreach($uids as $u)
		{
			$_uids[$u['uid']] = 1;
		}
		$gets['select'] = 'id,nickname,avatar';
		$gets['in'] = array('id', implode(',', array_keys($_uids)));
		$_users = $_user->getMap($gets);
		$new = array();
		foreach($_uids as $uid=>$u)
		{
			$new[] = $_users[$uid];
		}
		return $new;
	}
}