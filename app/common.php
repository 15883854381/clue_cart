<?php

use think\facade\Request;
use Firebase\JWT\JWT;
use  Firebase\JWT\key;

// 这是系统自动生成的公共文件
//function_exists('success')判断是否有这个文件
//成功时调用
if (!function_exists('success')) {
    function success($code, $mes, $data)
    {
        return json([
            'code' => $code,
            'mes' => $mes,
            'data' => $data,
        ]);
    }
}
//失败时调用
if (!function_exists('error')) {
    function error($code, $mes, $data)
    {
        return json([
            'code' => $code,
            'mes' => $mes,
            'data' => $data,

        ]);
    }
}

/**
 * 解密Token 获取用户 openid
 */
function decodeToken($token = null)
{

    $request = Request::instance();
    $token = $request->header('token');
    $key = md5('admin');
//    $time = time();

    // return $token;
    // 验证是否存在token
    if (empty($token)) {
//        return error(304, '请登录后访问', null);
        return false;
    } else {
        try {
            // TODO 判断时间是否在有效期类 
            $data = JWT::decode($token, new Key($key, 'HS256'));
            // 判断 token 是否超过有效期
            if (time() > $data->exp) {
                // return error(304, '登录过期，请重新登录', null);
                return false;
            } else {
                return $data;
            }
        } catch (Exception $e) {
            // return error(304, '登录失败', $e);
            return false;
        }
    }
}

/**
 * @param string $openid 用户的openid
 * @param string $expTime token 失效时间 单位分钟
 * @param string $ip token 用户ip 可不填写
 */
function encodeToken($openid, $expTime = 1440, $ip = '')
{
    // token 加密
    $jwt = new JWT();
    $key = md5('admin');
    $time = time();
    $payload = [
        'iss' => 'admin', // 签发人
        'Userip' => $ip, // 用户ip
        'exp' => strtotime("+" . $expTime . " minute", $time), // 过期时间
        'nbf' => $time, // 生效时间
        'iat' => $time, //签发时间
        'id' => $openid, // 用户id
    ];
    $token = $jwt::encode($payload, $key, 'HS256');
    return $token;
}


// 素材上传 使用此放方法
function http_request_post($url, $data, $file = '')
{
    if (!empty($file)) {
        $data['media'] = new CURLFile($file);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    //判断有无出错
    if (curl_errno($curl) > 0) {
        echo curl_error($curl);
        $output = 'http请求出错！' . '[' . curl_error($curl) . ']';
    }
    curl_close($curl);
    return $output;
}


function ordernum()
{
    return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8); //商户订单号
}











