<?php
class QQOAuth
{
    private $appid;
    private $appkey;

    public $access_token = array();
    private static $QQhexchars = "0123456789ABCDEF";

    function __construct($appid,$appkey)
    {
        $this->appid = $appid;
        $this->appkey = $appkey;
    }
    /**
     * @brief QQ登录中对url做编解码的统一函数
     * 按照RFC 1738 对URL进行编码
     * 除了-_.~之外的所有非字母数字字符都将被替换成百分号(%)后跟两位十六进制数
     */
    static function QQConnect_urlencode($str)
    {
        $urlencode = "";
        $len = strlen($str);

        for($x = 0 ; $len--; $x++)
        {
            if (($str[$x] < '0' && $str[$x] != '-' && $str[$x] != '.') ||
            ($str[$x] < 'A' && $str[$x] > '9') ||
            ($str[$x] > 'Z' && $str[$x] < 'a' && $str[$x] != '_') ||
            ($str[$x] > 'z' && $str[$x] != '~'))
            {
                $urlencode .= '%';
                $urlencode .= self::$QQhexchars[(ord($str[$x]) >> 4)];
                $urlencode .= self::$QQhexchars[(ord($str[$x]) & 15)];
            }
            else
            {
                $urlencode .= $str[$x];
            }
        }
        return $urlencode;
    }
    static function QQConnect_urldecode($str)
    {
        $urldecode = "";
        $len = strlen($str);

        for ($x = 0; $x < $len; $x++)
        {
            if ($str[$x] == '%' && ($len - $x) > 2 && (strpos(self::$QQhexchars, $str[$x+1]) !== false) && (strpos(self::$QQhexchars, $str[$x+2]) !== false))
            {
                $tmp = $str[$x+1].$str[$x+2];
                $urldecode .= chr(hexdec($tmp));
                $x += 2;
            }
            else
            {
                $urldecode .= $str[$x];
            }
        }

        return $urldecode;
    }
    /**
     * @brief 对参数进行字典升序排序
     * @param $params 参数列表
     * @return 排序后用&链接的key-value对（key1=value1&key2=value2...)
     */
    static function get_normalized_string($params)
    {
        ksort($params);
        $normalized = array();
        foreach($params as $key => $val)
        {
            $normalized[] = $key."=".$val;
        }
        return implode("&", $normalized);
    }
    /**
     * @brief 使用HMAC-SHA1算法生成oauth_signature签名值
     * @param $key  密钥
     * @param $str  源串
     * @return 签名值
     */
    static function get_signature($str, $key)
    {
        $signature = "";
        if (function_exists('hash_hmac'))
        {
            $signature = base64_encode(hash_hmac("sha1", $str, $key, true));
        }
        else
        {
            $blocksize	= 64;
            $hashfunc	= 'sha1';
            if (strlen($key) > $blocksize) $key = pack('H*', $hashfunc($key));
            $key	= str_pad($key,$blocksize,chr(0x00));
            $ipad	= str_repeat(chr(0x36),$blocksize);
            $opad	= str_repeat(chr(0x5c),$blocksize);
            $hmac 	= pack('H*',$hashfunc(($key^$opad).pack('H*',$hashfunc(($key^$ipad).$str))));
            $signature = base64_encode($hmac);
        }
        return $signature;
    }

    /**
     * @brief 对字符串进行URL编码，遵循rfc1738 urlencode
     *
     * @param $params
     *
     * @return URL编码后的字符串
     */
    static function get_urlencode_string($params)
    {
        ksort($params);
        $normalized = array();
        foreach($params as $key => $val)
        {
            $normalized[] = $key."=".self::QQConnect_urlencode($val);
        }
        return implode("&", $normalized);
    }

    /**
     * @brief 检查openid是否合法
     *
     * @param $openid  与用户QQ号码一一对应
     * @param $timestamp　时间戳
     * @param $sig　　签名值
     *
     * @return true or false
     */
    static function is_valid_openid($openid, $timestamp, $sig)
    {
        global $global_arg;
        $key = $_SESSION["appkey"];
        $str = $openid.$timestamp;
        $signature = self::get_signature($str, $key);
        $global_arg = $signature;
        return $sig == $signature;
    }

