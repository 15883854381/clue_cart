<?php
declare (strict_types=1);

namespace app\validate;

use think\Validate;

class OldCart extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'phone_number' => 'require|mobile',//手机号
        'user_name' => 'require|chs|length:1',//用户名
        'sex' => 'require|number|checkCsex', // 性别
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'phone_number.require' => "请填写手机号码",
        'phone_number.mobile' => '请填写正确的手机号码',
        'user_name.require' => '请填写用户（姓）',
        'user_name.chs' => '（姓）只能是汉字',
        'user_name.length' => '请填写用户（姓）',
        'sex.require' => '请选择用户性别',
        'sex.number' => '参数类型错误',
        'sex.checkCsex' => '请选择用户性别',

    ];

    public function checkCsex($value)
    {
        if ($value == 1 or $value == 0) {
            return true;
        } else {
            return false;
        }
    }

}
