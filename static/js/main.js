function checklogin()
{
	$.get('/ajax.php?method=checklogin',null,function(data){
	    if(data){
			$('#checklogin').html('<li>欢迎 <strong>'+data+'</strong></li><li><a href="/person/index/">进入个人中心</a></li> <li><a href="/page/logout/">退出登录</a></li>');
		}
	});
}
function post_comment(){
	var post = {'aid':$('#aid').val(),
			'app':$('#app').val(),
			'authcode':$('#authcode').val(),
			'content':$('#comment_content').val()};
	
	$.post('/ajax.php?method=comment',post,function(data){
			if(data=='nologin'){
				if(confirm('您还未登录！是否跳转到登录页面？'));
				window.location.href = '/page/login/';
			}else if(data=='noauth'){
				alert('验证码错误');
			}
			else{
				alert('发布成功！');
				$('#comment_form')[0].reset();
				window.location.href = window.location.href;
			}
			$('#authcode_img')[0].src = $('#authcode_img').attr('src')+'?r='+Math.random();	
	});
	
}