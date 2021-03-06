<?php
require 'config.php';
require 'admin/func.php';
require LIBPATH.'/system/Filter.php';
Filter::request();
define('HTML_STATIC',true);


if(empty($_GET['app'])) exit;
$app = ucwords(substr($_GET['app'],0,10));
$php->tpl->assign('app',$app);
$model = createModel($app);

if(!empty($_GET['id']))
{
    $aid = (int)$_GET['id'];
    //模板名称
    $tplname = strtolower($app).'_detail.html';

    //获取详细内容
    $det = $model->get($aid)->get();
    //阅读次数增加
    $model->set($aid,array('click_num'=>'`click_num`+1'));

    //关键词
    if(!empty($_GET['q']))
    {
        $det['content'] = preg_replace("/({$_GET['q']})/i","<font color=red>\\1</font>",$det['content']);
    }

    //获取小分类信息
    $cate = getCategory($det['cid']);
    $php->tpl->assign("cate",$cate);

    $ccate = getCategory($det['fid']);
    $php->tpl->assign("ccate",$ccate);

    Widget::comment('News',$aid);

    //是否使用特殊模板
    if($ccate['tpl_detail']) $tplname = $ccate['tpl_detail'];
    if($cate['tpl_detail']) $tplname = $cate['tpl_detail'];

    $php->tpl->assign('det',$det);
    $php->tpl->display($tplname);
}
elseif(!empty($_GET['cid']))
{
    //Error::dbd();
    $tplname = strtolower($app).'_list.html';
    $cate_id = (int)$_GET['cid'];
    $cate = getCategory($cate_id);
    if(empty($cate))
    {
        Swoole_js::js_back('不存在的分类！','/index.php');
        exit;
    }

    if($cate['fid']==0)
    {
        $php->tpl->assign("fid",$cate_id);
        $php->tpl->assign("ccate",$cate);
        if($cate['tplname']) $tplname = $cate['tplname'];
        $gets['fid'] = $cate_id;
    }
    else
    {
        if($cate['tplname']) $tplname = $cate['tplname'];
        $php->tpl->assign("cate",$cate);
        $ccate = $php->db->query("select * from st_catelog where id={$cate['fid']} limit 1")->fetch();
        $php->tpl->assign("ccate",$ccate);
        $gets['cid'] = $cate_id;
    }

    $pager = null;
    $gets['order'] = 'addtime desc';
    $gets['page'] = empty($_GET['page'])?1:(int)$_GET['page'];
    $gets['pagesize'] = empty($model->pagesize)?Swoole::$config->cms['pagesize']:$model->pagesize;
    $gets['select'] = "id,title,addtime";
    $list = $model->gets($gets,$pager);
    if($php->config->cms['html_static']) $pager->page_tpl = WEBROOT."/$app/list_{$cate_id}_%s.html";

    $pager = array('total'=>$pager->total,'render'=>$pager->render());
    $php->tpl->assign('pager',$pager);
    $php->tpl->assign("list",$list);
    $php->tpl->assign('cid',$cate_id);
    $php->tpl->display($tplname);
}