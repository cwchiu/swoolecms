<?php
class Func
{
    function mblog_link($id,$content,$maxlen=30)
    {
        $title = mb_substr($content,0,$maxlen);
        $end = mb_strpos($title,'。');

        if($end===false) $end = mb_strpos($title,'？');
        if($end===false) $end = mb_strpos($title,'，');
        if($end===false) $end = mb_strpos($title,',');
        $html = "<a href='/mblog/detail/?id={$id}'>";

        if($end===false or $end>$maxlen)
        {
            $html .= $title;
            return $html.'</a>'.mb_substr($content,$maxlen);
        }
        else
        {
            $html.=mb_substr($content,0,$end);
            return $html.'</a>'.mb_substr($content,$end);
        }
    }

    function parse_url($url)
    {
        if(substr($url,0,4)==='http') return $url;
        else return 'http://'.$url;
    }
}