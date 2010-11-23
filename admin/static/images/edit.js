// JavaScript Document

function WYSIWYD(textarea) {
	if (WYSIWYD.Browsercheck()) {
		this.config = new WYSIWYD.Config();
		this._htmlArea = null;
		this._textArea = textarea;
		this._editMode = "wysiwyg";
		this._timerToolbar = null;
	}
};
WYSIWYD.prototype.init = function () {
	var editor = this;
	var textarea = this._textArea;
	if (typeof textarea == "string") {
		this._textArea = textarea = WYSIWYD.getElementById("textarea", textarea);
	}
	this._ta_size = {
		w: textarea.offsetWidth,
		h: textarea.offsetHeight
	};
	textarea.style.display = "none";
	var htmlarea = document.createElement("div");
	htmlarea.className = "htmlarea";
	htmlarea.style.width   = "560px";
	this._htmlArea = htmlarea;
	textarea.parentNode.insertBefore(htmlarea, textarea);
	if (textarea.form) {
		var f = textarea.form;
		if (typeof f.onsubmit == "function") {
			var funcref = f.onsubmit;
			if (typeof f.__msh_prevOnSubmit == "undefined") {
				f.__msh_prevOnSubmit = [];
			}
			f.__msh_prevOnSubmit.push(funcref);
		}
		f.onsubmit = function() {
			editor._textArea.value = editor.getHTML();
			var a = this.__msh_prevOnSubmit;
			if (typeof a != "undefined") {
				for (var i in a) {
					a[i]();
				}
			}
		};
	}
	window.onunload = function() {
		editor._textArea.value = editor.getHTML();
	};
	this._createToolbar();
	var iframe = document.createElement("iframe");
	htmlarea.appendChild(iframe);
	this._iframe = iframe;
	if (!WYSIWYD.is_ie) {
		iframe.style.borderWidth = "0px";
	}
	var height = this._ta_size.h + "px";
	height = parseInt(height);
	var width = this._ta_size.w + "px";
	width = parseInt(width);
	if (!WYSIWYD.is_ie) {
		height -= 2;
		width -= 2;
	}
	iframe.style.width   = width + "px";
	iframe.style.height  = height + "px";
	textarea.style.width = iframe.style.width;
 	textarea.style.height= iframe.style.height;
	function initIframe() {
		var doc = editor._iframe.contentWindow.document;
		if (!doc) {
			if (WYSIWYD.is_gecko) {
				setTimeout(initIframe, 100);
				return false;
			} else {
				alert("ERROR: IFRAME can't be initialized.");
			}
		}
		if (WYSIWYD.is_gecko) {
			doc.designMode = "on";
		}
		editor._doc = doc;
			doc.open();
			var html = "<html>\n";
			html += "<head>\n";
			if (editor.config.baseURL)
				html += '<base href="' + editor.config.baseURL + '" />';
			html += "<style> html,body {border:0px;font-family:Verdana;font-size:12px;margin:2;}img {border:0;}</style>\n";
			html += "</head>\n";
			html += "<body>\n";
			html += editor._textArea.value;
			html += "</body>\n";
			html += "</html>";
			doc.write(html);
			doc.close();
		if (WYSIWYD.is_ie) {
			doc.body.contentEditable = true;
		}
		//editor.focusEditor();
		WYSIWYD._addEvents
			(doc, ["keydown", "keypress", "mousedown", "mouseup", "drag"],
			 function (event) {
				 return editor._editorEvent(WYSIWYD.is_ie ? editor._iframe.contentWindow.event : event);
			 });

		setTimeout(function() {
			editor.updateToolbar();
		}, 250);
	};
	setTimeout(initIframe, 100);
};
WYSIWYD.prototype._createToolbar = function () {
	var editor = this;
	var toolbar = document.createElement("div");
	this._toolbar = toolbar;
	toolbar.className = "toolbar";
	toolbar.unselectable = "1";
	var tb_row = null;
	var tb_objects = new Object();
	this._toolbarObjects = tb_objects;
	function newLine() {
		var table = document.createElement("table");
		table.border = "0px";
		table.cellSpacing = "0px";
		table.cellPadding = "0px";
		toolbar.appendChild(table);
		var tb_body = document.createElement("tbody");
		table.appendChild(tb_body);
		tb_row = document.createElement("tr");
		tb_body.appendChild(tb_row);
	};
	newLine();
	function setButtonStatus(id, newval) {
		var oldval = this[id];
		var el = this.element;
		if (oldval != newval) {
			switch (id) {
			    case "enabled":
				if (newval) {
					WYSIWYD._removeClass(el, "buttonDisabled");
					el.disabled = false;
				} else {
					WYSIWYD._addClass(el, "buttonDisabled");
					el.disabled = true;
				}
				break;
			    case "active":
				if (newval) {
					WYSIWYD._addClass(el, "buttonPressed");
				} else {
					WYSIWYD._removeClass(el, "buttonPressed");
				}
				break;
			}
			this[id] = newval;
		}
	};
	function createSelect(txt) {
		var options = null;
		var el = null;
		var cmd = null;
		var context = null;
		options = editor.config[txt];
		cmd = txt;
		if (options) {
			el = document.createElement("select");
			var obj = {
				name	: txt,
				element : el,
				enabled : true,
				text	: false,
				cmd		: cmd,
				state	: setButtonStatus,
				context : context
			};
			tb_objects[txt] = obj;
			for (var i in options) {
				var op = document.createElement("option");
				op.appendChild(document.createTextNode(i));
				op.value = options[i];
				el.appendChild(op);
			}
			WYSIWYD._addEvent(el, "change", function () {
				editor._comboSelected(el, txt);
			});
		}
		return el;
	};
	function createButton(txt) {
		var el = null;
		var btn = null;
		switch (txt) {
		    case "separator":
			el = document.createElement("div");
			el.className = "separator";
			break;
		    case "space":
			el = document.createElement("div");
			el.className = "space";
			break;
		    case "linebreak":
			newLine();
			return false;
		    default:
			btn = editor.config.btnList[txt];
		}
		if (!el && btn) {
			el = document.createElement("div");
			el.title = btn[0];
			el.className = "button";
			var obj = {
				name	: txt,
				element : el,
				enabled : true,
				active	: false,
				text	: btn[2],
				cmd		: btn[3],
				state	: setButtonStatus,
				context : btn[4] || null
			};
			tb_objects[txt] = obj;
			WYSIWYD._addEvent(el, "mouseover", function () {
				if (obj.enabled) {
					WYSIWYD._addClass(el, "buttonHover");
				}
			});
			WYSIWYD._addEvent(el, "mouseout", function () {
				if (obj.enabled) with (WYSIWYD) {
					_removeClass(el, "buttonHover");
					_removeClass(el, "buttonActive");
					(obj.active) && _addClass(el, "buttonPressed");
				}
			});
			WYSIWYD._addEvent(el, "mousedown", function (ev) {
				if (obj.enabled) with (WYSIWYD) {
					_addClass(el, "buttonActive");
					_removeClass(el, "buttonPressed");
					_stopEvent(is_ie ? window.event : ev);
				}
			});
			WYSIWYD._addEvent(el, "click", function (ev) {
				if (obj.enabled) with (WYSIWYD) {
					_removeClass(el, "buttonActive");
					_removeClass(el, "buttonHover");
					obj.cmd(editor, obj.name, obj);
					_stopEvent(is_ie ? window.event : ev);
				}
			});
			var img = document.createElement("img");
			img.src = btn[1];
			img.style.width = "20px";
			img.style.height = "20px";
			el.appendChild(img);
		} else if (!el) {
			el = createSelect(txt);
		}
		if (el) {
			var tb_cell = document.createElement("td");
			tb_row.appendChild(tb_cell);
			tb_cell.appendChild(el);
		} else {
			alert("FIXME: Unknown toolbar item: " + txt);
		}
		return el;
	};

	var first = true;
	for (var i in this.config.toolbar) {
		if (!first) {
			createButton("linebreak");
		} else {
			first = false;
		}
		var group = this.config.toolbar[i];
		for (var j in group) {
			var code = group[j];
			createButton(code);
		}
	}
	this._htmlArea.appendChild(toolbar);
};
WYSIWYD.prototype.setMode = function(mode) {
	if (typeof mode == "undefined") {
		mode = ((this._editMode == "textmode") ? "wysiwyg" : "textmode");
	}
	switch (mode) {
	    case "textmode":
		this._textArea.value = this.getHTML();
		this._iframe.style.display = "none";
		this._textArea.style.display = "block";
		break;
	    case "wysiwyg":
		if (WYSIWYD.is_gecko) {
			try {
				this._doc.designMode = "off";
			} catch(e) {};
		}
		this._doc.body.innerHTML = this.getHTML();

		this._iframe.style.display = "block";
		this._textArea.style.display = "none";
		if (WYSIWYD.is_gecko) {
			try {
				this._doc.designMode = "on";
			} catch(e) {};
		}
		break;
	    default:
		alert("Mode <" + mode + "> not defined!");
		return false;
	}
	this._editMode = mode;
	this.focusEditor();
};