    /**
     * @brief 所有Get请求都可以使用这个方法
     *
     * @param $url
     * @param $appid
     * @param $appkey
     * @param $access_token
     * @param $access_token_secret
     * @param $openid
     *
     * @return true or false
     */
    function do_get($url)
    {
        $sigstr = "GET"."&".self::QQConnect_urlencode("$url")."&";

        //必要参数, 不要随便更改!!
        $params = $_GET;
        $params["oauth_version"]          = "1.0";
        $params["oauth_signature_method"] = "HMAC-SHA1";
        $params["oauth_timestamp"]        = time();
        $params["oauth_nonce"]            = mt_rand();
        $params["oauth_consumer_key"]     = $this->appid;
        $params["oauth_token"]            = $this->access_token['oauth_token'];
        $params["openid"]                 = $this->access_token['openid'];
        unset($params["oauth_signature"]);

        //参数按照字母升序做序列化
        $normalized_str = self::get_normalized_string($params);
        $sigstr        .= self::QQConnect_urlencode($normalized_str);

        //签名,确保php版本支持hash_hmac函数
        $key = $this->appkey."&".$this->access_token['oauth_token_secret'];
        $signature = self::get_signature($sigstr, $key);
        $url      .= "?".$normalized_str."&"."oauth_signature=".self::QQConnect_urlencode($signature);

        //echo "$url\n";
        return file_get_contents($url);
    }

    /**
     * @brief 所有multi-part post 请求都可以使用这个方法
     *
     * @param $url
     * @param $appid
     * @param $appkey
     * @param $access_token
     * @param $access_token_secret
     * @param $openid
     *
     */
    static function do_multi_post($url, $appid, $appkey, $access_token, $access_token_secret, $openid)
    {
        //构造签名串.源串:方法[GET|POST]&uri&参数按照字母升序排列
        $sigstr = "POST"."&"."$url"."&";

        //必要参数,不要随便更改!!
        $params = $_POST;
        $params["oauth_version"]          = "1.0";
        $params["oauth_signature_method"] = "HMAC-SHA1";
        $params["oauth_timestamp"]        = time();
        $params["oauth_nonce"]            = mt_rand();
        $params["oauth_consumer_key"]     = $appid;
        $params["oauth_token"]            = $access_token;
        $params["openid"]                 = $openid;
        unset($params["oauth_signature"]);


        //获取上传图片信息
        foreach ($_FILES as $filename => $filevalue)
        {
            if ($filevalue["error"] != UPLOAD_ERR_OK)
            {
                //echo "upload file error $filevalue['error']\n";
                //exit;
            }
            $params[$filename] = file_get_contents($filevalue["tmp_name"]);
        }

        //对参数按照字母升序做序列化
        $sigstr .= self::get_normalized_string($params);

        //签名,需要确保php版本支持hash_hmac函数
        $key = $appkey."&".$access_token_secret;
        $signature = self::get_signature($sigstr, $key);
        $params["oauth_signature"] = $signature;

        //处理上传图片
        foreach ($_FILES as $filename => $filevalue)
        {
            $tmpfile = dirname($filevalue["tmp_name"])."/".$filevalue["name"];
            move_uploaded_file($filevalue["tmp_name"], $tmpfile);
            $params[$filename] = "@$tmpfile";
        }
        /*
         echo "len: ".strlen($sigstr)."\n";
         echo "sig: $sigstr\n";
         echo "key: $appkey&\n";
         */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);
        //$httpinfo = curl_getinfo($ch);
        //print_r($httpinfo);

