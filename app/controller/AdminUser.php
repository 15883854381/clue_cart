<?php

namespace app\controller;

use app\BaseController;
use app\model\User as UserModel;
use app\model\UserProcess;
use think\facade\Log;
use think\facade\Request;

class AdminUser extends BaseController
{

    /**
     * 查询用户所有信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getUserAll()
    {
        $user = new UserModel();
        $post = Request::instance()->post();
        $pageNumber = $post['pageNumber'] ?? 1;
        $pageSize = $post['pageSize'] ?? 10;
        $count = $user->count();
        $data = $user->field('nickname,headimgurl,openid AS id,phone_number,balance,upClueNum,type,notes_name,companyName,flas')->page($pageNumber, $pageSize)->select();

        return success(200, "获取成功", ['count' => $count, 'data' => $data]); // 未过期 且 手机号已存在
    }

    function CountUser()
    {

    }

    /**
     * 修改用户状态
     * @return
     */
    function EditUserFlas()
    {
        $request = Request::instance();
        if ($request->isPost()) {
            $user = new UserModel();
            $post = $request->post();
            $userProcessRes = self::verifyUserProcess($post['id']);
            if (!$userProcessRes) return error('304', '该用户还未上传审核信息', null);


            $updata = [
                'flas' => $post['flas'],
            ];
            if ($post['flas'] == '1') {
                $updata['type'] = $post['type'];
            }

            if (isset($post['notesName'])) {
                $updata['notes_name'] = $post['notesName'];
            }

            $userProcess = new UserProcess();
            $userProcess->where('openid', $post['id'])->save(['flag' => $post['flas']]);
            Log::info($updata);

            $res = $user->where('openid', $post['id'])->update($updata);
            if ($res) {
                return success(200, '修改成功', null);
            } else {
                return error('304', '修改失败', null);
            }
        }
    }

//    获取用户是否上传用户信息
    private function verifyUserProcess($id)
    {
        $userProcess = new UserProcess();
        $res = $userProcess->where('openid', $id)->find();
        if ($res) {
            return true;
        } else {
            return false;
        }
    }


}