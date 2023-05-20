<?php
declare (strict_types=1);

namespace app\validate;

use think\Validate;

class Clue extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'openid' => 'require',// oprnid
        'user_name' => 'require|chs|length:1',//用户名
        'phone_number' => 'require|mobile',//手机号
        'provinceID' => 'require|number', // 省份ID
        'cityID' => 'require|number', // 市级ID
        'CartBrandID' => 'require|number', // 汽车品牌
        'PhoneBelongingplace' => 'require', // 号码归属地
        'unitPrice_1' => 'require|number|integer', // 第一次价格
        'unitPrice_2' => 'number|integer', // 第二次价格
        'unitPrice_3' => 'number|integer', // 第三次价格
        'sex' => 'require|number|checkCsex', // 性别
        'sales' => 'require|number'// 售卖次数
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'openid.require' => "用户登录失效",
        'user_name.require' => '请填写用户名',
        'user_name.chs' => '用户(姓)应为中文汉字',
        'user_name.length' => '用户名长度不正确(1)',
        'phone_number.require' => "请填写手机号码",
        'phone_number.mobile' => "请填写争正确的手机号码",
        'provinceID.require' => "请选择城市",
        'provinceID.number' => "请正确操作",
        'cityID.require' => "请选择城市",
        'cityID.number' => "请正确操作",
        'CartBrandID.number' => "请选择汽车品牌",
        'CartBrandID.require' => "请选择购车品牌",
        'sex.checkCsex' => "请选择用户性别",
        'sex.require' => "请选择用户性别",
        'sex.number' => "请选择用户性别",
        "PhoneBelongingplace.require" => "请填写用户的归属地",
        "unitPrice_1.require" => '请填写第【1】次价格',
        "unitPrice_1.number" => '金额应为数字',
        "unitPrice_1.integer" => '金额应为整数',
        "unitPrice_2.number" => '金额应为数字',
        "unitPrice_2.integer" => '金额应为整数',
        "unitPrice_3.number" => '金额应为数字',
        "unitPrice_3.integer" => '金额应为整数',
    ];

    //验证场景
    protected $scene = [
        "up" => ['user_name', 'phone_number', 'provinceID', 'cityID', 'cartType', 'CartBrandID'],
        "Clueup" => ['user_name', 'phone_number', 'unitPrice_1', 'unitPrice_2', 'unitPrice_3','sex'],
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
