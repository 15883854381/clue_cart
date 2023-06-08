<?php

namespace app\controller;

use app\BaseController;
use think\facade\Config;
use think\facade\Request;
use WpOrg\Requests\Requests as http;
use WeChatPay\Formatter;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Builder;
use WeChatPay\Crypto\AesGcm;
use WeChatPay\Util\PemUtil;

class Payment
{
    //微信支付
    private $appid = 'wx1db4af9dd7f6371f';  //微信公众号ID,唯一标识
    private $secret = '9aa5ad7b6b2f6d221a83769ab8c14ea9';  //微信公众号的appsecret
    private $mchid = '1643243881'; // 商户号
    private $keyCert = 'file://' . '/website/Server/tp/public/wechatpay/apiclient_key.pem';  //商户API私钥
    private $serialNo = '34A07035CA76B3A82927EE23FD31FED4F747854F';  // 「商户API证书」的「证书序列号」
    private $wechatpayCert = 'file://' . '/website/Server/tp/public/wechatpay/wechatpay_32E3709A8EBC2F806A4737C526AB5E5911D9E0BF.pem'; //微信支付平台证书
    private $openid = 'oCSg36mgTy7jNS-AFgk1HAyJSBLY'; // openid
    private $notify_url = '';


    public function __construct()
    {
        $Weixin = Config::get('WeixinConfig.Weixin');
        $this->appid = $Weixin['appid'];
        $this->secret = $Weixin['appsecret'];
        $this->mchid = $Weixin['merchantId'];
        $this->serialNo = $Weixin['serialNo'];
        $this->keyCert = $Weixin['keyCert'];
        $this->wechatpayCert = $Weixin['wechatpayCert'];
        $this->notify_url = $Weixin['notify_url'];

    }


    public function getPrepayId($datas)
    {


        $data = [
            'appid' => $this->appid, //公众号的服务号APPID
            'mchid' => $this->mchid, //商户号
            'attach' => 'ceshi_' . time(), //附加数据,在查询API和支付通知中原样返回
            'notify_url' => $this->notify_url . 'notify/' . $datas['out_trade_no'], //异步接收微信支付结果通知的回调地址
        ];
        $data = array_merge($data, $datas);

        $instance = $this->APIv3();
        try {
            $resp = $instance->chain('v3/pay/transactions/jsapi')->post(['json' => $data]); //jsapi下单
            $prepay_id = json_decode($resp->getBody(), true)['prepay_id'];
            if (isset($prepay_id)) {
                return $prepay_id;
            } else {
                return false;
//                echo $resp->getStatusCode(), PHP_EOL;
//                echo $resp->getBody(), PHP_EOL;
            }
        } catch (\Exception $e) {
            return false;
            // 进行错误处理
//            echo $e->getMessage(), PHP_EOL;
//            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
//                return $r = $e->getResponse();
////                echo $r->getStatusCode() . ' ' . $r->getReasonPhrase(), PHP_EOL;
////                echo $r->getBody(), PHP_EOL, PHP_EOL, PHP_EOL;
//            }
//            return $e->getTraceAsString();
        }
    }

//异步接收微信支付结果通知，自行编写数据处理
    public function notify()
    {
//        file_put_contents('3.txt', "我支付成功了");
    }

//获取支付参数
    public function payConfig($prepay_id)
    {
        $config = $this->sign($prepay_id);
        return json($config);
    }

    //微信支付订单号查询
    public function cxTransactionId()
    {
        $id = '420000***93';
        $resp = $this->APIv3()
            ->chain('v3/pay/transactions/id/' . $id)
            ->get(['query' => ['mchid' => $this->mchid]]);
        return $resp->getBody();
    }

    //商户订单号查询
    public function cxOutTradeNo()
    {
        $out_trade_no = '20221004***02';
        $resp = $this->APIv3()
            ->chain('v3/pay/transactions/out-trade-no/' . $out_trade_no)
            ->get(['query' => ['mchid' => $this->mchid]]);
        return $resp->getBody();
    }

    //关闭订单
    public function closeOutTradeNo($out_trade_no)
    {
        $resp = $this->APIv3()
            ->chain('v3/pay/transactions/out-trade-no/' . $out_trade_no . '/close')
            ->post(['json' => ['mchid' => $this->mchid]]);
        return $resp->getBody(); //正常无返回数据
    }