WYSIWYD.prototype.forceRedraw = function() {
	this._doc.body.style.visibility = "hidden";
	this._doc.body.style.visibility = "visible";
};
WYSIWYD.prototype.focusEditor = function() {
	switch (this._editMode) {
	    case "wysiwyg" : this._iframe.contentWindow.focus(); break;
	    case "textmode": this._textArea.focus(); break;
	    default	   : alert("ERROR: mode " + this._editMode + " is not defined");
	}
	return this._doc;
};
WYSIWYD.prototype.updateToolbar = function(noStatus) {
	var doc = this._doc;
	var text = (this._editMode == "textmode");
	var ancestors = null;
	if (!text) {
		ancestors = this.getAllAncestors();
	}
	for (var i in this._toolbarObjects) {
		var btn = this._toolbarObjects[i];
		var cmd = i;
		var inContext = true;
		if (btn.context && !text) {
			inContext = false;
			var context = btn.context;
			var attrs = [];
			if (/(.*)\[(.*?)\]/.test(context)) {
				context = RegExp.$1;
				attrs = RegExp.$2.split(",");
			}
			context = context.toLowerCase();
			var match = (context == "*");
			for (var k in ancestors) {
				if (!ancestors[k]) {
					continue;
				}
				if (match || (ancestors[k].tagName.toLowerCase() == context)) {
					inContext = true;
					for (var ka in attrs) {
						if (!eval("ancestors[k]." + attrs[ka])) {
							inContext = false;
							break;
						}
					}
					if (inContext) {
						break;
					}
				}
			}
		}
		btn.state("enabled", (!text || btn.text) && inContext);
		if (typeof cmd == "function") {
			continue;
		}
		switch (cmd) {
		    case "fontname":
		    case "fontsize":
		    case "formatblock":
			if (!text) try {
				var value = ("" + doc.queryCommandValue(cmd)).toLowerCase();
				if (!value) {
					break;
				}
				var options = this.config[cmd];
				var k = 0;
				for (var j in options) {
					if ((j.toLowerCase() == value) ||
					    (options[j].substr(0, value.length).toLowerCase() == value)) {
						btn.element.selectedIndex = k;
						break;
					}
					++k;
				}
			} catch(e) {};
			break;
		    case "textindicator":
			if (!text) {
				try {with (btn.element.style) {
					backgroundColor = WYSIWYD._makeColor(
						doc.queryCommandValue(WYSIWYD.is_ie ? "backcolor" : "hilitecolor"));
					if (/transparent/i.test(backgroundColor)) {
						backgroundColor = WYSIWYD._makeColor(doc.queryCommandValue("backcolor"));
					}
					color = WYSIWYD._makeColor(doc.queryCommandValue("forecolor"));
					fontFamily = doc.queryCommandValue("fontname");
					fontWeight = doc.queryCommandState("bold") ? "bold" : "normal";
					fontStyle = doc.queryCommandState("italic") ? "italic" : "normal";
				}} catch (e) {}
			}
			break;
		    case "htmlmode": btn.state("active", text); break;
		    case "lefttoright":
		    case "righttoleft":
			var el = this.getParentElement();
			while (el && !WYSIWYD.isBlockElement(el))
				el = el.parentNode;
			if (el)
				btn.state("active", (el.style.direction == ((cmd == "righttoleft") ? "rtl" : "ltr")));
			break;
		    default:
			try {
				btn.state("active", (!text && doc.queryCommandState(cmd)));
			} catch (e) {}
		}
	}
};
WYSIWYD.prototype.insertNodeAtSelection = function(toBeInserted) {
	if (!WYSIWYD.is_ie) {
		var sel = this._getSelection();
		var range = this._createRange(sel);
		sel.removeAllRanges();
		range.deleteContents();
		var node = range.startContainer;
		var pos = range.startOffset;
		switch (node.nodeType) {
		    case 3:
			if (toBeInserted.nodeType == 3) {
				node.insertData(pos, toBeInserted.data);
				range = this._createRange();
				range.setEnd(node, pos + toBeInserted.length);
				range.setStart(node, pos + toBeInserted.length);
				sel.addRange(range);
			} else {
				node = node.splitText(pos);
				var selnode = toBeInserted;
				if (toBeInserted.nodeType == 11) {
					selnode = selnode.firstChild;
				}
				node.parentNode.insertBefore(toBeInserted, node);
				this.selectNodeContents(selnode);
				this.updateToolbar();
			}
			break;
		    case 1:
			var selnode = toBeInserted;
			if (toBeInserted.nodeType == 11) {
				selnode = selnode.firstChild;
			}
			node.insertBefore(toBeInserted, node.childNodes[pos]);
			this.selectNodeContents(selnode);
			this.updateToolbar();
			break;
		}
	} else {
		return null;
	}
};
WYSIWYD.prototype.getParentElement = function() {
	var sel = this._getSelection();
	var range = this._createRange(sel);
	if (WYSIWYD.is_ie) {
		switch (sel.type) {
		    case "Text":
		    case "None":
			return range.parentElement();
		    case "Control":
			return range.item(0);
		    default:
			return this._doc.body;
		}
	} else try {
		var p = range.commonAncestorContainer;
		if (!range.collapsed && range.startContainer == range.endContainer &&
		    range.startOffset - range.endOffset <= 1 && range.startContainer.hasChildNodes())
			p = range.startContainer.childNodes[range.startOffset];
		while (p.nodeType == 3) {
			p = p.parentNode;
		}
		return p;
	} catch (e) {
		return null;
	}
};
WYSIWYD.prototype.getAllAncestors = function() {
	var p = this.getParentElement();
	var a = [];
	while (p && (p.nodeType == 1) && (p.tagName.toLowerCase() != 'body')) {
		a.push(p);
		p = p.parentNode;
	}
	a.push(this._doc.body);
	return a;
};
WYSIWYD.prototype.selectNodeContents = function(node, pos) {
	this.focusEditor();
	this.forceRedraw();
	var range;
	var collapsed = (typeof pos != "undefined");
	if (WYSIWYD.is_ie) {
		range = this._doc.body.createTextRange();
		range.moveToElementText(node);
		(collapsed) && range.collapse(pos);
		range.select();
	} else {
		var sel = this._getSelection();
		range = this._doc.createRange();
		range.selectNodeContents(node);
		(collapsed) && range.collapse(pos);
		sel.removeAllRanges();
		sel.addRange(range);
	}
};
WYSIWYD.prototype._comboSelected = function(el, txt) {
	this.focusEditor();
	var value = el.options[el.selectedIndex].value;
	switch (txt) {
	    case "fontname":
	    case "fontsize": this.execCommand(txt, false, value); break;
	    case "formatblock":
		(WYSIWYD.is_ie) && (value = "<" + value + ">");
		this.execCommand(txt, false, value);
		break;
	}
};
WYSIWYD.prototype.execCommand = function(cmdID, UI, param) {
	var editor = this;
	this.focusEditor();
	cmdID = cmdID.toLowerCase();
	switch (cmdID) {
	    case "htmlmode" : this.setMode(); break;
	    case "hilitecolor":
		(WYSIWYD.is_ie) && (cmdID = "backcolor");
	    case "forecolor":
		this._popupDialog(bbsurl + "/wysiwyg.php?type=color", function(color) {
			if (color) {
				editor._doc.execCommand(cmdID, false, "#" + color);
			}
		}, WYSIWYD._colorToRgb(this._doc.queryCommandValue(cmdID)));
		break;
	    case "undo":
	    case "redo":
			this._doc.execCommand(cmdID, UI, param); break;
	    case "insertimage": insertImage(); break;
	    case "cut":
	    case "copy":
	    case "paste":
			try{this._doc.execCommand(cmdID, UI, param);}
			catch(e){}
			break;
	    case "lefttoright":
	    case "righttoleft":
		var dir = (cmdID == "righttoleft") ? "rtl" : "ltr";
		var el = this.getParentElement();
		while (el && !WYSIWYD.isBlockElement(el))
			el = el.parentNode;
		if (el) {
			if (el.style.direction == dir)
				el.style.direction = "";
			else
				el.style.direction = dir;
		}
		break;
	    default: this._doc.execCommand(cmdID, UI, param);
	}
	this.updateToolbar();
	return false;
};
WYSIWYD.prototype._editorEvent = function(ev) {
	var editor = this;
	var keyEvent = (WYSIWYD.is_ie && ev.type == "keydown") || (ev.type == "keypress");
	if (editor._timerToolbar) {
		clearTimeout(editor._timerToolbar);
	}
	editor._timerToolbar = setTimeout(function() {
		editor.updateToolbar();
		editor._timerToolbar = null;
	}, 50);
};
WYSIWYD.prototype.getHTML = function() {
	switch (this._editMode) {
	    case "wysiwyg"  :
			return WYSIWYD.getHTML(this._doc.body, false, this);
	    case "textmode" : return this._textArea.value;
	    default	    : alert("Mode <" + mode + "> not defined!");
	}
	return false;
};

