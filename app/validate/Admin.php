<?php

namespace app\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'username' => 'require|chs',// 用户姓名
        'nickname' => 'require|chs',// 客服昵称
        'phone_number' => 'require|mobile',// 手机号
        'password' => 'require', // 密码
//        'validation_id' => 'require', // 权限ID
//        'WatchCode' => 'require',// 微信二维码
        'media_id' => 'require',// 微信素材ID
        'region' => 'require',//分配地区
        'authority' => 'require'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'username.require' => '请填写用户名',
        'username.chs' => '用户姓名应为中文汉字',
        'phone_number.require' => "请填写手机号码",
        'phone_number.mobile' => "请填写正确的手机号码",
        'password.require' => "请填写密码",
//        'validation_id.require' => "请为用户分配权限",
//        'WatchCode.require' => "客服人员必须上传微信二维码",
        'media_id.require' => "后台上传文件出错",
        'region.require' => '请为客服分配地区',
        'nickname.require' => "请为客服人员填写昵称",
        'nickname.chs' => "昵称只能为汉字",
        'authority' => "请选择用户类型",
    ];

    //验证场景
    protected $scene = [
        "admin" => ['username', 'phone_number', 'password', 'authority'],
        "customer" => ['username', 'phone_number', 'password', 'nickname',  'region', 'authority'],
        "caller" => ['username', 'phone_number', 'password', 'authority']
    ];

}