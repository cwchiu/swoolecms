var socket = {
	config : {
		ip :"127.0.0.1",
		port :8080,
		flashcontainer :"flashcontainer",
		auto :true
	},

	connect : function() {
		socket.flash.log("begin connect to session server");
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
		socket.flash.send('/setname '+uid+' '+uname);
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
		socket.flash.log("receive from server:"+msg);
		recvmsg(msg);
	}

};
function initChatClient() {
	var so = new SWFObject("/static/swf/socket_bridge.swf", "socketBridge","800", "400", "9", "#ffffff");
	so.addParam("allowscriptaccess", "always");
	so.addVariable("scope", "socket");
	so.write(socket.config.flashcontainer);
}
var ndate = new Date;
function recvmsg(msg)
{
	var txt = '';	
	var ntime = ndate.getHours()+':'+ndate.getMinutes()+':'+ndate.getSeconds();
	if("setname success"==msg) txt = '<div>[系统提示] 登录成功！</div>';
	else
		txt = '<div><p class="friend-id">'+tname+'&nbsp;&nbsp;'+ntime+'</p><p class="the-cont">'+msg+'</p></div>';
	jQuery('#msglist').append(txt);
}
function sendmsg()
{
	var input = document.getElementById('msgcontent');	
	var msg = input.value;
	var ntime = ndate.getHours()+':'+ndate.getMinutes()+':'+ndate.getSeconds();
	if(jQuery.trim(msg)=='') return false;
	socket.send('/sendall '+msg);
	var txt = '<div><p class="my-id">'+uname+'&nbsp;&nbsp;'+ntime+'</p><p class="the-cont">'+msg+'</p></div>';
	jQuery('#msglist').append(txt);
	input.value='';
}