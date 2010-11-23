if (!window.XMLHttpRequest)
{
	window.XMLHttpRequest = function()
	{
		var xmlHttp = null;
		var ex;
		try
		{
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP.4.0");
		}
		catch (ex)
		{
			try
			{
				xmlHttp = new ActiveXObject("MSXML2.XMLHTTP");
			}
			catch (ex)
			{
				try
				{
					xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				catch (ex)
				{}
			}
		}
		return xmlHttp;
	}
}

function XHR(callback)
{
	switch(typeof(callback))
	{
		case "function":
		case "string":
			break; //允许参数是 函数或字符串

		default:
			return null;
	}
	//*/

	var xml_method = 0;
	var http = new XMLHttpRequest();
	if (http == null)
	{
		//alert("创建对象失败!");
		return null;
	}

	http.onreadystatechange = function(){
		/* 	0: Uninitialized
			1: Loading
			2: Loaded
			3: Interactive
			4: Finished */
		if(http.readyState == 4)
		{
			try
			{
				var ret = http.responseText; //结果
				if (typeof(callback)=="function")
				{
					callback(ret); //回访回调函数
				}
				else if(typeof(callback)=="string")
				{
					var lc = callback.indexOf("(");
					var rc = callback.indexOf(")");
					//alert("callback: "+lc+" "+rc);
					if ((lc<0)&&(rc<0))
					{
						s = callback+"(ret)";
					}
					else
					{
						var a = "";
						a = (rc-lc<2)?"":",";
						r = /\)/g;
						s = callback.replace(r ,a+"ret)");
					}
					//alert(s);
					eval(s);
				}
				//http = null;
			}
			catch(e)
			{
				//alert(e.description);
			}
		}
	};

	this.get = function(url){
		try
		{
			//alert('a');
			//*
			http.open('get', url, true);
			http.send(null);
			//*/
		}
		catch(e)
		{
			//alert(e.description);
		}
	};

	this.post = function(url,args){
		try
		{
			http.open('post', url, true);
			http.setRequestHeader("Method", "POST " + url + " HTTP/1.1");
			http.setRequestHeader("Charset","GB2312");
			http.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			//*
			var arg_g_arr = args.split("&");
			for(key in arg_g_arr)
			{
				value_arr = arg_g_arr[key].split("=");
				value_arr[1] = encodeURI(value_arr[1]);
				arg_g_arr[key] = value_arr.join("=");
			}
			args = arg_g_arr.join("&");
			//alert(args);
			//*/
			http.send(args);
		}
		catch(e)
		{
			//alert(e.description);
		}
	};
}
