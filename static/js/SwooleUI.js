/**
 * 得到ID为id的DOM对象
 * 
 * @param id
 * @return DOM object
 */
function obj(id) {
	return document.getElementById(id);
}
/**
 * 得到name为name的DOM对象数组
 * 
 * @param name
 * @return DOM objects
 */
function objs(name) {
	return document.getElementsByTagName(name);
}
function addEvent(obj, evType, fn) {
	if (obj.addEventListener) {
		obj.addEventListener(evType, fn, false);
		return true;
	} else if (obj.attachEvent) {
		var r = obj.attachEvent("on" + evType, fn);
		return r;
	} else {
		return false;
	}
}
function getOffset(evt) {
	var target = evt.target;
	if (target.offsetLeft == undefined) {
		target = target.parentNode;
	}
	var pageCoord = getPageCoord(target);
	var eventCoord = {
		x :window.pageXOffset + evt.clientX,
		y :window.pageYOffset + evt.clientY
	};
	var offset = {
		offsetX :eventCoord.x - pageCoord.x,
		offsetY :eventCoord.y - pageCoord.y
	};
	return offset;
}
function getPageCoord(element) {
	var coord = {
		x :0,
		y :0
	};
	while (element) {
		coord.x += element.offsetLeft;
		coord.y += element.offsetTop;
		element = element.offsetParent;
	}
	return coord;
}
function imageAuto(nID, nMaxWidth, nMaxHeight) {
	var objParentID = obj(nID);
	var objImg = objParentID.getElementsByTagName("img");
	for (i = 0; i < objImg.length; i++) {
		img_resize(objImg[i], nMaxWidth, nMaxHeight);
	}
}
function img_resize(imgObj, maxWidth, maxHeight) {
	var w_h = imgObj.width / imgObj.height;
	var h_w = imgObj.height / imgObj.width;

	if (w_h < h_w) {
		if (imgObj.height > maxHeight)
			imgObj.height = maxHeight;
	} else {
		if (imgObj.width > maxWidth)
			imgObj.width = maxWidth;
	}
}
function img_reload(img) {
	var r = Math.random();
	var img_o = obj(img);
	img_o.src = img_o.src + '?' + r;
}
swoole = function() {
};
swoole.prototype.version = '1.01';

