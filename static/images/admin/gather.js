// JavaScript Document
var gatherid;
var opStep=1;
var tmpfile='';
var gatherTotal=0;
var timeTotal=0;
var maxnum=15;
var contentTotal=0;
var operate = document.getElementById('operate');
//var http = new ActiveXObject("Microsoft.XMLHTTP");

function gather(gid){
	document.getElementById('msg').style.display = '';
	operate.style.display = '';
	gatherid=gid;
	opStep=1;
	tmpfile='';
	gatherTotal=0;
	timeTotal=0;
	maxnum=15;
	contentTotal=0;
	var queryString = "&action=start&";
	queryString += "gid="+gatherid+"&";
	queryString += "step="+opStep+"&";
	queryString += "tmpfile="+tmpfile;
	var listurl = url+queryString;
	xhr = new XHR('showStat');
	xhr.get(listurl);
	//document.write(listurl);
}

function showStat(res){
	var msgArray = res.split("|");
	var stat = msgArray[0];
	var gatherNum = parseInt(msgArray[1]);
	var spendTime = parseFloat(msgArray[2]);
	gatherTotal+= gatherNum;
	timeTotal+= spendTime;
	tmpfile = msgArray[3];
	if(opStep==1){
		operate.innerHTML="正在分析列表页........"
	}else{
		operate.innerHTML="正在分析列表页........(STEP"+opStep+")";
	}		
	if(gatherNum>0){
		operate.innerHTML+="<BR />成功获取列表页 "+gatherNum+" 项，共耗时 "+spendTime+" 秒。";
	}else{
		operate.innerHTML+="<BR />分析列表页失败，请检查网络状况以及相关采集配置.";
	}
	if(stat=='continue'){
		opStep++;			
		operate.innerHTML+="<BR />继续下一步........."
		gather(gatherid);
	}else if(stat=='complete'){
		if(gatherTotal==0){
			operate.innerHTML="分析列表页结束,没有获取到任何符合要求的内容页,采集结束.";
			setTimeout('closeMsg()',3000);
			return;
		}
		operate.innerHTML="列表分析结束，共耗时 "+timeTotal+" 秒。<BR>现在开始采集内容页(合计"+gatherTotal+"条信息).............";
		opStep=1;
		timeTotal=0;
		gatherContent();
	}else{
		alert('采集过程出现异常，被迫中断');
		setTimeout('closeMsg()',3000);
		return;
	}
}

function gatherContent(){
	var queryString = "&action=start&job=getcontent&";
	queryString += "gid="+gatherid+"&";
	queryString += "maxnum="+maxnum+"&";
	queryString += "step="+opStep+"&";
	queryString += "tmpfile="+tmpfile+"&";
	var xhr2 = new XHR('startGather');
	var contenturl = url+queryString;
	xhr2.get(contenturl);
	//document.write(contenturl);
	/*
	http.open("post", url, true);
	http.onreadystatechange = startGather;
	http.setRequestHeader("Content-Type", contentType);
	http.send(queryString);	
	*/
}

function startGather(res){
	var numArray = res.split("|");
	var stat = numArray[0];
	var validNum = numArray[1];
	var filtreitNum = numArray[2];
	var spendTime = parseFloat(numArray[3])
	timeTotal+= spendTime;
	contentTotal += parseInt(validNum);
	operate.innerHTML="成功采集 "+validNum+" 条内容，过滤重复网址 "+ filtreitNum+" 条，服务器耗时 "+spendTime+" 秒";
	if(stat=='complete'){
		operate.innerHTML+="<BR>内容页采集完成, total "+contentTotal+"，耗时 "+timeTotal+" 秒 ";
		setTimeout('closeMsg()',3000);
		return;
	}else if(stat=='continue'){
		operate.innerHTML+="<BR>(STEP "+opStep+" ) 自动继续下一步.........";
		opStep++;
		gatherContent();
	}else{
		//alert(stat);
		alert('采集出现异常错误，被迫中止');
		setTimeout('closeMsg()',3000);			
		return;
	}
}

function closeMsg(){
	document.getElementById('msg').style.display='none';
}