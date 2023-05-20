<?php

namespace app\model;


use think\facade\Request;
use think\Model;

class Order extends Model
{
    protected $table = 'order_list';

    // 查询是新车还是二手车订单
    public function OldType()
    {
        $post = Request::post();
        $res = $this->where('out_trade_no', $post['out_trade_no'])->field('cart_type')->find();
        if ($res['cart_type'] == 1) {
            return 'clue';
        } else if ($res['cart_type'] == 2) {
            return 'clue_old';
        }
        return false;
    }


}