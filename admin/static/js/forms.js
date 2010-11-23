// JavaScript Document
var ifcheck = {};
var current_open = '';
function $(id)
{
	return document.getElementById(id);
}

function CheckAll(box_name) {
	if (typeof (ifcheck[box_name]) == 'undefined')
		ifcheck[box_name] = true;
	var boxes = document.getElementsByName(box_name);
	for (i = 0; i < boxes.length; i++) {
		boxes[i].checked = ifcheck[box_name];
	}
	if (ifcheck[box_name])
		ifcheck[box_name] = false;
	else
		ifcheck[box_name] = true;
}
function multiSelect(data,name,div){
	if(data.length==0) return false;
	var select = document.createElement('select');	
	for(var i in data){
		addOption(select,i,data[i][1]);
	}
	select.click = multiSelect(data[document.getElementById(name).value]['child'],div);
	div.appendChild(select);
}
function addOption(cselect, value, text ,callback) {
	var newOption = document.createElement("option");
	newOption.value = value;
	newOption.text = text;
	cselect.options.add(newOption);
}

function getCheckBoxValue(box_name) {
	var boxes = document.getElementsByName(box_name);
	var values = new Array();
	for ( var i = 0; i < boxes.length; i++) {
		if (boxes[i].checked) {
			values.push(boxes[i].value);
		}
	}
	return values;
}


function showTarget(job) {
	if (job != '') {
		var o = $(job);
		if (o)
			openOption(job);
	}
	if (current_open != '' && $(current_open))
		closeOption(current_open);
	current_open = job;
}

function openOption(job) {
	$(job).disabled = false;
	$(job).style.display = '';
}

function closeOption(job) {
	$(job).disabled = true;
	$(job).style.display = 'none';
}

function NewFile(stat) {
	var filename = prompt("请给文件夹起个名字", '新的文件夹');
	if (filename != null && filename != "") {
		location.href = "/sell/addfile/?name=" + filename + "&online=" + stat
				+ "";
	}
	return;
}

function EditFile(id, name) {
	var filename = prompt("请输入一个新名字", name);
	if (filename != null && filename != "") {
		location.href = "/sell/dofile/?action=edit&filename=" + filename
				+ "&id=" + id + "";
	}
	return;
}