    //退款
    public function Orderrefund($data)
    {
        $dasta = [
            'transaction_id' => $data['transaction_id'], // 微信支付订单号
            'out_refund_no' => $data['out_refund_no'], // 商户退款单号
            'notify_url' => $this->notify_url . "AdminOrder/verify_refund",// 回调地址
            'funds_account' => 'AVAILABLE',
            'amount' => [
                'refund' => $data['price'] * 100, // 退款金额
                'total' => $data['price'] * 100, // 原订单金额
                'currency' => 'CNY' // 退款币种
            ]

        ];
        $resp = $this->APIv3()->chain('v3/refund/domestic/refunds')->post(['json' => $dasta]);
        return $resp->getBody();
    }


    // 转账
    public function transferAccounts()
    {
        $weixin = \think\facade\Config::get('WeixinConfig.Weixin');
        $out_trade_no = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8); //退款订单号

        $updata = [
            'appid' => $weixin['appid'], // 【商户appid】 申请商户号的appid或商户号绑定的appid（企业号corpid即为此appid）
            'out_batch_no' => $out_trade_no, // 【商家批次单号】 商户系统内部的商家批次单号，要求此参数只能由数字、大小写字母组成，在商户系统内部唯一
            'batch_name' => '测试', // 【批次名称】 该笔批量转账的名称
            'batch_remark' => '测试',//【批次备注】 转账说明，UTF8编码，最多允许32个字符
            'total_amount' => (1 * 100), // 【转账总金额】 转账金额单位为“分”。转账总金额必须与批次内所有明细转账金额之和保持一致，否则无法发起转账操作
            'total_num' => 1, // 【转账总笔数】 一个转账批次单最多发起一千笔转账。转账总笔数必须与批次内所有明细之和保持一致，否则无法发起转账操作。
            'transfer_detail_list' => [
                [
                    'out_detail_no' => $out_trade_no, // 【商家明细单号】 商户系统内部区分转账批次单下不同转账明细单的唯一标识，要求此参数只能由数字、大小写字母组成
                    'transfer_amount' => 1 * 100, // 【转账金额】 转账金额单位为“分”
                    'transfer_remark' => '测试', // 【转账备注】 单条转账备注（微信用户会收到该备注），UTF8编码，最多允许32个字符
                    'openid' => 'oCSg36mgTy7jNS-AFgk1HAyJSBLY',
                ]
            ]
        ];
        try {
            $resp = $this->APIv3()->chain('/v3/transfer/batches')->post(['json' => $updata]);
            return $resp->getBody();
        } catch (\Exception $e) {

            // 进行错误处理
            echo $e->getMessage(), PHP_EOL;
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $r = $e->getResponse();
                echo $r->getStatusCode() . ' ' . $r->getReasonPhrase(), PHP_EOL;
                echo $r->getBody(), PHP_EOL, PHP_EOL, PHP_EOL;
            }
            return $e->getTraceAsString();
        }


    }






//=============================================================================
    //构造 APIv3 客户端实例
    private function APIv3()
    {
        $merchantId = $this->mchid; // 商户号
        // 从本地文件中加载「商户API私钥」，「商户API私钥」会用来生成请求的签名
        $merchantPrivateKeyFilePath = $this->keyCert;
        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);
        // 「商户API证书」的「证书序列号」
        $merchantCertificateSerial = $this->serialNo;
        // 从本地文件中加载「微信支付平台证书」，用来验证微信支付应答的签名
        $platformCertificateFilePath = $this->wechatpayCert;
        $platformPublicKeyInstance = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);
        // 从「微信支付平台证书」中获取「证书序列号」
        $platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);
        // 构造一个 APIv3 客户端实例
        $instance = Builder::factory([
            'mchid' => $merchantId,
            'serial' => $merchantCertificateSerial,
            'privateKey' => $merchantPrivateKeyInstance,
            'certs' => [$platformCertificateSerial => $platformPublicKeyInstance],
        ]);
        return $instance;
    }

    // 签名
    private function sign($prepay_id)
    {
        $merchantPrivateKeyFilePath = $this->keyCert;
        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath);
        $params = [
            'appId' => $this->appid,
            'timestamp' => (string)Formatter::timestamp(),
            'nonceStr' => Formatter::nonce(),
            'package' => 'prepay_id=' . str_replace('"', '', $prepay_id),
        ];
        $params += ['paySign' => Rsa::sign(
            Formatter::joinedByLineFeed(...array_values($params)),
            $merchantPrivateKeyInstance
        ), 'signType' => 'RSA'];
        return $params;
    }


}