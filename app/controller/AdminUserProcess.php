<?php

// 审核公司资料
namespace app\controller;

use app\BaseController;
use app\model\UserProcess;
use think\facade\Db;
use think\facade\Filesystem;
use think\facade\Log;
use think\facade\Request;

class AdminUserProcess extends BaseController
{
    // 上传用户审核资料
    function upUserProcess()
    {
        $post = Request::instance()->post();
        // 文件处理并返回数据 url
        $fileimg = Request()->file('file');

        $token = decodeToken();  // 解码token

        $UserProcess = new UserProcess();
        $UserProcess->where('openid', $token->id)->delete();


        $updata = [
            'openid' => $token->id,
            'username' => $post['username'],
            'phone_number' => $post['phone_number'],
            'type' => $post['type'],
        ];
        if ($post['type'] == 2) {
            $updata['companyName'] = $post['companyName'];
            if (!empty($fileimg)) {
                $imgurl = [];
                foreach ($fileimg as $item) {
                    $imgurl[] = Filesystem::disk('public')->putFile('process', $item);
                }
                $updata['img'] = serialize($imgurl);
            } else {
                return error(304, '上传失败,你还没有上传营业执照', null);
            }
        }


        $UserProcess = new UserProcess();
        Log::info($updata);
        try {
            $res = $UserProcess->save($updata);

        } catch (\Exception $e) {
            return error(304, '上传失败', null);
        }
        if ($res) {
            return success(200, '上传成功', null);
        } else {
            return error(304, '上传失败', null);
        }
    }

    // 获取用户的审核状态
    function getState()
    {
        $UserProcess = new UserProcess();
        $token = decodeToken();  // 解码token
        $res = $UserProcess->field('flag')->where('openid', $token->id)->find();
        if (!empty($res)) {
            return success(200, '获取成功', $res);
        }
        return error(304, '没有数据', $res);

    }

    // 获取单个用户的审核数据
    function getEnevtUserProcess()
    {
        $request = Request::domain(1);
        $post = Request()->post();
        if (!isset($post['id'])) {
            return error(304, '参数错误', null);
        }
        $sql = "SELECT a.companyName,a.id,b.flas,img,a.openid,a.phone_number,a.type,a.username,img,notes_name as notesName  FROM user_process a LEFT JOIN user b ON a.openid = b.openid WHERE  a.openid = '${post['id']}'";
        $res = Db::query($sql);
        if (!$res) {
            return error(304, '没有数据', null);
        }
        $res = $res[0];
        $res['img'] = unserialize($res['img']);
        if (!empty($res['img'])) {
            $res['img'] = $request . '/' . 'storage' . '/' . $res['img'][0];
        }
        return error(200, '获取成功', $res);
    }
}

