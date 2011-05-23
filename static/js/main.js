function checklogin() {
	$
			.get(
					'/ajax.php?method=checklogin',
					null,
					function(data) {
						if (data) {
							$('#checklogin')
									.html(
											'<li>欢迎 <strong>' + data + '</strong></li><li><a href="/person/index/">进入个人中心</a></li> <li><a href="/page/logout/">退出登录</a></li>');
						}
					});
}
function post_comment() {
	var post = {
		'aid' :$('#aid').val(),
		'app' :$('#app').val(),
		'authcode' :$('#authcode').val(),
		'content' :$('#comment_content').val()
	};

	$.post('/ajax.php?method=comment', post, function(data) {
		if (data == 'nologin') {
			if (confirm('您还未登录！是否跳转到登录页面？'))
				window.location.href = '/page/login/';
		} else if (data == 'noauth') {
			alert('验证码错误');
		} else {
			alert('发布成功！');
			$('#comment_form')[0].reset();
			window.location.href = window.location.href;
		}
		$('#authcode_img')[0].src = $('#authcode_img').attr('src') + '?r='
				+ Math.random();
	});
}

function ask_best(reid) {
	var post = {
		'reid' :reid
	};
	$.post('/ajax.php?method=ask_best', post, function(data) {
		if (data == 'nologin') {
			if (confirm('您还未登录！是否跳转到登录页面？'))
				window.location.href = '/page/login/';
		} else {
			alert('采纳成功！');
			window.location.href = window.location.href;
		}
	});
}

function ask_vote(reid, btn) {
	var post = {
		'reid' :reid
	};
	$.post('/ajax.php?method=ask_vote', post, function(data) {
		if (data == 'nologin') {
			if (confirm('您还未登录！是否跳转到登录页面？'))
				window.location.href = '/page/login/';
		} else {
			alert('投票成功！');
			$(btn).hide();
		}
	});
}
function auto_save(id){
	var html = FCKeditorAPI.GetInstance('content').GetXHTML(true);
	//内容为空
	if(html=='') return false;
	//内容未改变
	if(blog_content==html) return false; 
	var post = {'title':$('#title').val(),'c_id':$('#c_id').val(),'content':html,'autosave':1,'id':blog_id};
	jQuery.post('/myblog/write/?act=draft',post,function(res){
		res = parseInt(res);
		if(res>1) blog_id = res;
		var now=new Date();
		var notice = '自动保存提示：草稿已自动保存，时间：'+now.getHours()+'点'+now.getMinutes()+'分';
		$('#save_notice').html(notice);
		blog_content = html;
	});
	
}