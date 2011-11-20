<?php
function getCategorys($app)
{
    $model = createModel('Catelog');
    $all = $model->all();
    $all->filter("app='$app'");
    $all->order('id');
    $list = $all->fetchall();
    $new = array();
    foreach($list as $li)
    {
        if($li['fid']==0) $new[$li['id']] = $li;
        else $new[$li['fid']]['child'][] = $li;
    }
    return $new;
}

function getChildCategory($app,$fid=0)
{
    $model = createModel('Catelog');
    $all = $model->all();
    $all->filter("app='$app'");
    $all->filter("fid=$fid");
    $all->order('id');
    return $all->fetchall();
}

function getCategory($id)
{
    $model = createModel('Catelog');
    return $model->get($id)->get();
}
function getContentCount($app,$cate_id,$level='fid')
{
    $c[$level] = $cate_id;
    $_model = createModel($app);
    return $_model->count($c);
}
function getChildCount($fid)
{
    $c['fid'] = $fid;
    $_model = createModel('Catelog');
    return $_model->count($c);
}
/**
 * 获取CMS的APP列表
 * @return unknown_type
 */
function getApps()
{
    global $php;
    $list = $php->db->query('select * from '.TABLE_PREFIX.'_apps where close=0')->fetchall();
    $return = array();
    foreach($list as $li)
    {
        $return[$li['app']] = $li['name'];
    }
    return $return;
}

function getTags()
{
    $model = createModel('Tag');
    $tags = $model->all()->fetchall();
    $tags[] = array('id'=>0,'name'=>'不推荐');
    $new = array();
    foreach($tags as $t)
    {
        $new[$t['id']] = $t['name'];
    }
    return $new;
}

function getHtmlDetail($app,$id)
{
    $url = WEBROOT.'/cms.php?app='.$app.'&id='.$id;
    return file_get_contents($url);
}
function getHtmlList($app,$cate_id,$page=1,$level='fid')
{
    $url = WEBROOT."/cms.php?$level=$cate_id&app=$app&page=$page";
    return file_get_contents($url);
}

function cms_attachment(&$params,&$smarty)
{
    $attachment = <<<HTML
    <script language="javascript">
    function fck_insert(html)
    {
        var fck = FCKeditorAPI.GetInstance("content");
        fck.InsertHtml(html);
    }
    </script>
    <iframe src="/admin/admin.php?action=attachment&app={$params['app']}&aid={$params['aid']}" height="240" width="100%" frameborder="0" scrolling="no" id='upload_resource'></iframe>
HTML;
    return $attachment;
}

function cms_link(&$params,&$smarty)
{
    global $php;
    if(HTML_STATIC)
    {
        if(empty($params['use'])) return WEBROOT."/{$params['app']}/{$params['id']}.html";
        elseif($params['use']=='list') return WEBROOT."/{$params['app']}/list_{$params['id']}_1.html";
        elseif($params['use']=='page') return WEBROOT."/{$params['pagename']}.html";
    }
    else
    {
        if(empty($params['use'])) return WEBROOT."/cms.php?app={$params['app']}&id={$params['id']}";
        elseif($params['use']=='list') return WEBROOT."/cms.php?app={$params['app']}&{$params['level']}={$params['id']}";
        elseif($params['use']=='page') return WEBROOT."/index.php?p={$params['pagename']}";
    }
}

function cms_cate(&$params,&$smarty)
{
    if(empty($params['app'])) exit('App名称不能为空');
    if(empty($params['name'])) $params['name'] = 'cate';
    if(empty($params['fid'])) $params['fid'] = 0;
    $smarty->_tpl_vars[$params['name']] = getChildCategory($params['app'],(int)$params['fid']);
}

function cms_list(&$params,&$smarty)
{
    if(empty($params['cid'])) exit('分类不能为空');
    $cid = (int)$params['cid'];
    if(empty($params['name'])) $params['name'] = 'list'.$cid;
    $cate = getCategory($cid);
    $model = createModel($cate['app']);

    if($cate['fid']==0) $gets['fid'] = $cid;
    else $gets['cid'] = $cid;

    if(empty($params['select'])) $params['select'] = 'id,title,addtime';
    if(empty($params['num'])) $params['num']=10;

    //查询的条数
    $gets['limit'] = (int)$params['num'];
    //查询的字段
    $gets['select'] = $params['select'];

    if(!empty($params['order'])) $gets['order'] = $params['order'];
    if(!empty($params['dig'])) $gets['tagid'] = (int)$params['dig'];

    if(!empty($params['titlelen']))
    {
        $gets['select'] = str_replace('title',"substring( title, 1, {$gets['titlelen']} ) AS title,title as title_full",$gets['select']);
    }
    $smarty->_tpl_vars[$params['name']] = $model->gets($gets);
}

function cms_det(&$params,&$smarty)
{
	
}

function cms_conf(&$params,&$smarty)
{
	if(empty($params['name'])) exit('配置名称不能为空！');
	global $php;
	$c = $php->db->query('select cvalue from st_config where ckey="'.$params['name'].'" limit 1')->fetch();
	if(empty($c)) return false;
	else return $c['cvalue'];
}

function cms_htmlcode($file,&$smarty)
{
    if(!function_exists('file_ext')) import_func('file');
    $ext = file_ext($file);
    switch($ext)
    {
        case 'jpg':
        case 'gif':
        case 'png':
        case 'bmp':
            return "<img src=$file />";
        default:
            return "<a href=$file>$file</a>";
    }
}