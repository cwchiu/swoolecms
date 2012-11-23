<?php
class Func
{
	static $sort_mode = -1; //升序/降序
	function time_sort($a,$b)
	{
		if(strtotime($a['addtime'])>strtotime($b['addtime'])) return self::$sort_mode;
		else return -self::$sort_mode;
	}
    function mblog_link($id,$content,$maxlen=30,$return_title=false)
    {
        $title = mb_substr($content,0,$maxlen);
        $end = mb_strpos($title,'。');

        if($end===false) $end = mb_strpos($title,'？');
        if($end===false) $end = mb_strpos($title,'，');
        if($end===false) $end = mb_strpos($title,',');
        $html = "<a href='/mblog/detail/{$id}'><strong>";

        if($end===false or $end>$maxlen)
        {
            $html .= $title;
            if($return_title) return $html.'</a>';
            else return $html.'</strong></a>'.mb_substr($content,$maxlen);
        }
        else
        {
            $html.=mb_substr($content,0,$end);
            if($return_title) return $html.'</a>';
            else return $html.'</strong></a>'.mb_substr($content,$end);
        }
    }

    function getUser($uid)
    {
    	$_user = createModel('UserInfo');
    	return $_user->getInfo($uid);
    }

    function parse_url($url)
    {
        if(substr($url,0,4)==='http') return $url;
        else return 'http://'.$url;
    }
}