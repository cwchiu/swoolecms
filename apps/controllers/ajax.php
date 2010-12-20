<?php
class ajax extends Controller
{
    var $is_ajax = true;

    function check_email()
    {
        if(!empty($_GET['email'])) return $this->model->UserInfo->exists($_GET['email']);
    }
}
?>