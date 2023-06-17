<?php

namespace app\controller;

use app\BaseController;
use app\model\role;
use think\facade\Log;

class AdminRole extends BaseController
{
    // 添加用户时使用 更具当前用户的权限分配 用户最大只能增加自己同级别的人员
    function Role_list()
    {
        $token = decodeToken();
        $role = new role();
        $res = $role->field('id,role_name')->where('id', '>=', $token->note->authority)->select()->toArray();
        if (!$res) {
            return error(304, '没有角色数据', null);
        }
        return success(200, '获取成功', $res);
    }

    // 在角色管理界面 使用 查询所有的用户角色 和 权限列表
    function Role_All_list()
    {

        $role = new role();
        $res = $role->select()->toArray();
        if (!$res) {
            return error(304, '没有数据', null);
        }


        $nuw_arr = [];
        foreach ($res as $item) {
            $item['role_authority'] = explode('|', $item['role_authority']);
            $nuw_arr[] = $item;
        }

        return success(200, '获取成功', $nuw_arr);
    }


    // 查询用户是属于哪个角色
    function RoleState()
    {
        $token = decodeToken();
        if (!$token) {
            return error(304, '没有访问权限', null);
        }
        return success(200, '获取成功', ["authority" => $token->note->authority]);
    }


}