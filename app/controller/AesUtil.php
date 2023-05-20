<?php

namespace app\controller;

class AesUtil
{
    /**
     * AES key
     *
     * @var string
     */
    public $aesKey = '';

    const KEY_LENGTH_BYTE = 32;
    const AUTH_TAG_LENGTH_BYTE = 16;

    /**
     * Constructor
     */
    public
    function __construct()
    {
        $Weixin = \think\facade\Config::get('WeixinConfig.Weixin');
        $this->aesKey = $Weixin['APIv3'];
    }

    /**
     * Decrypt AEAD_AES_256_GCM ciphertext
     *
     * @param string $associatedData AES GCM additional authentication data
     * @param string $nonceStr AES GCM nonce
     * @param string $ciphertext AES GCM cipher text
     *
     * @return string|bool      Decrypted string on success or FALSE on failure
     */
    public function decryptToString($associatedData, $nonceStr, $ciphertext)
    {
        $ciphertext = \base64_decode($ciphertext);
        if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
            return false;
        }

        // ext-sodium (default installed on >= PHP 7.2)
        if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
            return \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $this->aesKey);
        }

        // ext-libsodium (need install libsodium-php 1.x via pecl)
        if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && \Sodium\crypto_aead_aes256gcm_is_available()) {
            return \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $this->aesKey);
        }

        // openssl (PHP >= 7.1 support AEAD)
        if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
            $ctext = substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE);
            $authTag = substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE);

            return \openssl_decrypt($ctext, 'aes-256-gcm', $this->aesKey, \OPENSSL_RAW_DATA, $nonceStr,
                $authTag, $associatedData);
        }

        throw new \RuntimeException('AEAD_AES_256_GCM需要PHP 7.1以上或者安装libsodium-php');
    }
}


//require_once('./v3OrderCallBack.php');
//
//$getCallBackData = file_get_contents('php://input'); //接收来自微信的回调数据
//
//file_put_contents('./callBack.json', $getCallBackData . "\n\r", FILE_APPEND); //将接收到的数据存入callBack.json文件中
//
//$getData = new AesUtil;
//
//$getReturnData = file_get_contents('./callBack.json');  //将返回的json数据赋值给变量
//$disposeReturnData = json_decode($getReturnData, true);  //将变量由json类型数据转换为数组
//
//$associatedData = $disposeReturnData['resource']['associated_data'];  //获取associated_data数据,附加数据
//$nonceStr = $disposeReturnData['resource']['nonce'];                  //获取nonce数据,加密使用的随机串
//$ciphertext = $disposeReturnData['resource']['ciphertext'];           //获取ciphertext数据,base64编码后的数据密文
//
//$result = $getData->decryptToString($associatedData, $nonceStr, $ciphertext); //调用微信官方给出的方法将解密后的数据赋值给变量
//
//$array_data = json_decode($result, true);  //将解密后的数据转换为数组
//
//file_put_contents('./decryptReturnData.json', $result); //将数据以JSON格式储存到decryptReturnData.json文件中












