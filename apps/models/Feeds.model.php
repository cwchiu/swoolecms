<?php
class Feeds extends Model
{
	var $table = 'user_feed';

	function send($type,$uid,$tid=0,$event_id=0)
	{
		$id = $this->put(array('ftype'=>$type,'uid'=>$uid,'tid'=>$tid,'eventid'=>$event_id));
		$user = $this->swoole->model->UserInfo->getInfo($uid);
		$this->db->query("update {$this->table} set nickname='{$user['nickname']}' where id=$id");
		if($tid!==0)
		{
			$feed_type = SiteDict::get('feed_type');
			$user['info'] = $feed_type[$type][0];
			$user['look'] = $feed_type[$type][1];
			$user['link'] = $feed_type[$type][2];
			$user['event'] = $event_id;
			//UserMsg::insertMsg($tid,'feeds',$user);
		}
	}
	function recv($uid)
	{
		$list = $this->gets(array('tid'=>$uid,'limit'=>10));
		$c = count($list)<10;
		if($c)
		{
			$list += $this->db->query("select * from {$this->table} where uid in (select uid from user_relation where tid={$uid} and watched=1) order by id desc limit ".(16-$c))->fetchall();
		}
		return $list;
	}
}
?>