WYSIWYD.agt		= navigator.userAgent.toLowerCase();
WYSIWYD.is_ie	= ((WYSIWYD.agt.indexOf("msie") != -1) && (WYSIWYD.agt.indexOf("opera") == -1));
WYSIWYD.is_gecko= (navigator.product == "Gecko");

WYSIWYD.Browsercheck = function() {
	if (WYSIWYD.is_gecko) {
		if (navigator.productSub < 20021201) {
			alert("You need at least Mozilla-1.3 Alpha.");
			return false;
		}
		if (navigator.productSub < 20030210) {
			alert("Mozilla < 1.3 Beta is not supported!");
			return false;
		}
	}
	return WYSIWYD.is_gecko || WYSIWYD.is_ie;
};
WYSIWYD.prototype._getSelection = function() {
	if (WYSIWYD.is_ie) {
		return this._doc.selection;
	} else {
		return this._iframe.contentWindow.getSelection();
	}
};
WYSIWYD.prototype._createRange = function(sel) {
	if (WYSIWYD.is_ie) {
		return sel.createRange();
	} else {
		this.focusEditor();
		if (typeof sel != "undefined") {
			try {
				return sel.getRangeAt(0);
			} catch(e) {
				return this._doc.createRange();
			}
		} else {
			return this._doc.createRange();
		}
	}
};
WYSIWYD._addEvent = function(el, evname, func) {
	if (WYSIWYD.is_ie) {
		el.attachEvent("on" + evname, func);
	} else {
		el.addEventListener(evname, func, true);
	}
};
WYSIWYD._addEvents = function(el, evs, func) {
	for (var i in evs) {
		WYSIWYD._addEvent(el, evs[i], func);
	}
};
WYSIWYD._removeEvent = function(el, evname, func) {
	if (WYSIWYD.is_ie) {
		el.detachEvent("on" + evname, func);
	} else {
		el.removeEventListener(evname, func, true);
	}
};
WYSIWYD._stopEvent = function(ev) {
	if (WYSIWYD.is_ie) {
		ev.cancelBubble = true;
		ev.returnValue = false;
	} else {
		ev.preventDefault();
		ev.stopPropagation();
	}
};
WYSIWYD._removeClass = function(el, className) {
	if (!(el && el.className)) {
		return;
	}
	var cls = el.className.split(" ");
	var ar = new Array();
	for (var i = cls.length; i > 0;) {
		if (cls[--i] != className) {
			ar[ar.length] = cls[i];
		}
	}
	el.className = ar.join(" ");
};
WYSIWYD._addClass = function(el, className) {
	WYSIWYD._removeClass(el, className);
	el.className += " " + className;
};

