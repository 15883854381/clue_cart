<?php
declare (strict_types=1);

namespace app\validate;

use think\Validate;

class Order extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        "order_num" => 'require', // 订单号
        "clue_id" => 'require',// 线索ID
        "openid" => 'require',//用户ID
        "buy_num" => 'require|number', // 购买条数
        "price" => 'require|number',//价格
        "payment_time" => 'require', // 付款时间
        "flat" => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'order_num.require' => "订单ID生成失败",
        'clue_id.require' => '没有这条线索',
        'openid.require' => '请登录',
        'buy_num.require' => '请选择购买条数',
        'price.require' => '没有购买金额',
        'payment_time.require' => '没有付款时间',

    ];

    //验证场景
    protected $scene = [
        "up" => ['order_num', 'clue_id', 'openid', 'buy_num', 'price'],
    ];


}
