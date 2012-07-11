// JavaScript Document
var star_dlg;
var pic_dlg;
function show_ui_star() {
	var html = '<p>文字：<input type="text" name="star_title" id="star_title" size="42" /><br />链接：<input name="star_url" type="text" id="star_url" size="42" /></p>';
	var btns = [ new swoole.Button(
			'确定添加',
			function() {
				var link = '<img src="/static/images/world.png" width="16" height="16" /> <a target="_blank" href="'
						+ obj('star_url').value
						+ '">'
						+ obj('star_title').value + '</a> <a href="javascript:mblog_clear_link()">[删除]</a>';
				jQuery.post('/person/mylinks/?add', {
					'title' :obj('star_title').value,
					'url' :obj('star_url').value
				}, function(data) {
					obj('mblog_url').value = data;
				});
				obj('m_star').innerHTML = link;
				star_dlg.close();
			}) ];
	star_dlg = new swoole.Dialog('添加星标', html, 400, 200, true, btns, true);
	star_dlg.show();
}
function mblog_clear_link(){
	obj('m_star').innerHTML = '<li id="m_star"><a href="javascript:show_ui_star()"><img src="/static/images/star_1.png" /> 星标</a></li>';
	obj('mblog_url').value = '';
}
function mblog_clear_pic(){
	obj('m_pic').innerHTML = '<li id="m_pic"><a href="javascript:show_ui_pic(this)"><img src="/static/images/picture.png" /> 图片</a></li>';
	obj('mblog_pic').value = '';
}
function show_ui_pic() {
	var html = '<iframe frameborder="0" scrolling="auto" height="300" width="500" src="/myphoto/index/?from=mblog"></iframe>';
	pic_dlg = new swoole.Dialog('添加星标', html, 500, 300, true, "请从相册中选取图片",
			true, true);
	pic_dlg.show();
}
function show_pic(o,pic){
	var t = o.offsetTop;
	var l = o.offsetLeft;
	var h = o.offsetHeight;
	var w = o.offsetWidth;
	while (o = o.offsetParent) {
		t += o.offsetTop;
		l += o.offsetLeft;
	}
	obj("pic_preview").innerHTML = '<img src="'+pic+'" />';
	obj("pic_preview").style.top = t + h + "px";
	obj("pic_preview").style.left = l + "px";
	obj("pic_preview").style.display = "block";
}
function insert_pic(id, pic) {
	var link = '<img src="/static/images/photos.png" width="16" height="16" /> <a onmouseover="show_pic(this,\''
			+ pic
			+ '\')" onmouseout="obj(\'pic_preview\').style.display =\'none\';" target="_blank" href="/myphoto/show/?id='
			+ id
			+ '">内容贴图</a> <a href="javascript:mblog_clear_pic()">[删除]</a>';
	obj('mblog_pic').value = id;
	obj('m_pic').innerHTML = link;
	pic_dlg.close();
}
function addText(o, txt) {
	selection = document.selection;
	o.focus();
	if (typeof o.selectionStart != "undefined") {
		var s = o.selectionStart;
		o.value = o.value.substr(0, o.selectionStart) + txt
				+ o.value.substr(o.selectionEnd);
		o.selectionEnd = s + txt.length;
	} else if (selection && selection.createRange) {
		var sel = selection.createRange();
		sel.text = txt;
	} else {
		o.value += txt;
	}
}
