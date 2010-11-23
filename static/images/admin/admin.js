// JavaScript Document

ifcheck = true;
function CheckAll(form)
{
	for (var i=0;i<form.elements.length-2;i++)
	{
		var e = form.elements[i];
		if(e.type=='checkbox') e.checked = ifcheck;
	}
	ifcheck = ifcheck == true ? false : true;
}

var selectCheck = 0;
function whole(form)
{
	for (var i=0;i<form.elements.length-2;i++)
	{
		var e = form.elements[i];
		if(e.type=='checkbox'){
			if(e.checked==true) selectCheck++;
		}
	}
	if(selectCheck<=0){
		alert("请选择操作对象");
		return false;
	}
}