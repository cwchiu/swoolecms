function $goto (url) {
	var main = parent.mainFrame;
	main.location = url;
}

function loginOut()
{
	var msg = confirm("确定要退出系统么？");
	if(!msg){
		return false;
	}
	return true;
}

function closeSys(){
	var msg = confirm('您确认要离开本管理系统么?');
	if(msg){
		parent.close();
		return true;
	}else{
		return false;
	}
}

function ShowMenu(nav){
	var left = parent.leftFrame;
	var main = parent.mainFrame;
	if(nav=='category'){
		left.location='/admin.php?adminjob=tree';
		main.location='/admin.php?adminjob=category';
	}else{
		if(left.document.URL=='http://vc.website.com/admin.php?adminjob=tree'){
			left.location='/admin.php?adminjob=left&nav='+nav;
		}else{
			var Show = left.Showmenu;
			left.nav = nav;
			Show();
		}
	}
}

function $Nav(){
	if(window.navigator.userAgent.indexOf("MSIE")>=1) return 'IE';
  else if(window.navigator.userAgent.indexOf("Firefox")>=1) return 'FF';
  else return "OT";
}

function OpenMenu(cid,lurl,rurl,bid){
   if($Nav()=='IE'){
     if(rurl!='') top.document.frames.main.location = rurl;
     if(cid > -1) top.document.frames.menu.location = 'index_menu.php?c='+cid;
     else if(lurl!='') top.document.frames.menu.location = lurl;
     if(bid>0) document.getElementById("d"+bid).className = 'thisclass';
     if(preID>0 && preID!=bid) document.getElementById("d"+preID).className = '';
     preID = bid;
   }else{
     if(rurl!='') top.document.getElementById("main").src = rurl;
     if(cid > -1) top.document.getElementById("menu").src = 'index_menu.php?c='+cid;
     else if(lurl!='') top.document.getElementById("menu").src = lurl;
     if(bid>0) document.getElementById("d"+bid).className = 'thisclass';
     if(preID>0 && preID!=bid) document.getElementById("d"+preID).className = '';
     preID = bid;
   }
}

function ChangeMenu(way){
	var addwidth = 10;
	var fcol = top.document.all.bodyFrame.cols;
	if(way==1) addwidth = 10;
	else if(way==-1) addwidth = -10;
	else if(way==0){
		if(FrameHide == 0){
			preFrameW = top.document.all.bodyFrame.cols;
			top.document.all.bodyFrame.cols = '0,*';
			FrameHide = 1;
			return;
		}else{
			top.document.all.bodyFrame.cols = preFrameW;
			FrameHide = 0;
			return;
		}
	}
	fcols = fcol.split(',');
	fcols[0] = parseInt(fcols[0]) + addwidth;
	top.document.all.bodyFrame.cols = fcols[0]+',*';
}

function resetBT(){
	if(preID>0) document.getElementById("d"+preID).className = 'bdd';
	preID = 0;
}