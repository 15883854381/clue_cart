<?php

namespace app\job;

use app\controller\Clue;
use app\controller\Payment;
use think\queue\Job;
use \think\facade\Db;


class OrderBatchPayment
{
    public function Task1(Job $job, $data)
    {


        $this->errorNotifyBath($data['out_trade_no']);
        try {
            (new Payment())->closeOutTradeNo($data['out_trade_no']);
        } catch (\Exception $e) {
            trace($e->getMessage());
        }

        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        $job->delete();

        // 也可以重新发布这个任务
//        $job->release($delay); //$delay为延迟时间

    }


    // 支付失败的时候，调用，修改状态
    public function errorNotifyBath($name)
    {
        $orderTemporary = Db::table('order_temporary');
        $res = $orderTemporary->where([['out_trade_no', '=', $name], ['state', '=', '0']])->select()->toArray();
        if ($res) {
            foreach ($res as $item) {
                $type = $item['cart_type'] == '1' ? 'clue' : 'clue_old';
                Db::table($type)->where('clue_id', $item['clue_id'])->save(['Tosell' => Db::raw('Tosell-' . $item['buy_num'])]);
                Db::table('order_temporary')->where([['clue_id', '=', $item['clue_id']], ['out_trade_no', '=', $item['out_trade_no']]])
                    ->save(['state' => 1]);
            }
        }
    }


}