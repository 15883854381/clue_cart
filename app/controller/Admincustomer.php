<?php

namespace app\controller;

use app\BaseController;
use CURLFile;
use think\facade\Db;
use think\facade\Filesystem;
use  \think\facade\Request;
use think\facade\Log;
use WpOrg\Requests\Requests as http;

class Admincustomer extends BaseController
{
//    var $customer;
//
//    function __construct()
//    {
//        $this->customer = Db::table('customer');
//    }

    // 获取客服人员清单
    function getCustomer()
    {
        $res = Db::table('customer')->Order('createtime', 'DESC')->select();
        $data = [];
        foreach ($res as $key => $item) {
            $item['WatchCode'] = 'http://' . $_SERVER['HTTP_HOST'] . '/' . 'storage' . '/' . $item['WatchCode'];
            $data[] = $item;
        }
        return success(200, '获取成功', $data);
    }


    // 新增客服人员名单
    function AddCustomer()
    {
        $post = Request::instance()->post();
        $fileimg = Request()->file('WatchCode');
        if ($fileimg) {
            $savename = Filesystem::disk('public')->putFile('images', $fileimg);
            $post['WatchCode'] = $savename;
            $media = dirname(dirname(__DIR__)) . '\public\storage\\' . $savename;
            $mediaData = self::upImgWeix($media);
            $mediaDatas = json_decode($mediaData, true);
            $post['media_id'] = $mediaDatas['media_id'];
        }

        $res = Db::table('customer')->save($post);
        if ($res) {
            return success(200, '上传成功', null);
        }
        return error(304, '上传失败', null);
    }

    // 删除客服客服人员
    function DeleteCustomer()
    {
        $post = Request::instance()->post();
        $res = Db::table('customer')->delete(['id', '=', $post['id']]);
        if (!$res) {
            return error(304, '删除失败', Db::table('customer')->getLastSql());
        }
        return success(200, '删除成功', null);
    }

    // 修改用户状态
    function EditCustomerFlag()
    {
        $post = Request::instance()->post();
        $res = Db::table('customer')->where('id', $post['id'])->update(['flag' => $post['flag']]);
        if (!$res) {
            return error(304, '修改失败', null);
        }
        return success(200, '修改成功', null);


    }

    // 分配用户的专属客服
    function Usercustomer()
    {
        $token = decodeToken();
        $request = Request::domain(1);
        if (!$token){
            $row = Db::table('customer')->where('region', '0')->find();
            $row['WatchCode'] = $request . '/' . 'storage' . '/' . $row['WatchCode'];
            return success(200, '没有专属客服', $row);
        }


        $res = Db::table('user')->where('openid', $token->id)->field('area')->find();
        if (!$res || empty($res['area'])) {
            $row = Db::table('customer')->where('region', '0')->find();
            $row['WatchCode'] = $request . '/' . 'storage' . '/' . $row['WatchCode'];
            return success(200, '没有专属客服', $row);
        }

        $row = Db::table('customer')->when(empty($res['area']),
            function ($query) {
                $query->where('region', '0');
            }, function ($query) use ($res) {
                $query->where([['region', 'like', '%' . $res['area'] . '%']]);
            })->where('flag', 1)->find();

        $row['WatchCode'] = $request . '/' . 'storage' . '/' . $row['WatchCode'];
        return success(200, '1233', $row);
    }

    private function upImgWeix($media)
    {
        $ulits = new Ulits($this->app);
        $access_token = $ulits->GetAccess_token();
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s";
        $url = sprintf($url, $access_token, 'image');
        $data = ['media' => new CURLFile($media)];
        $res = http_request_post($url, $data, $media);
        return $res;
    }


}