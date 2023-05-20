<?php

namespace app\controller;

use app\BaseController;
use app\controller\Ulits;
use think\App;
use think\facade\Db;
use think\facade\Log;
use think\facade\Request;
use WpOrg\Requests\Requests as http;

class WXMenu extends BaseController
{

    // 创建微信菜单
    function CreateMenu()
    {
        $ulite = new Ulits($this->app);
        $access_token = $ulite->GetAccess_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token;
        $data = [
            "button" => [
                [
                    "name" => "汽车线索",
                    "url" => "http://e.199909.xyz/",
                    "type" => "view",
//                    "sub_button" => [
//
//                        [
//                            "type" => "view",
//                            "name" => "购买线索",
//                            "url" => "http://e.199909.xyz/",
//                            'key' => '2'
//                        ],
//                        [
//                            "type" => "view",
//                            "name" => "发布线索",
//                            "url" => "http://e.199909.xyz/#/up_Business",
//                            'key' => '1'
//
//                        ]
//                    ]

                ],
                [
                    "name" => "专属客服",
                    "type" => "click",
                    "key" => 'SYSM-001'
                ],
//                [
//                    "name" => "我的",
//                    "sub_button" => [
//                        [
//                            "type" => "click",
//                            "name" => "联系客服",
//                            "key" => "SYSM-001",
//                            "sub_button" => []
//                        ],
//                        [
//                            "type" => "view",
//                            "name" => "登录/注册",
//                            "url" => "http://e.199909.xyz/#/user_data",
//                            'key' => '4'
//                        ]
//                    ]
//                ]
            ]
        ];
        $res = http::post($url, [], json_encode($data, JSON_UNESCAPED_UNICODE));
        $data = json_decode($res->body, true);
        return json($data);
    }

    // 发送 图片
    public function sendImage()
    {
        $xmlData = file_get_contents("php://input");
        // 解析 xml
        $postArr = simplexml_load_string($xmlData, "SimpleXMLElement", LIBXML_NOCDATA);
        if ($postArr->Event == 'CLICK') {
            switch ($postArr->EventKey) {
                case 'SYSM-001':
                    $data = self::customer($postArr->FromUserName);
                    return self::Image($postArr->FromUserName, $postArr->ToUserName, $data['media_id']);
            }
        }
    }

    // 客服分配
    function customer($openid){
        $res = Db::table('user')->where('openid', $openid)->field('area')->find();
        if (!$res) {
            return Db::table('customer')->where('region', '0')->find();
        }
        return Db::table('customer')->when(empty($res['area']),
            function ($query) {
                $query->where('region', '0');
            }, function ($query, $res) {
                $query->where([['region', 'like', '%' . $res['area'] . '%']]);
            })->where('flag', 1)->find();
    }


    // 发送文字信息
    private function SendText($ToUserName, $FromUserName, $Content)
    {
        $textXml = "<xml>
                  <ToUserName><![CDATA[%s]]></ToUserName>
                  <FromUserName><![CDATA[%s]]></FromUserName>
                  <CreateTime>%s</CreateTime>
                  <MsgType><![CDATA[text]]></MsgType>
                  <Content><![CDATA[%s]]></Content>
                </xml>";

        return sprintf($textXml, $FromUserName, $ToUserName, time(), $Content);
    }

    // 发送图文信息

    /**
     * @param $ToUserName
     * @param $FromUserName
     * @param $Content
     * @param $Title
     * @param $Description
     * @param $PicUrl
     * @param $Url
     * @return string
     */
    private function sendImgText($ToUserName, $FromUserName, $Content, $Title, $Description, $PicUrl, $Url)
    {
        $textXml = "<xml>
                      <ToUserName><![CDATA[%s]]></ToUserName>
                      <FromUserName><![CDATA[%s]]></FromUserName>
                      <CreateTime>%s</CreateTime>
                      <MsgType><![CDATA[news]]></MsgType>
                      <ArticleCount>1</ArticleCount>
                      <Articles>
                        <item>
                          <Title><![CDATA[%s]]></Title>
                          <Description><![CDATA[%s]]></Description>
                          <PicUrl><![CDATA[%s]]></PicUrl>
                          <Url><![CDATA[%s]]></Url>
                        </item>
                      </Articles>
                    </xml>";

        return sprintf($textXml, $FromUserName, $ToUserName, time(), $Content, $Title, $Description, $PicUrl, $Url);
    }

    // 发送图片
    private function Image($FromUserName, $ToUserName, $media_id)
    {
        $textXml = "<xml>
                      <ToUserName><![CDATA[%s]]></ToUserName>
                      <FromUserName><![CDATA[%s]]></FromUserName>
                      <CreateTime>%s</CreateTime>
                      <MsgType><![CDATA[image]]></MsgType>
                      <Image>
                        <MediaId><![CDATA[%s]]></MediaId>
                      </Image>
                    </xml>";
        return sprintf($textXml, $FromUserName, $ToUserName, time(), $media_id);
    }


}