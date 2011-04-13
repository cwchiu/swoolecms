<?php
class AskSubject extends Model
{
    public $table = 'ask_subject';

    function getForms()
    {
        $forms['gold'] = Form::select('gold',range(0,200,5),0,true);
        $gets['order'] = '';
        $category = createModel('AskCategory')->getMap($gets,'name');
        $forms['category'] = Form::select('category',$category);
        return $forms;
    }
}
?>