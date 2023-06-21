<?php

namespace app\controller;

use app\BaseController;
use app\model\admin;
use app\model\customer;
use app\model\User as UserModel;
use app\model\UserProcess;
use CURLFile;
use think\facade\Db;
use think\facade\Filesystem;
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

            $updata['authority'] = $post['type'] == 2 ? 6 : 8; // 此处修改用户角色 目前只有 2个角色

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

    // 公司人员管理 ===============

    // 查询所有人员 信息
    function SelectAllAdminUser()
    {
        $sql = "SELECT a.id,username,phone_number,flag,role_name,authority FROM admin a LEFT JOIN role b ON a.authority = b.id";
        $res = Db::query($sql);
        if (!$res) {
            return error(304, '没有数据', null);
        }
        return success(200, '获取成功', $res);
    }

    // 查询客服人员的详情数据
    function SelectCustomerUser()
    {

        $request = Request::instance();
        $file = Request::domain(1);
        if (!$request->isPost()) {
            return error(304, '请求出错', null);
        }
        $post = $request->post();
        if (!isset($post['phone_number'])) {
            return error(304, '参数错误', null);
        }
        $customer = new customer();
        $res = $customer->where('phone_number', $post['phone_number'])->field('nickname,WatchCode,region')->find();
        $res['WatchCode'] = $file . '/' . 'storage' . '/' . $res['WatchCode'];
        if (!$res) {
            return error(304, '没有当前用户的数据', null);
        }
        return success(200, '获取成功', $res);
    }

    // 修改 人员数据 单个
    function EditAdminUser()
    {
        $request = Request::instance();
        if (!$request->isPost()) {
            return error(304, '请求出错', null);
        }
        $post = $request->post();
        if (!isset($post['id'])) {
            return error(304, '参数错误', null);
        }

        $admin = new admin();
        $adminItemUser = $admin->where('id', $post['id'])->find();
        $res = $adminItemUser->save($post);
        if (!$res) {
            return error(304, '修改失败', null);
        }

        // 判断如果是客服人员 如果有相应的值变化 也需要做对于的修改
        if ($adminItemUser['authority'] == 3) {
            $watchFile = self::UpFileWatch();
            if ($watchFile['code'] == 200) {
                $post = $post + $watchFile;
            } else {
                unset($post['WatchCode']);
            }

            $customer = new customer();
            $customer->allowField(['nickname', 'WatchCode', 'phone_number', 'region', 'media_id'])
                ->where('phone_number', $adminItemUser['phone_number'])
                ->strict(false)
                ->save($post);
        }

        return success(200, '修改成功', null);


    }

    // 删除人员
    function deleteAdminUser()
    {
        $request = Request::instance();
        if (!$request->isPost()) {
            return error(304, '请求出错', null);
        }

        $post = $request->post();
        if (!isset($post['id'])) {
            return error(304, '参数错误', null);
        }
        // 删除管理人员的表
        $admin = new admin();
        $res = $admin->where('id', $post['id'])->field('phone_number,authority')->find(); // 删除所需的数据 ，用于删除客服表
        if (!$res) {
            return error(304, '此数据已被删除', null);
        }
        $delete_res = $res->delete(); // 删除数据
        if (!$delete_res) {
            return error(304, '删除失败', null);
        }
        // 如果是客服人员 则需要删除客服表数据
        if ($res['authority'] == 3) {
            $customer = Db::table('customer');
            $customer->where('phone_number', $res['phone_number'])->delete();
        }

        return error(200, '删除成功', null);
    }

    // 新增人员 管理员 客服 外呼
    function addAdminUser()
    {
        $request = Request::instance();
        $validate = self::dataAdminUser_validate();
        if ($validate['code'] != 200) {
            return error(304, $validate['mes'], null);
        }
        $post = $request->post();
        $admin = new admin();

        // 上传文件到微信
        if ($post['authority'] == 3) {
            $file_res = self::UpFileWatch();
            if ($file_res['code'] != 200) {
                return error(304, $file_res['mes'], null);
            }
            $post = $post + $file_res;
        }

        // 获取上传者的id
        $token = decodeToken();
        if (isset($token->note->id)) {
            $post['add_user_id'] = $token->note->id;
        }

        $upData = [
            'username' => $post['username'],
            'phone_number' => $post['phone_number'],
            'password' => $post['password'],
            'authority' => $post['authority'],
            'add_user_id' => $post['add_user_id']
        ];

        // 上传到数据库 admin 数据库
        $res = $admin->strict(false)->insert($upData);
        if (!$res) {
            return error(304, '上传失败', null);
        }
        // 如果是客服，则上传图片到customer
        if ($post['authority'] == 3) {
            $customer = new customer();
            $upData = [
                'nickname' => $post['nickname'],
                'WatchCode' => $post['WatchCode'],
                'phone_number' => $post['phone_number'],
                'region' => $post['region'],
                'media_id' => $post['media_id']
            ];

            $customer->strict(false)->insert($upData);
        }


        return success(200, '上传成功', null);
    }

    // 获取外呼人员
    function supportStaff()
    {
        $admin = new admin();
        $res = $admin->where([['authority', '=', 4], ['flag', '=', 1]])->field('id,username')->select()->toArray();
        if (!$res) {
            return error(304, '没有数据', null);
        }
        return success(200, '获取成功', $res);

    }

    // 上传客服人员的 二维码到微信平台 upImgWeix
    private function UpFileWatch()
    {
        $fileimg = Request()->file('WatchCode');
        if ($fileimg) {
            $savename = Filesystem::disk('public')->putFile('images', $fileimg);
            $media = dirname(dirname(__DIR__)) . '\public\storage\\' . $savename;
            $mediaData = self::upImgWeix($media);
            $mediaDatas = json_decode($mediaData, true);
            return ['media_id' => $mediaDatas['media_id'], 'WatchCode' => $savename, 'code' => 200];
        } else {
            return ['code' => 304, 'mes' => '请上传客服的二维码'];
        }
    }

    // 请求 微信 接口 上传图片 UpFileWatch
    private function upImgWeix($media)
    {
        $ulits = new Ulits($this->app);
        $access_token = $ulits->GetAccess_token();
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s";
        $url = sprintf($url, $access_token, 'image');
        $data = ['media' => new CURLFile($media)];
        return http_request_post($url, $data, $media);

    }


    /**
     * 验证数据 是否完善  addAdminUser
     * @param $request
     * @return array 返回状态
     */
    private function dataAdminUser_validate(): array
    {
        $request = Request::instance();
        if (!$request->isPost()) {
            return ['code' => 304, 'mes' => '请求出错'];
        }
        $post = $request->post();
        Log::info($post);
        if (!isset($post['authority'])) {
            return ['code' => 304, 'mes' => '参数出错'];
        }

        $scene = '';
        switch ($post['authority']) {
            case 1:
            case 2:
                $scene = 'admin';
                break;
            case 3:
                $scene = 'customer';
                break;
            case 4:
                $scene = 'caller';
                break;
            default:
                $scene = 'admin';

        }

        try {
            validate(\app\validate\Admin::class)->scene($scene)->check($post);
        } catch (\Exception $e) {
            return ['code' => 304, 'mes' => $e->getMessage()];
        }


        $admin = new admin();
        $res = $admin->where('phone_number', $post['phone_number'])->find();
        if ($res) {
            return ['code' => 304, 'mes' => '手机号码已存在'];
        }

        return ['code' => 200, 'mes' => '成功'];
    }

    // 获取所有 状态 为 1 的用户
    function getFlagSuccess()
    {
        $user = new \app\model\User();
        $res = $user->where('flas', 1)->field('openid,nickname')->select();
        return success(200, '', $res);
    }


}
