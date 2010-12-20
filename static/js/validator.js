/**
 * 用于表单验证
 * 支持的标签：
 * empty   值为空的时候，提示文字，并使当前表单元素获得焦点
 * equal   值必须等于某个数值
 * noequal 值必须不等于某个数值
 * equalo  值不惜等于某个对象的值
 * ctype   检查值的类型，支持email、tel、english、mobile、nickname几种格式
 */

/**
 * 去除字符串两边的空格
 */
function trim(str) {
	return str.replace(/(^\s*)|(\s*$)/g, "");
}
function ltrim(str) {
	return str.replace(/(^\s*)/g, "");
}
function rtrim(str) {
	return str.replace(/(\s*$)/g, "");
}
function isMobile(mobile) {
	return (/^(?:13\d|15\d|18\d)-?\d{5}(\d{3}|\*{3})$/).test(trim(mobile));
}
function isEmail(strValue) {
	return (/^[\w-\.]+@[\w-]+(\.(\w)+)*(\.(\w){2,4})$/).test(trim(strValue));
}
function isPhone(strValue){
	return (/^\d{3}-?\d{8}|\d{4}-?\d{7}$/).test(trim(strValue));	
}
function isTel(str) {	
	return (/^1[3|4|5|8][0-9]\d{4,8}$/).test(trim(strValue));
}
/**
 * 获取单选框的值
 * 
 * @param radioName
 * @return
 */
function getRadioValue(radioName) {
	var obj = document.getElementsByName(radioName);
	var objLen = obj.length;
	var i;
	for (i = 0; i < objLen; i++) {
		if (obj[i].checked == true) {
			return obj[i].value;
		}
	}
	return null;
}
/**
 * 获取复选框的值
 */
function getCheckboxValue(radioName) {
	var obj = document.getElementsByName(radioName);
	var objLen = obj.length;
	var i;
	var result = "";
	for (i = 0; i < objLen; i++) {
		if (obj[i].checked == true) {
			result += obj[i].value + ",";
		}
	}
	return result;
}
/**
 * 复选框 是否处于 选中状态
 */
function CheckboxToChecked(eleName, cValue) {

	var obj = document.getElementsByName(eleName);

	var objLen = obj.length;
	var i;
	var result = "";
	for (i = 0; i < objLen; i++) {

		if (obj[i].value == cValue) {

			obj[i].checked = true;
		} else {
			obj[i].checked = false;
		}
	}
	return result;
}

// checkBox至少选中一项
function chkCheckBoxChs(objNam, txt) {
	var obj = document.getElementsByName(objNam);
	var objLen = obj.length;
	var num = 0;
	for (i = 0; i < objLen; i++) {
		if (obj[i].checked == true) {
			num++;
		}
	}
	if (num == 0) {
		alert(txt);
		return false;
	}
	return true;
}

function isEnglish(strValue) {
	var reg = /[A-Za-z0-9_]{6,20}/i;
	var patt = new RegExp(reg);
	return patt.test(strValue);
}
function isNickname(strValue) {
	var reg = /^[a-z-_\u4e00-\u9fa5]+$/i;
	return reg.test(trim(strValue));
}
function isRealname(strValue){
	var reg = /^[\u4e00-\u9fa5]+$/i;
	return reg.test(trim(strValue));
}
function isPassword(strValue) {
	var reg = strValue.length;
	if(reg >= 6 && reg <= 12 ){
	   return true;
	}else{
		return false;
	}
}
function isArea(strValue) {
	var reg = /^0\d{2,3}$/;
	var patt = new RegExp(reg);
	return patt.test(strValue);
}

function isNumber(strValue){
	var reg = /^\d+$/;
	return reg.test(trim(strValue));
}
function error_handle(o,msg){
	o.focus();
	alert(msg);
}
function right_handle(){}
function split_param(attr){
	return attr.split('|');
}

// 自定义过滤器
var custom_filter = new Array;

