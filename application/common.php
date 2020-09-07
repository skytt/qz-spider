<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function check_login() {
	$user = session('user_auth');
	if (empty($user)) {
		return null;
	} else {
		return $user['id'];
	}
}

function encodeInp($input="") {
    $keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    $output = "";
    $chr1 = "";
    $chr2 = "";
    $chr3 = "";
    $enc1 = "";
    $enc2 = "";
    $enc3 = "";
    $enc4 = "";
    $i = 0;
    do {
        $chr1 = get_bianma(substr($input, $i++, 1));//input.charCodeAt(i++);
        $chr2 = get_bianma(substr($input, $i++, 1));//input.charCodeAt(i++);
        $chr3 = get_bianma(substr($input, $i++, 1));//input.charCodeAt(i++);
        $enc1 = $chr1 >> 2;
        $enc2 = ((intval($chr1) & 3) << 4) | (intval($chr2) >> 4);
        $enc3 = ((intval($chr2) & 15) << 2) | (intval($chr3) >> 6);
        $enc4 = intval($chr3) & 63;
        if (!is_numeric($chr2)) {
            $enc3 = 64;
            $enc4 = 64;
        } else if (!is_numeric($chr3)) {
            $enc4 = 64;
        }
        //$output = $output + keyStr.charAt(enc1) + keyStr.charAt(enc2) + keyStr.charAt(enc3) + keyStr.charAt(enc4);
        $output = $output . $keyStr[$enc1] . $keyStr[$enc2] . $keyStr[$enc3] . $keyStr[$enc4] ;
        $chr1 = "";
        $chr2 = "";
        $chr3 = "";
        $enc1 = "";
        $enc2 = "";
        $enc3 = "";
        $enc4 = "";
    } while ($i < strlen($input));
    return $output;
}

//get_bianma(substr($f, $e, 1))等同于js代码$f.charCodeAt($e)
function get_bianma($str)//等同于js的charCodeAt()  
{  
    $result = array();  
    for($i = 0, $l = mb_strlen($str, 'utf-8');$i < $l;++$i)
    {  
        $result[] = uniord(mb_substr($str, $i, 1, 'utf-8'));  
    }  
    return join(",", $result);  
}  
function uniord($str, $from_encoding = false)  
{  
    $from_encoding = $from_encoding ? $from_encoding : 'UTF-8';  
    if (strlen($str) == 1)  
        return ord($str);  
    $str = mb_convert_encoding($str, 'UCS-4BE', $from_encoding);  
    $tmp = unpack('N', $str);  
    return $tmp[1];  
}  