// 获得事件Event对象，用于兼容IE和FireFox
swoole.getEvent = function(event) {
	return window.event || arguments.callee.caller.arguments[0];
};
swoole.Dialog = function(title, msg, w, h, modal, buttons, moveable, fixed) {
	if (typeof (modal) == 'undefined')
		var modal = true;
	if (!moveable)
		moveable = true;
	if (!fixed)
		fixed = false;

	var browers_toolbar = 100;

	var iWidth = document.documentElement.clientWidth;
	var iHeight = document.documentElement.clientHeight + screen.height;

	var bodyObj = document.createElement("div");
	var dialogObj = document.createElement("div");

	this.show = function() {
		if (modal)
			document.body.appendChild(bodyObj);
		document.body.appendChild(dialogObj);
	};

	this.hide = function() {
		if (modal)
			bodyObj.style.display = 'none';
		dialogObj.style.display = 'none';
	}

	this.display = function() {
		if (modal)
			bodyObj.style.display = 'block';
		dialogObj.style.display = 'block';
	}

	this.close = function() {
		if (modal)
			document.body.removeChild(bodyObj);
		document.body.removeChild(dialogObj);
	};

	if (modal) {
		bodyObj.className = 'Dialog_bodybg';
		if (document.body.clientHeight < screen.height)
			var bg_h = screen.height;
		else
			var bg_h = document.body.clientHeight;

		if (document.all)
			bodyObj.style.cssText = "width:" + iWidth + "px;height:"
					+ (bg_h + 50) + "px;";
		else
			bodyObj.style.cssText = "width:100%;height:" + (bg_h + 50) + "px;";
	}
	var dialog_top = 0;
	
	if (fixed) {
		dialogObj.className = 'Dialog_fixed';
		dialog_top = ((window.innerHeight || document.body.offsetHeight)-h)/2;
	} else {
		dialogObj.className = 'Dialog';
		dialog_top = document.documentElement.scrollTop + (window.innerHeight || document.body.offsetHeight) / 2 - h / 2;
	}
	if (dialog_top < 0)
		dialog_top = 0;
	dialogObj.style.top = dialog_top + "px";
	dialogObj.style.left = document.documentElement.scrollLeft
			+ (document.documentElement.clientWidth - w) / 2 + "px";
	dialogObj.style.width = w + 'px';
	var headBar = document.createElement("div");
	var bodyBar = document.createElement("div");
	var footBar = document.createElement("div");

	headBar.className = 'Dialog_head';
	headBar.style.paddingLeft = "10px";

	var moveX = 0;
	var moveY = 0;
	var moveTop = 0;
	var moveLeft = 0;
	var docMouseMoveEvent = document.onmousemove;
	var docMouseUpEvent = document.onmouseup;
	if (moveable)
		headBar.onmousedown = function() {
			var evt = swoole.getEvent();
			moveable = true;
			moveX = evt.clientX;
			moveY = evt.clientY;
			moveTop = parseInt(dialogObj.style.top);
			moveLeft = parseInt(dialogObj.style.left);
			document.onmousemove = function() {
				var evt = swoole.getEvent();
				var x = moveLeft + evt.clientX - moveX;
				var y = moveTop + evt.clientY - moveY;
				if (x > 0 && (x + w < iWidth) && y > 0 && (y + h < iHeight)) {
					dialogObj.style.left = x + "px";
					dialogObj.style.top = y + "px";
				}
			};

			document.onmouseup = function() {
				document.onmousemove = docMouseMoveEvent;
				document.onmouseup = docMouseUpEvent;
				moveable = false;
				moveX = 0;
				moveY = 0;
				moveTop = 0;
				moveLeft = 0;
			};
		}
	var titleDiv = document.createElement('div');
	titleDiv.className = 'Dialog_title';
	titleDiv.innerHTML = title;
	headBar.appendChild(titleDiv);

	var closeBtn = document.createElement('div');
	closeBtn.className = "Dialog_close";
	closeBtn.innerHTML = "<span>关闭</span>";
	closeBtn.onclick = this.close;
	headBar.appendChild(closeBtn);

	this.remove = function(block) {
		if (block == 'close') {
			headBar.removeChild(closeBtn);
		}
		if (block == 'foot') {
			dialogObj.removeChild(footBar);
		}
		if (block == 'head') {
			dialogObj.removeChild(headBar);
		}
		if (block == 'all') {
			headBar.removeChild(closeBtn);
			dialogObj.removeChild(footBar);
			dialogObj.removeChild(headBar);
			dialogObj.className = 'Dialog_none';
			bodyBar.className = 'Dialog_msg_none';
		}
	}

	bodyBar.className = 'Dialog_msg';
	bodyBar.innerHTML = msg;

	footBar.className = 'Dialog_bottom';
	if (typeof (buttons) == 'object') {
		for (i = 0; i < buttons.length; i++) {
			btn = buttons[i];
			btn.addTo(footBar);
		}
	} else if (typeof (buttons) == 'string') {
		footBar.innerHTML = buttons;
	} else {
		var btn = new swoole.Button('确定');
		btn.addTo(footBar);
		btn.click(this.close);
	}
	dialogObj.appendChild(headBar);
	dialogObj.appendChild(bodyBar);
	dialogObj.appendChild(footBar);
};
swoole.Dialog.prototype.type = 'Dialog';
/**
 * 浮动提示
 * 
 * @param msg
 * @param width
 * @param height
 * @param left_to
 * @return
 */
/*
 * swoole.Tip = function(msg,width,height,left_to){ if (typeof (left_to) ==
 * 'undefined') left_to = 5; var event = swoole.getEvent(event); var tip_div =
 * document.createElement('div'); var close = document.createElement('span');
 * 
 * document.get
 * 
 * tip_div.className = 'Tip_div'; tip_div.style.width = width+'px';
 * tip_div.style.height = height+'px';
 * 
 * tip_div.innerHTML = msg; document.body.appendChild(tip_div);
 * 
 * this.init = function(){ }
 * 
 * this.close = function(){ document.removeChild(tip_div); } }
 * swoole.Tip.prototype.type = 'Tip';
 */
swoole.alert = function(msg) {
	var alert_dialog = new swoole.Dialog('提示', msg, 320, 320);
	alert_dialog.show();
};
swoole.confirm = function(msg, yes, no) {
	var btns = [ new swoole.Button('是', yes), new swoole.Button('否', no) ];
	var confirm_dialog = new swoole.Dialog('请选择', msg, 320, 320, true, btns);
	confirm_dialog.show();
};

swoole.Button = function(text, callback) {
	this.m_button = document.createElement('input');
	this.m_button.type = 'button';
	this.m_button.className = 'swoole_button';
	this.m_button.value = text;
	if (callback)
		this.m_button.onclick = callback;
	this.addTo = function(obj) {
		obj.appendChild(this.m_button);
	};
	this.click = function(call) {
		this.m_button.onclick = call;
	};
};
swoole.Button.prototype.type = 'Button';
