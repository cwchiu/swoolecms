<?php
class UserBase extends Controller
{
    public $uid;
    function __construct($swoole)
    {
        parent::__construct($swoole);
        session();
        Auth::$login_url = '/page/login/?';
        Auth::login_require();
        $this->uid = $_SESSION['user_id'];
    }
}
