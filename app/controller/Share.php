<?php

namespace app\controller;

use app\BaseController;
use app\Request;
use think\Log;

class Share extends BaseController
{
    public function getShareList()
    {
        $share = new \app\model\Share();
        $res = $share->getUserMapSingle();
        \think\facade\Log::info($res);
        if (!$res) {
            return error(304, '你还没有分享线索，快去分享吧', null);
        }
        return success(200, '获取成功', $res);

    }

    // 获取单个用户上传的数据
    function getUserDetail()
    {
        $post = \think\facade\Request::post();
        if (!isset($post['userid'])) return error(304, '参数错误', null);
        $share = new \app\model\Share();
        $res = $share->getUserDetail($post['userid']);
        if (!$res) {
            return error(304, '当前用户还没上传线索', null);
        }
        return success(200, '获取成功', $res);
    }
}