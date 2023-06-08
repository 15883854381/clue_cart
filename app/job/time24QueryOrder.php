<?php

namespace app\job;

use app\controller\Payment;
use think\facade\Log;
use think\queue\Job;
use \think\facade\Db;

class time24QueryOrder
{

    public function fire(Job $job, $data)
    {
        $Order = Db::table('order_list');
        $res = $Order->where('transaction_id', $data['transaction_id'])->find();
        if ($res['flat'] == 3 or $res['flat'] == 4) {
            $Order->update(['flat' => 1]);
            echo '交易成功，我修改了';
        } else {
            echo '交易成功，但未作修改';
        }


        $job->delete();

        //....这里执行具体的任务
//        print_r($data);
//        if ($job->attempts() > 3) {
//            //通过这个方法可以检查这个任务已经重试了几次了
//        }
//
//
//        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法

//
//        // 也可以重新发布这个任务
//        $job->release($delay); //$delay为延迟时间

    }

//    public function failed($data)
//    {
//
//        // ...任务达到最大重试次数后，失败了
//    }

}