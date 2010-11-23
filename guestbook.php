<?php
require 'config.php';
require 'admin/func.php';

if($_POST)
{
    if(empty($_POST['realname']))
	{
		Swoole_js::js_back('姓名不能为空！');
		exit;
	}
    if(empty($_POST['mobile']))
	{
		Swoole_js::js_back('电话不能为空！');
		exit;
	}
	unset($_POST['x'],$_POST['y']);
	$_POST['product'] = implode(',',$_POST['product']);
	$_POST['source'] = implode(',',$_POST['source']);	
	$php->model->Guestbook->put($_POST);
	Swoole_js::js_goto('注册成功！','guestbook.php');
}

if(!empty($_GET['id']))
{
	$gb = $php->model->Guestbook->get($_GET['id'])->get();
	$php->tpl->assign('gb',$gb);
	$php->tpl->display('guestbook_detail.html');
}
else
{
	require 'dict/forms.php';
    $pager = null;
	$gets['page'] = empty($_GET['page'])?1:$_GET['page'];
	$gets['pagesize'] =  12;
	$gets['select'] = "id,username,title,addtime";
	$gets['where'][] = "reply!=''";
	$list = $php->model->Guestbook->gets($gets,$pager);

	$_forms['title'] = Form::radio('title',$forms['title'],null,true,array('empty'=>'请选择称谓'));
	$_forms['age'] = Form::select('age',$forms['age'],null,true,array('empty'=>'请选择年龄阶段'));
	$_forms['ctime'] = Form::select('ctime',$forms['ctime'],null,true,array('empty'=>'请选择方便沟通的时间'));
	$_forms['product'] = Form::checkbox('product',$forms['product'],null,true);
	$_forms['source'] = Form::checkbox('source',$forms['source'],null,true);

	$pager = array('total'=>$pager->total,'render'=>$pager->render());
	$php->tpl->assign('pager',$pager);
    $php->tpl->assign('forms',$_forms);
	$php->tpl->assign("list",$list);
	$php->tpl->display('guestbook.html');
}


