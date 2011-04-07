<?php
class UserComment extends Model
{
	//Here write Database table's name
	var $table = 'user_comment';

	function getByAid($app,$aid)
	{
	    $gets['leftjoin'] = array(createModel('UserInfo')->table,createModel('UserInfo')->table.'.id='.$this->table.'.uid');
	    $gets['select'] = 'content,uid,uname,avatar,addtime';
	    $gets['aid'] = $aid;
	    $gets['app'] = $app;
	    $gets['order'] = 'addtime';
	    $gets['limit'] = 50;
	    return $this->gets($gets);
	}
}