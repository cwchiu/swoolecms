<?php
class UserBase extends Controller
{
    public $uid;
    function __construct($swoole)
    {
        parent::__construct($swoole);
        if (isset($_POST["PHPSESSID"])) session_id($_POST["PHPSESSID"]);
        session();
        Auth::$login_url = '/page/login/?';
        Auth::login_require();
        $this->uid = $_SESSION['user_id'];
    }
}