WYSIWYD.isBlockElement = function(el) {
	var blockTags = " body form textarea fieldset ul ol dl li div " +
		"p h1 h2 h3 h4 h5 h6 quote pre table thead " +
		"tbody tfoot tr td iframe address ";
	return (blockTags.indexOf(" " + el.tagName.toLowerCase() + " ") != -1);
};
WYSIWYD.needsClosingTag = function(el) {
	var closingTags = " head script style div span tr td tbody table em strong font a title ";
	return (closingTags.indexOf(" " + el.tagName.toLowerCase() + " ") != -1);
};
WYSIWYD.htmlEncode = function(str) {
	str = str.replace(/&/ig, "&amp;");
	str = str.replace(/</ig, "&lt;");
	str = str.replace(/>/ig, "&gt;");
	str = str.replace(/\x22/ig, "&quot;");
	return str;
};
WYSIWYD.getHTML = function(root, outputRoot, editor) {
	var html = "";
	switch (root.nodeType) {
	    case 1:
	    case 11:
		var closed;
		var i;
		var root_tag = (root.nodeType == 1) ? root.tagName.toLowerCase() : '';
		if (WYSIWYD.is_ie && root_tag == "head") {
			if (outputRoot)
				html += "<head>";
			var save_multiline = RegExp.multiline;
			RegExp.multiline = true;
			var txt = root.innerHTML.replace(/(<\/|<)\s*([^ \t\n>]+)/ig, function(str, p1, p2) {
				return p1 + p2.toLowerCase();
			});
			RegExp.multiline = save_multiline;
			html += txt;
			if (outputRoot)
				html += "</head>";
			break;
		} else if (outputRoot) {
			closed = (!(root.hasChildNodes() || WYSIWYD.needsClosingTag(root)));
			html = "<" + root.tagName.toLowerCase();
			var attrs = root.attributes;
			for (i = 0; i < attrs.length; ++i) {
				var a = attrs.item(i);
				if (!a.specified) {
					continue;
				}
				var name = a.nodeName.toLowerCase();
				if (/_moz|contenteditable|_msh/.test(name)) {
					continue;
				}
				var value;
				if (name != "style") {
					if (typeof root[a.nodeName] != "undefined" && name != "href" && name != "src") {
						value = root[a.nodeName];
					} else {
						value = a.nodeValue;
						if (WYSIWYD.is_ie && (name == "href" || name == "src")) {
							value = editor.stripBaseURL(value);
						}
					}
				} else {
					value = root.style.cssText;
				}
				if (/(_moz|^$)/.test(value)) {
					continue;
				}
				html += " " + name + '="' + value + '"';
			}
			html += closed ? " />" : ">";
		}
		for (i = root.firstChild; i; i = i.nextSibling) {
			html += WYSIWYD.getHTML(i, true, editor);
		}
		if (outputRoot && !closed) {
			html += "</" + root.tagName.toLowerCase() + ">";
		}
		break;
	    case 3:
		if ( !root.previousSibling && !root.nextSibling && root.data.match(/^\s*$/i) ) html = '&nbsp;';
		else html = WYSIWYD.htmlEncode(root.data);
		break;
	    case 8:
		html = "<!--" + root.data + "-->";
		break;
	}
	return html;
};
WYSIWYD.prototype.stripBaseURL = function(string) {
	var baseurl = this.config.baseURL;
	baseurl = baseurl.replace(/[^\/]+$/, '');
	var basere = new RegExp(baseurl);
	string = string.replace(basere, "");
	baseurl = baseurl.replace(/^(https?:\/\/[^\/]+)(.*)$/, '$1');
	basere = new RegExp(baseurl);
	return string.replace(basere, "");
};
String.prototype.trim = function() {
	a = this.replace(/^\s+/, '');
	return a.replace(/\s+$/, '');
};
WYSIWYD._makeColor = function(v) {
	if (typeof v != "number") {
		return v;
	}
	var r = v & 0xFF;
	var g = (v >> 8) & 0xFF;
	var b = (v >> 16) & 0xFF;
	return "rgb(" + r + "," + g + "," + b + ")";
};
WYSIWYD._colorToRgb = function(v) {
	if (!v)
		return '';
	function hex(d) {
		return (d < 16) ? ("0" + d.toString(16)) : d.toString(16);
	};
	if (typeof v == "number") {
		var r = v & 0xFF;
		var g = (v >> 8) & 0xFF;
		var b = (v >> 16) & 0xFF;
		return "#" + hex(r) + hex(g) + hex(b);
	}
	if (v.substr(0, 3) == "rgb") {
		var re = /rgb\s*\(\s*([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\s*\)/;
		if (v.match(re)) {
			var r = parseInt(RegExp.$1);
			var g = parseInt(RegExp.$2);
			var b = parseInt(RegExp.$3);
			return "#" + hex(r) + hex(g) + hex(b);
		}
		return null;
	}
	if (v.substr(0, 1) == "#") {
		return v;
	}
	return null;
};
WYSIWYD.prototype._popupDialog = function(url, action, init) {
	Dialog(url, action, init);
};
WYSIWYD.getElementById = function(tag, id) {
	var el, i, objs = document.getElementsByTagName(tag);
	for (i = objs.length; --i >= 0 && (el = objs[i]);)
		if (el.id == id)
			return el;
	return null;
};
WYSIWYD.prototype.insertHTML = function(html) {
	var sel = this._getSelection();
	var range = this._createRange(sel);
	if (WYSIWYD.is_ie) {
		range.pasteHTML(html);
	} else {
		var fragment = this._doc.createDocumentFragment();
		var div = this._doc.createElement("div");
		div.innerHTML = html;
		while (div.firstChild) {
			fragment.appendChild(div.firstChild);
		}
		var node = this.insertNodeAtSelection(fragment);
	}
};
function Dialog(url, action, init) {
	if (typeof init == "undefined") {
		init = window;
	}
	Dialog._geckoOpenModal(url, action, init);
};
Dialog._parentEvent = function(ev) {
	if (Dialog._modal && !Dialog._modal.closed) {
		Dialog._modal.focus();
		WYSIWYD._stopEvent(ev);
	}
};
Dialog._return = null;
Dialog._modal = null;
Dialog._arguments = null;

Dialog._geckoOpenModal = function(url, action, init) {
	var dlg = window.open(url, "hadialog",
			      "toolbar=no,menubar=no,personalbar=no,top=200,left=300,width=10,height=10," +
			      "scrollbars=no,resizable=yes");
	Dialog._modal = dlg;
	Dialog._arguments = init;

	function capwin(w) {
		WYSIWYD._addEvent(w, "click", Dialog._parentEvent);
		WYSIWYD._addEvent(w, "mousedown", Dialog._parentEvent);
		WYSIWYD._addEvent(w, "focus", Dialog._parentEvent);
	};
	function relwin(w) {
		WYSIWYD._removeEvent(w, "click", Dialog._parentEvent);
		WYSIWYD._removeEvent(w, "mousedown", Dialog._parentEvent);
		WYSIWYD._removeEvent(w, "focus", Dialog._parentEvent);
	};
	capwin(window);
	for (var i = 0; i < window.frames.length; capwin(window.frames[i++]));
	Dialog._return = function (val) {
		if (val && action) {
			action(val);
		}
		relwin(window);
		for (var i = 0; i < window.frames.length; relwin(window.frames[i++]));
		Dialog._modal = null;
	};
};
function insertImage() {
	var outparam = null;
	image = editor.getParentElement();
	if (image && !/^img$/i.test(image.tagName)) image = null;
	if (image) outparam = {
		f_url    : WYSIWYD.is_ie ? editor.stripBaseURL(image.src) : image.getAttribute("src"),
		f_alt    : image.alt,
		f_border : image.border,
		f_align  : image.align,
		f_vert   : image.vspace,
		f_horiz  : image.hspace
	};
	editor._popupDialog(bbsurl + "/wysiwyg.php?type=image", function(param) {
		if (!param) {
			return false;
		}
		var img = image;
		if (!img) {
			var sel = editor._getSelection();
			var range = editor._createRange(sel);
			editor._doc.execCommand("insertimage", false, param.f_url);
			if (WYSIWYD.is_ie) {
				img = range.parentElement();
				if (img.tagName.toLowerCase() != "img") {
					img = img.previousSibling;
				}
			} else {
				img = range.startContainer.previousSibling;
			}
		} else {
			img.src = param.f_url;
		}
		for (field in param) {
			var value = param[field];
			switch (field) {
			    case "f_alt"    : img.alt	 = value; break;
			    case "f_border" : img.border = parseInt(value || "0"); break;
			    case "f_align"  : img.align	 = value; break;
			    case "f_vert"   : img.vspace = parseInt(value || "0"); break;
			    case "f_horiz"  : img.hspace = parseInt(value || "0"); break;
			}
		}
	}, outparam);
}
function insertTable() {
	var sel = editor._getSelection();
	var range = editor._createRange(sel);
	editor._popupDialog(bbsurl + "/wysiwyg.php?type=table", function(param) {
		if (!param) {
			return false;
		}
		var doc = editor._doc;
		var table = doc.createElement("table");
		for (var field in param) {
			var value = param[field];
			if (!value) {
				continue;
			}
			switch (field) {
			    case "f_width"   : table.style.width = value + param["f_unit"]; break;
			    case "f_align"   : table.align = value; break;
				case "f_bgcolor" : table.bgcolor = value; break;
				case "f_bdcolor" : table.bordercolor = value; break;
			    case "f_border"  : table.border	 = parseInt(value); break;
			    case "f_spacing" : table.cellspacing = parseInt(value); break;
			    case "f_padding" : table.cellpadding = parseInt(value); break;
			}
		}
		var tbody = doc.createElement("tbody");
		table.appendChild(tbody);
		for (var i = 0; i < param["f_rows"]; ++i) {
			var tr = doc.createElement("tr");
			tbody.appendChild(tr);
			for (var j = 0; j < param["f_cols"]; ++j) {
				var td = doc.createElement("td");
				tr.appendChild(td);
				(WYSIWYD.is_gecko) && td.appendChild(doc.createElement("br"));
			}
		}
		if (WYSIWYD.is_ie) {
			range.pasteHTML(table.outerHTML);
		} else {
			editor.insertNodeAtSelection(table);
		}
		return true;
	}, null);
}
function rming() {
	editor.focusEditor();
	sm=prompt('URL:',"http://");
	if(sm!=null) {
		sm="[rm]"+sm+"[/rm]";
		editor.insertHTML(sm);
	}
}
function wmv() {
	editor.focusEditor();
	sm=prompt('URL:',"http://");
	if(sm!=null) {
		sm="[wmv]"+sm+"[/wmv]";
		editor.insertHTML(sm);
	}
}
function setswf() {
	editor.focusEditor();
	sm2=prompt('width,height',"400,300");
	if (sm2!=null) {
		sm3=prompt('URL:',"http://");
		if (sm3!=null) {
			if (sm2=="") {
				sm="[flash=400,300]"+sm3+"[/flash]";
			} else {
				sm="[flash="+sm2+"]"+sm3+"[/flash]";
			}
		}
		editor.insertHTML(sm);
	}
}
function quote() {
	editor.focusEditor();
	sm="[quote] [/quote]";
	editor.insertHTML(sm);
}
function code() {
	editor.focusEditor();
	sm="[code] [/code]";
	editor.insertHTML(sm);
}
function br() {
	editor.focusEditor();
	sm="<br />";
	editor.insertHTML(sm);
}
function saletable() {
	editor.focusEditor();
	editor._popupDialog(bbsurl + "/wysiwyg.php?type=sale", function(param) {
		editor.insertHTML(param);
		return true;
	}, null);
}
function softtable(){
	editor.focusEditor();
	editor._popupDialog(bbsurl + "/wysiwyg.php?type=download", function(param) {
		editor.insertHTML(param);
		return true;
	}, null);
}
function addattach(aid) {
	editor.focusEditor();
	sm=' [attachment='+aid+'] ';
	editor.insertHTML(sm);
}