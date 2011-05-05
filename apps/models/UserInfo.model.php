<?php
class UserInfo extends Model
{
    public $table = 'user_login';
    public $cache_life = 600;

    function exists($username)
    {
        $rs = $this->db->query('select count(*) as cc from '.$this->table." where username='{$username}'");
        $cc = $rs->fetch();
        if($cc['cc']==0) return false;
        return true;
    }

    function getInfo($uid)
    {
        $cache_key = 'user_info_'.$uid;
        $user = $this->swoole->cache->get($cache_key);

        if(empty($user))
        {
            require WEBPATH.'/dict/forms.php';
            $user = $this->get($uid)->get();
            $user['sex'] = $forms['sex'][$user['sex']];
            $user['education'] = $forms['education'][$user['education']];
            $user['php_level'] = $forms['level'][$user['php_level']];

            $_skill = createModel('UserSkill')->getMap();
            $_s = explode(',',$user['skill']);
            foreach($_s as $s)
            {
                $skill[] = $_skill[$s];
            }
            $user['skill'] = $skill;
            $this->swoole->cache->set($cache_key,$user,$this->cache_life);
        }
        return $user;
    }
}
?>