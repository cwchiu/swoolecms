<?php
require('../../../config.php');
session();
Auth::$session_prefix = $_GET['prefix'];
Auth::login_require();
import_func('file');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
<style type="text/css">
<!--
body,td,th {
	font-size: 12px;
}
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
-->
</style>
</head>

<body>
<?php
if(isset($_FILES['media']))
{
	$file = file_upload('media');
	if($file=='')
	{
		Swoole_js::js_back('错误的类型！');
		exit;
	}
?>
<script language="javascript">
window.parent.upfile_success("<?php echo $file; ?>");
</script>
<?php
}
?>

<form name="form1" enctype="multipart/form-data" method="post" action="">
  <hr width="100%" size="1" color="#CCCCCC" />
上传图片：
  <input name="media" type="file" id="media" size="40">
  
  <label>
  <input type="submit" name="Submit" value="上传">
  </label>
</form>
</body>
</html>