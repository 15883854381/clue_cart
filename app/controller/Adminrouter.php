<?php

namespace app\controller;

use app\BaseController;
use app\model\admin;
use app\model\adminrouter as Router;
use app\model\role;
use app\Request;
use think\cache\driver\Redis;
use think\db\Where;
use think\facade\Config;
use think\facade\Log;

class Adminrouter extends BaseController
{

    // 路由数据
    function setRouter()
    {
        $token = decodeToken();
        if (!$token) {
            return error(304, '非法访问', null);
        }

        if ($token->note->login_type != 2 && $token->note->login_type != 1) {
            return error(304, '登录参数错误', null);
        }

        // 个人 和 企业登录
//        if ($token->note->login_type == 1) {
//            Log::info($token);
//            return error(304, '个人用户', null);
//        }


        //   管理人员 权限判断
//        if ($token->note->login_type == 2) {

        $role = new role();
        $res = $role->where('id', $token->note->authority)->find();
        if (!$res) {
            return error(304, '你没有权限', null);
        }
        if (!$res['role_authority']) {
            return error(304, '你没有权限', null);
        }


        $ItemUser = explode('|', $res['role_authority']);

        // 筛查菜单
        $router = new Router();
        $supRouter = $router->where([['s_id', '=', 0]])->select(); // 上级路由
        $subRouter = $router->where([['s_id', '<>', 0], ['id', 'in', $ItemUser]])->select();
        $RouterMapAll = [];
        foreach ($supRouter as $item) {
            $sup = [
                'path' => $item['router_path'],
                'redirect' => $item['redirect'],
                'component' => $item['component_url'],
                'meta' => ['title' => $item['title'], 'icon' => $item['icon']],
            ];
            foreach ($subRouter as $it) {
                if ($item['id'] == $it['s_id']) {
                    $sup['children'][] = [
                        'path' => $it['router_path'],
                        'name' => $it['router_name'],
                        'component' => $it['component_url'],
                        'meta' => ['title' => $it['title'], 'icon' => $it['icon']],
                    ];
                }
            }
            if (isset($sup['children'])) {
                $RouterMapAll[] = $sup;
            }
        }
        return success(200, '获取成功', $RouterMapAll);
    }


// 权限验证 . 验证是否有当前的页面权限
    function permissions_validation()
    {


        $post = \think\facade\Request::post();
        if (!isset($post['url'])) {
            return error(304, '没有权限', null);
        }

        $router = new Router();
        $res = $router->where('url', $post['url'])->find();// 在此处获取 url id

        // 获取对于角色的 权限 表
        $token = decodeToken();
        $role = new role();
        $roleId = $role->where('id', $token->note->authority)->find();
        $roleArr = explode('|', $roleId['role_authority']);

        if (!in_array($res['id'], $roleArr)) {
            return error(304, '你没有权限', null);
        }
        return success(200, '', null);
    }

}