        curl_close($ch);
        //删除上传临时文件
        unlink($tmpfile);
        return $ret;
    }
    /**
     * @brief 所有post 请求都可以使用这个方法
     * @param $url
     * @param $appid
     * @param $appkey
     * @param $access_token
     * @param $access_token_secret
     * @param $openid
     */
    static function do_post($url, $appid, $appkey, $access_token, $access_token_secret, $openid)
    {
        //构造签名串.源串:方法[GET|POST]&uri&参数按照字母升序排列
        $sigstr = "POST"."&".QQConnect_urlencode($url)."&";

        //必要参数,不要随便更改!!
        $params = $_POST;
        $params["oauth_version"]          = "1.0";
        $params["oauth_signature_method"] = "HMAC-SHA1";
        $params["oauth_timestamp"]        = time();
        $params["oauth_nonce"]            = mt_rand();
        $params["oauth_consumer_key"]     = $appid;
        $params["oauth_token"]            = $access_token;
        $params["openid"]                 = $openid;
        unset($params["oauth_signature"]);

        //对参数按照字母升序做序列化
        $sigstr .= self::QQConnect_urlencode(self::get_normalized_string($params));

        //签名,需要确保php版本支持hash_hmac函数
        $key = $appkey."&".$access_token_secret;
        $signature = self::get_signature($sigstr, $key);
        $params["oauth_signature"] = $signature;

        $postdata = self::get_urlencode_string($params);

        //echo "$sigstr******\n";
        //echo "$postdata\n";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);

        curl_close($ch);
        return $ret;
    }

    /**
     * @brief 获取access_token。请求需经过URL编码，编码时请遵循 RFC 1738
     * @param $request_token
     * @param $request_token_secret
     * @param $vericode
     *
     * @return 返回字符串格式为：oauth_token=xxx&oauth_token_secret=xxx&openid=xxx&oauth_signature=xxx&oauth_vericode=xxx&timestamp=xxx
     */
    function getAccessToken($request_token, $request_token_secret, $vericode)
    {
        //请求具有Qzone访问权限的access_token的接口地址, 不要更改!!
        $url    = "http://openapi.qzone.qq.com/oauth/qzoneoauth_access_token?";

        //生成oauth_signature签名值。签名值生成方法详见（http://wiki.opensns.qq.com/wiki/【QQ登录】签名参数oauth_signature的说明）
        //（1） 构造生成签名值的源串（HTTP请求方式 & urlencode(uri) & urlencode(a=x&b=y&...)）
        $sigstr = "GET"."&".self::QQConnect_urlencode("http://openapi.qzone.qq.com/oauth/qzoneoauth_access_token")."&";

        //必要参数，不要随便更改!!
        $params = array();
        $params["oauth_version"]          = "1.0";
        $params["oauth_signature_method"] = "HMAC-SHA1";
        $params["oauth_timestamp"]        = time();
        $params["oauth_nonce"]            = mt_rand();
        $params["oauth_consumer_key"]     = $this->appid;
        $params["oauth_token"]            = $request_token;
        $params["oauth_vericode"]         = $vericode;

        //对参数按照字母升序做序列化
        $normalized_str = self::get_normalized_string($params);
        $sigstr        .= self::QQConnect_urlencode($normalized_str);
        //（2）构造密钥
        $key = $this->appkey."&".$request_token_secret;
        //（3）生成oauth_signature签名值。这里需要确保PHP版本支持hash_hmac函数
        $signature = self::get_signature($sigstr, $key);
        //构造请求url
        $url      .= $normalized_str."&"."oauth_signature=".self::QQConnect_urlencode($signature);
        parse_str(file_get_contents($url),$this->access_token);
    }
    /**
     * 请求临时token.请求需经过URL编码，编码时请遵循 RFC 1738
     * @param $appid
     * @param $appkey
     * @return 返回字符串格式为：oauth_token=xxx&oauth_token_secret=xxx
     */
    function getRequestToken()
    {
        //请求临时token的接口地址, 不要更改!!
        $url    = "http://openapi.qzone.qq.com/oauth/qzoneoauth_request_token?";
        //生成oauth_signature签名值。签名值生成方法详见（http://wiki.opensns.qq.com/wiki/【QQ登录】签名参数oauth_signature的说明）
        //（1） 构造生成签名值的源串（HTTP请求方式 & urlencode(uri) & urlencode(a=x&b=y&...)）
        $sigstr = "GET"."&".self::QQConnect_urlencode("http://openapi.qzone.qq.com/oauth/qzoneoauth_request_token")."&";

        //必要参数
        $params = array();
        $params["oauth_version"]          = "1.0";
        $params["oauth_signature_method"] = "HMAC-SHA1";
        $params["oauth_timestamp"]        = time();
        $params["oauth_nonce"]            = mt_rand();
        $params["oauth_consumer_key"]     = $this->appid;

        //对参数按照字母升序做序列化
        $normalized_str = self::get_normalized_string($params);
        $sigstr        .= self::QQConnect_urlencode($normalized_str);

        //（2）构造密钥
        $key = $this->appkey."&";

        //（3）生成oauth_signature签名值。这里需要确保PHP版本支持hash_hmac函数
        $signature = self::get_signature($sigstr, $key);
        //构造请求url
        $url      .= $normalized_str."&"."oauth_signature=".self::QQConnect_urlencode($signature);
        $keys = array();
        parse_str(file_get_contents($url),$keys);
        return $keys;
    }
    /**
     * 跳转到QQ登录页面.请求需经过URL编码，编码时请遵循 RFC 1738
     * @param $appid
     * @param $appkey
     * @param $callback
     * @return 返回字符串格式为：oauth_token=xxx&openid=xxx&oauth_signature=xxx&timestamp=xxx&oauth_vericode=xxx
     */
    function getAuthorizeURL($request_token,$callback)
    {
        //跳转到QQ登录页的接口地址, 不要更改!!
        $redirect = "http://openapi.qzone.qq.com/oauth/qzoneoauth_authorize?oauth_consumer_key={$this->appid}&";
        //构造请求URL
        $redirect .= "oauth_token=".$request_token["oauth_token"]."&oauth_callback=".self::QQConnect_urlencode($callback);
        return $redirect;
    }
    /**
     * 调用API，获取信息
     * @param $api
     */
    function api_get($api)
    {
        return json_decode($this->do_get("http://openapi.qzone.qq.com/$api"), true);
    }

}
