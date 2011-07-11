<?php
class Me extends Model
{
    public $table = 'st_tag';
    /**
     * 结构定义
     * @return unknown_type
     */
    function _struct_()
    {
        //存储引擎
        $this->_engine = 'innodb';
        //表结构
        $this->_struct = array(
	        'id'=>array('name'=>'ID','type'=>'id'),
	        'name'=>array('name'=>'姓名','type'=>'varchar','size'=>32,'null'=>false,'default'=>'','index'=>true),
        	'sex'=>array('name'=>'性别','type'=>'set'),
            'addtime'=>array('name'=>'添加日期','type'=>'addtime')
        );
    }
    /**
     * 表单定义
     * @return unknown_type
     */
    function _form_()
    {
        require WEBPATH.'/dict/forms.php';
        $this->_form = array(
            'name'=>array('type'=>'input','size'=>40,'empty'=>'名称不能为空','ctype'=>'realname|真实姓名只能为汉字'),
            'email'=>array('type'=>'input','size'=>80,'empty'=>'邮件不能为空','ctype'=>'email|Email格式不正确'),
            'password'=>array('type'=>'password','size'=>40,'empty'=>'密码不能为空','ctype'=>'password|密码只能为英文字母和数字','maxlen'=>32,'minlen'=>6),
        	'file'=>array('type'=>'upload','empty'=>'文件不能为空'),
            'sex'=>array('type'=>'radio','option'=>$forms['sex'],'empty'=>'性别不能为空'),
            'fav'=>array('type'=>'checkbox','option'=>$forms['level'],'empty'=>'爱好不能为空'),
            'edu'=>array('type'=>'select','option'=>$forms['education'],'empty'=>'学历不能为空'),
        );
    }
	/**
	 * 增加操作时，调用
	 * @return unknown_type
	 */
    function _add_()
    {

    }
    /**
     * 修改操作时调用
     * @return unknown_type
     */
    function _set_()
    {

    }
    /**
     * 列表时调用
     * @return unknown_type
     */
    function _list_()
    {

    }
}