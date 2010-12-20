<?php
class UserInfo extends Model
{
	var $table = 'user_login';
    function exists($username)
	{
		$rs = $this->db->query('select count(*) as cc from '.$this->table." where username='{$username}'");
		$cc = $rs->fetch();
		if($cc['cc']==0) return false;
		return true;
	}
}
?>