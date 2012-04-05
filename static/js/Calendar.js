/**
 * 日历表单库
 * @author Tianfeng.Han
 * @package SwooleUI
 */
var months = new Array("一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月",
		"十月", "十一月", "十二月");
var daysInMonth = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
var days = new Array("日", "一", "二", "三", "四", "五", "六");
var today;
document.write("<div id='Calendar' class='calendar_div'></div>");
var calendar = obj('Calendar');

function getDays(month, year) {
	// 下面的这段代码是判断当前是否是闰年的
	if (1 == month)
		return ((0 == year % 4) && (0 != (year % 100))) || (0 == year % 400) ? 29
				: 28;
	else
		return daysInMonth[month];
}

function getToday() {
	// 得到今天的年,月,日
	this.now = new Date();
	this.year = this.now.getFullYear();
	this.month = this.now.getMonth();
	this.day = this.now.getDate();
}

function getStringDay(str) {
	// 得到输入框的年,月,日
	var str = str.split("-");

	this.now = new Date(parseFloat(str[0]), parseFloat(str[1])-1,parseFloat(str[2]));
	this.year = this.now.getFullYear();
	this.month = this.now.getMonth();
	this.day = this.now.getDate();
}

function newCalendar() {
	var parseYear = parseInt(obj('Year').options[obj('Year').selectedIndex].value);
	var newCal = new Date(parseYear, obj('Month').selectedIndex, 1);
	var day = -1;
	var startDay = newCal.getDay();
	var daily = 0;

	if ((today.year == newCal.getFullYear())
			&& (today.month == newCal.getMonth()))
		day = today.day;

	var tableCal = obj('calendar_table');
	var intDaysInMonth = getDays(newCal.getMonth(), newCal.getFullYear());

	for ( var intWeek = 1; intWeek < tableCal.rows.length; intWeek++) {
		for ( var intDay = 0; intDay < tableCal.rows[intWeek].cells.length; intDay++) {
			var cell = tableCal.rows[intWeek].cells[intDay];
			if ((intDay == startDay) && (0 == daily))
				daily = 1;

			if (day == daily) // 今天，调用今天的Class
			{
				cell.style.background = '#197dd0';
				cell.style.color = '#FFFFFF';
				// cell.style.fontWeight='bold';
			} else if (intDay == 6) // 周六
				cell.style.color = 'green';
			else if (intDay == 0) // 周日
				cell.style.color = 'red';

			if ((daily > 0) && (daily <= intDaysInMonth)) {
				cell.innerHTML = daily;
				daily++;
			} else
				cell.innerHTML = "";
		}
	}
}
function GetDate(day, InputBox) {
	var sDate;
	// 这段代码处理鼠标点击的情况
	sDate = obj('Year').value + "-" + obj('Month').value + "-" + day.innerHTML;
	obj(InputBox).value = sDate;
	HiddenCalendar();
}

function HiddenCalendar() {
	// 关闭选择窗口
	obj('Calendar').style.visibility = 'hidden';
}

function ShowCalendar(InputBox) {
	var x, y, intLoop, intWeeks, intDays;
	var DivContent;
	var year, month, day;
	
	var o = InputBox;
	var oid = o.id;
	var thisyear; // 真正的今年年份

	if (!oid) oid = o.name;

	thisyear = new getToday();
	thisyear = thisyear.year;

	today = o.value;
	if (isDate(today))
		today = new getStringDay(today);
	else
		today = new getToday();

	// 显示的位置
	x = o.offsetLeft;
	y = o.offsetTop;
	while (o = o.offsetParent) {
		x += o.offsetLeft;
		y += o.offsetTop;
	}
	var calendar = obj('Calendar');
	calendar.style.left = x+'px';
	calendar.style.top = (y+26)+'px';
	calendar.style.visibility = "visible";

	// 下面开始输出日历表格(border-color:#9DBAF7)
	DivContent = "<table border='0' cellspacing='0'>";
	DivContent += "<tr class='tr_title'>";
	DivContent += "<td>";

	// 年
	DivContent += "<select name='Year' id='Year' onChange='newCalendar()'>";
	for(intLoop=(thisyear-10);intLoop<(thisyear+10);intLoop++)
	{
		DivContent += "<option value= " + intLoop + " "	+ (today.year == intLoop ? "Selected" : "") + ">" + intLoop	+ "</option>";
	}
	DivContent += "</select>";

	// 月
	DivContent += "<select name='Month' id='Month' style='margin-left:5px;' onChange='newCalendar()'>";
	for (intLoop = 0; intLoop < months.length; intLoop++)
		DivContent += "<option value= " + (intLoop + 1) + " "
				+ (today.month == intLoop ? "Selected" : "") + ">"
				+ months[intLoop] + "</option>";
	DivContent += "</select>";

	DivContent += "</td>";

	DivContent += "<td class='calendar_close' title='关闭'><a href='javascript:HiddenCalendar()'><img src='/static/nback_img/tc_close.gif' width='29' height='22' border='0' /></a></td>";
	DivContent += "</tr>";

	DivContent += "<tr><td align='center' colspan='2'>";
	DivContent += "<table id='calendar_table' border='0' class='tab_date'>";

	// 星期
	DivContent += "<tr class='tab_date_tr1'>";
	for (intLoop = 0; intLoop < days.length; intLoop++)
		DivContent += "<td align='center' style='font-size:12px'>"
				+ days[intLoop] + "</td>";
	DivContent += "</tr>";

	// 天
	for (intWeeks = 0; intWeeks < 6; intWeeks++) {
		DivContent += "<tr>";
		for (intDays = 0; intDays < days.length; intDays++)
		{
			DivContent += "<td onClick='GetDate(this,\""+oid+ "\")' class='calendar_td' align='center'></td>";
		}
		DivContent += "</tr>";
	}
	DivContent += "</table></td></tr></table>";
	obj('Calendar').innerHTML = DivContent;
	newCalendar();
}

function isDate(dateStr) {
	var datePat = /^(\d{4})(\-)(\d{1,2})(\-)(\d{1,2})$/;
	var matchArray = dateStr.match(datePat);
	if (matchArray == null)
		return false;
	var month = matchArray[3];
	var day = matchArray[5];
	var year = matchArray[1];
	if (month < 1 || month > 12)
		return false;
	if (day < 1 || day > 31)
		return false;
	if ((month == 4 || month == 6 || month == 9 || month == 11) && day == 31)
		return false;
	if (month == 2) {
		var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
		if (day > 29 || (day == 29 && !isleap))
			return false;
	}
	return true;
}