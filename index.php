<?php
require('config.php');
require 'admin/func.php';

GeneralView::$method_prefix = 'act';
class app_index extends GeneralView
{
	function act_list()
	{
		if(empty($_GET['p']) or $_GET['p']=='index')
		{
			include WEBPATH.'/gold/sitedict'.date('Ymd').'.php';
		    	$this->swoole->tpl->assign('dict',$sitedict);
			$this->swoole->tpl->display('index.html');
		}
		else
		{
			$page = $_GET['p'];
			$model = createModel('News');
			$det = $model->get($page,'pagename');
			$this->swoole->tpl->assign('det',$det);
			$this->swoole->tpl->display('index_page.html');
		}
	}

	function act_search()
	{
		//Error::dbd();
		if(!empty($_GET['q']))
		{
			$php = $this->swoole;
			$cates = getProductCategorys();
            $php->tpl->assign('cates',$cates);
			$table = $this->swoole->model->Product;
			$gets['where'][] = 'PN like \'%'.$_GET['q'].'%\'';
			$gets['orwhere'][] = 'name like \'%'.$_GET['q'].'%\'';
			$gets['page'] = empty($_GET['page'])?1:$_GET['page'];
			$gets['pagesize'] =  12;
			$list = $php->model->Product->gets($gets,$pager);
			if(empty($list)) header('location:/page.php?view=nofound');
			$pager = array('total'=>$pager->total,'render'=>$pager->render());
			$php->tpl->assign('pager',$pager);
			$php->tpl->assign("list",$list);
			$php->tpl->display('product_search.html');
		}
	}
}
$app = new app_index($php);
$app->run();
?>
