var users;
var socket = {
	config : {
		ip :"www.swoole.com",
		port :8080,
		flashcontainer :"flashcontainer",
		auto :true
	},
	connect : function() {
		socket.flash.log("begin connect to session server");
		sysinfo("[系统提示] 正在连接聊天室服务器...<br />");
		socket.flash.connect(socket.config.ip, socket.config.port);
	},
	send : function(msg) {
		if (socket.isConnected >= 1) {
			socket.flash.send(msg);
		}
	},
	loaded : function() {
		socket.flash = document.getElementById("socketBridge");
		socket.isConnected = 0;
		if (socket.config.auto) {
			socket.connect();
		}
	},
	connected : function() {
		socket.isConnected = 1;
		socket.flash.log("connected to session server");
		socket.flash.send('/setname ' + uid + ' ' + uname);
		sysinfo("[系统提示] 已连接到服务器，正在初始化帐户...<br />");
	},
	close : function() {
		socket.flash.close();
		socket.isConnected = 0;
		socket.flash.log("close connection");
	},
	disconnected : function() {
		socket.isConnected = 0;
		socket.flash.log("disconnected");
	},
	ioError : function(msg) {
		socket.flash.log(msg);
		socket.isConnected = 0;
	},
	securityError : function(msg) {
		socket.flash.log(msg);
		socket.isConnected = 0;
	},
	receive : function(msg) {
		socket.flash.log("receive from server:" + msg);
		recvmsg(msg);
	}
};
/**
 * 初始化聊天室
 * 
 * @return
 */
function initChatClient() {
	var so = new SWFObject("/static/swf/socket_bridge.swf", "socketBridge",
			"1", "1", "9", "#ffffff");
	so.addParam("allowscriptaccess", "always");
	so.addVariable("scope", "socket");
	so.write(socket.config.flashcontainer);
}
/**
 * 插入屏幕
 * 
 * @param msg
 * @return
 */
function sysinfo(msg) {
	jQuery('#msglist').append(msg);
}
/**
 * 接收到消息的回调函数
 * 
 * @param msg
 * @return
 */
function recvmsg(msg) {
	var txt = '';
	var now = new Date;
	var ntime = now.getHours() + ':' + now.getMinutes() + ':'
			+ now.getSeconds();
	if ("setname success" == msg) {
		sysinfo('[系统提示] 登录成功！<br />');
		socket.flash.send('/getusers');
		sysinfo('[系统提示] 正在获取已登录用户信息...<br />');
	} else if ("user exists" == msg) {
		socket.close();
		sysinfo('[系统提示] 您已登录，请不要重复登陆！<br />');
	} else if (msg.substring(0, 6) == 'users:') {
		users = eval('(' + msg.substring(6) + ')');
		sysinfo('[系统提示] 获取成功！<br />');
		init_userlist();
	} else {
		var data = eval('(' + msg + ')');
		if (data.type == 'msg') {
			var ntime = now.getHours() + ':' + now.getMinutes() + ':'
					+ now.getSeconds();
			if (data.to == '0') {
				txt = '<span style="color:red">' + users[data.from]
						+ '</span> 对 <span style="color:red">所有人</span> 说'
						+ ntime + '<br />' + data.msg + '<br />';
			} else {
				txt = '<span style="color:red">' + users[data.from]
						+ '</span> 对你说' + ntime + '<br />' + data.msg
						+ '<br />';
			}
			sysinfo(txt);
		} else if (data.type == 'sys') {
			var info = data.msg.split(':');
			if (info[0] == 'login') {
				users[info[1]] = info[2];
			} else if (info[0] == 'logout') {
				delete users[info[1]];
			}
			init_userlist();
		}
	}

}
/**
 * 初始化已有用户
 * 
 * @return
 */
function init_userlist() {
	var i;
	$('#pleft').html('');
	for (i in users) {
		$('#pleft').append(
				'<div><img src="/static/images/lightbulb.png" width="16" height="16" />'
						+ users[i] + ' <a href="/page/user/?uid=' + i
						+ '"  target="_blank">个人主页</a></div>');
	}
	$('#users').html('<option value="0">所有人</option>');
	for (i in users) {
		if (i == uid)
			continue;
		$('#users').append(
				'<option value="' + i + '">' + users[i] + '</option>');
	}
}
function sendmsg() {
	var input = document.getElementById('msgcontent');
	var msg = input.value;
	if (jQuery.trim(msg) == '')
		return false;
	var now = new Date;
	var ntime = now.getHours() + ':' + now.getMinutes() + ':'
			+ now.getSeconds();
	var to = $('#users').val();
	var txt;
	if (to == '0') {
		socket.send('/sendall ' + msg);
		txt = '你对 <span style="color:red">所有人</span> 说' + ntime + '<br />'
				+ HTMLEnCode(msg) + '<br />';
	} else {
		socket.send('/sendto ' + to + ' ' + msg);
		txt = '你对 <span style="color:red"> ' + users[to] + '</span> 说' + ntime
				+ '<br />' + HTMLEnCode(msg) + '<br />';
	}
	sysinfo(txt);
	input.value = '';
}
function HTMLEnCode(str) {
	var s = "";
	if (str.length == 0)
		return "";
	s = str.replace(/&/g, "&gt;");
	s = s.replace(/</g, "&lt;");
	s = s.replace(/>/g, "&gt;");
	s = s.replace(/    /g, "&nbsp;");
	s = s.replace(/\'/g, "&#39;");
	s = s.replace(/\"/g, "&quot;");
	s = s.replace(/\n/g, "<br>");
	return s;
}