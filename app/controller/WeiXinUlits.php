<?php

namespace app\controller;

class WeiXinUlits
{
    // 定时发送批量发送模板消息
    public function sendTemplate()
    {
        $user = new \app\model\User();
        $date = date('Y年m月d');
        $userdata = $user->field('openid,nickname')->select();
        $clue = new \app\model\Clue();
        $count = $clue->where('flag', 1)->count();
        $ulitsThree = new UlitsThree();
        foreach ($userdata as $item) {
            $ulitsThree->sendWeiXinTempleat_notConter($item, $count, $date);
        }
    }
}