function check_input(input){
	var qs;
	var attr;
	var other_obj;
	var value;
	
	// 为空的情况 -empty
	if (input.getAttribute('empty') && input.value == '') {
		error_handle(input,input.getAttribute('empty'));
		return false;
	}
	
	// 检测字符串最大长度
	if (input.getAttribute('maxlen')) {
		qs = split_param(input.getAttribute('maxlen'));
		if(input.value.length > qs[0]){
			error_handle(input,qs[1]);
			return false;
		}
	}
	
	// 检测字符串最小长度
	if (input.getAttribute('minlen')) {
		qs = split_param(input.getAttribute('minlen'));
		if(input.value.length < qs[0]){
			error_handle(input,qs[1]);
			return false;
		}
	}
	
	// 检查数值相等的情况 -equal
	if (input.getAttribute('equal')) {
		attr = input.getAttribute('equal');
		qs = attr.split('|');
		if (input.value != qs[0]) {
			error_handle(input,qs[1]);
			return false;
		}
	}
	
	// 检查数值不相等的情况 -noequal
	if (input.getAttribute('noequal')) {
		attr = input.getAttribute('noequal');
		qs = attr.split('|');
		if (input.value == qs[0]) {
			error_handle(input,qs[1]);
			return false;
		}
	}
	
	// 检查对象相等的情况 -equalo
	if (input.getAttribute('equalo')) {
		attr = input.getAttribute('equalo');
		qs = attr.split('|');
		other_obj = document.getElementById(qs[0]);
		if (input.value != other_obj.value) {
			error_handle(input,qs[1]);
			return false;
		}
	}

	// 检查值的类型 -ctype
	if (input.getAttribute('ctype')) {
		attr = input.getAttribute('ctype');
		qs = attr.split('|');
		var func = 'is'+ qs[0].substring(0,1).toUpperCase()+qs[0].substring(1).toLowerCase();
		
		if (!eval(func+'(input.value)')) {
			error_handle(input,qs[1]);
			return false;
		}
	}
	
	// 检查异步请求的情况Ajax
	if (input.getAttribute('ajax')) {
		attr = input.getAttribute('ajax');
		qs = attr.split('|');
		var ajax_call = qs[0];
		eval(ajax_call+"(input,qs[1])");
	}
	
	for(var j=0;j<custom_filter.length;j++){
		if(input.id==custom_filter[j].name || input.name==custom_filter[j].name){
			if(custom_filter[j].callback(input)==false){
				error_handle(input,custom_filter[j].msg);
				return false;
			}
		}
	}
	return true;
}
function checkform(event, oform) {
	event = event ? event : window.event;
	if (oform == undefined || oform == null)
		var oform = event.srcElement ? event.srcElement : event.target;
	var elms = oform.elements;
	for ( var i = 0; i < elms.length; i++) {		
		var r = check_input(elms[i]);
		if(r==false) return false;
		else if(r!=false) right_handle(elms[i]);
	}
	return true;
}
/**
 * 增加自定义过滤条件
 * 
 * @return
 */
function add_filter(name,msg,callback){
	custom_filter.push({'name':name,'msg':msg,'callback':callback});
}
/**
 * 验证表单
 * 
 * @param id
 * @return
 */
function validator(id) {
	if(id==null) return false;
	var oform = document.getElementById(id);
	oform.onsubmit = checkform;
}
function validator_each(id){
	if(id==null) return false;
	var elms = document.getElementById(id);
	for ( var i = 0; i < elms.length; i++) {
		elms[i].onblur = function(){
			var r = check_input(this);
			if(r==false) return false;
			else if(r!=false) right_handle(this);
			return true;
		};
	}
	return true;
}
/**
 * 强制验证表单，用于非提交的处理，执行此函数时，即检查表单合格性
 * 
 * @param id
 * @return
 */
function validator_force(id) {
	var oform = document.getElementById(id);
	return checkform(null, oform);
}