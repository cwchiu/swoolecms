<?php
require '../config.php';
$me = createModel('Me');
$forms = $me->getForm('set',10);

echo Form::head('me_add','post');
foreach($forms as $k=>$f)
{
    echo $k,':';
    echo $f,BL;
}
echo Form::button('','提交',array('type'=>'submit'));
echo Form::foot('me_add');