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

function getApps()
{
	global $php;
	return $php->db->query('select * from '.TABLE_PREFIX.'_app')->fechall();
}

function getTags()
{
	$model = createModel('Tag');
	return $model->all()->fetchall();
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

function cms_attachment(&$params)
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

function cms_link(&$params)
{
	global $php;
	if($php->config->cms['html_static'])
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

function cms_htmlcode($file)
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