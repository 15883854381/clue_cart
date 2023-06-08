<?php

namespace app\controller;

use app\BaseController;
use think\Request;
use app\model\User;
use app\model\admin;


class AdminLogin extends BaseController
{
    // 后台管理系统的登录
    function login(Request $request)
    {
        if (!$request->ispost()) {
            return error(304, '请求出错', null);
        }
        $post = $request->post();

        if (!isset($post['login_type'])) {
            return error(304, '参数错误', null);
        }
        $note = [];
        $tokenId= '';
        if ($post['login_type'] == '1') {
            // TODO 此处需要判断是否在数据库中
            // 个人、企业


            if (!isset($post['code']) || !isset($post['phone_number'])) {
                return error(304, '参数错误', null);
            }
            $code = new Ulits($this->app);
            $code = $code->getcode();
            if (!$code) {
                return error(304, '验证码错误', null);
            }
            $user = new User();
            $res = $user->where('phone_number', $post['phone_number'])->find();
            if (!$res) {
                return error(304, '登录失败，此用户不存在，请在微信公众号注册',null);
            }

            $tokenId = $res['openid'];
            $note['id'] = $res['openid']; // 用户的id
            $note['login_type'] = $post['login_type'];
            $note['authority'] = $res['authority'];


        } elseif ($post['login_type'] == '2') {

            // 管理员
            if (!isset($post['phone_number']) || !isset($post['password'])) {
                return error(304, '参数错误', null);
            }

            // 查询用户名是否存在
            $admin = new admin();
            $username = $admin->where('phone_number', $post['phone_number'])->find();
            if (!$username) {
                return error(304, '用户不存在', null);
            }
            // 查询用户名和登录密码是否正确
            $res = $admin->where([['phone_number', '=', $post['phone_number']], ['password', '=', $post['password']]])->find();
            if (!$res) {
                return error(304, '用户名或密码错误', null);
            }
            if ($res['authority'] == 5) {
                return error(304, '你没有任何权限，不能进入', null);
            }

            if (isset($res['id'])) {
                $note['id'] = $res['id']; // 用户的id
                $note['login_type'] = $post['login_type'];
                $note['authority'] = $res['authority'];
            }
            $tokenId = $post['phone_number'];


        } else {
            return error(304, '参数错误', null);
        }


        // 向前端发送 token 和 用户基本数据
        $token = encodeToken($tokenId, 7200, $request->ip(), $note);
        $reData = [
            'token' => $token,
            'ext' => 7200,
            'phone_number' => $post['phone_number']
        ];

        return error(200, '登录成功', $reData);

    }

}