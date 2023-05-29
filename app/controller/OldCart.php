<?php

namespace app\controller;

use app\BaseController;
use Ramsey\Uuid\Uuid;
use think\facade\Db;
use think\facade\Log;
use think\facade\Request;
use app\model\OldCart as OldCartModedl;

class OldCart extends BaseController
{
    // 上传二手车线索
    function upOldCart()
    {

        if (!Request::isPost()) {
            return false;
        }

        $svial = self::updatavail();
        if ($svial != 'success') {
            return $svial;
        }
        $post = Request::post();
        // 第三方手机验证
        $ulits = new UlitsTwo($this->app);
        $PhoneBelongingplace = $ulits->batchUcheck($post['phone_number']); // 号码归属地
        if (!$PhoneBelongingplace) {
            return error(304, '请填写正确，且有效的手机号码', null);
        }
        $oldcart = new OldCartModedl();

        $clue_id = Uuid::uuid6()->getHex()->toString();
        $token = decodeToken();

        for ($i = 0; $i < $post['sales']; $i++) {
            $post['unitPrice_' . ($i + 1)] = $post['unitPrice_' . $i];
        }


        $data = ['clue_id' => $clue_id, 'openid' => $token->id, 'PhoneBelongingplace' => $PhoneBelongingplace['area']];
        $updata = array_merge($data, $post);
        $res = $oldcart->save($updata);
        if (!$res) {
            return error(304, '上传线索失败，请核对数据后重新上传', null);
        }
        Db::name('user')->where('openid', $token->id)->inc('upClueNum', 1)->update(); // 用户的上上传数据 增加
        // 添加tagsMap
        if (!empty($updata['userTags'])) {
            $oldcart->TagsMap($clue_id, $updata['userTags']);
        }

        return error(200, '上传成功', null);

    }

    private function updatavail()
    {
        $post = Request::post();
        Log::info($post);
        // 验证数据
        try {
            validate(\app\validate\OldCart::class)->check($post);
        } catch (\Exception $e) {
            return error('304', $e->getMessage(), null);
        }
        $oldcart = new OldCartModedl();
        $searchPhone = $oldcart->searchPhone($post['phone_number']);
        if ($searchPhone) {
            return error('304', "当前手机用户已存在", null);
        }
        // 判断售卖次数
        $sales = (int)$post['sales'];
        if ($sales > 3) {
            return error('304', "数据最多只能售卖【3】次", null);
        }
        // 验证价格
        $pickNum = 0;
        $n = 1;
        for ($i = 0; $i < $sales; $i++) {

            if (!array_key_exists("unitPrice_" . $i, $post)) {
                return error('304', "请填写第【${n}】次价格", null);
            }
            if ($post["unitPrice_" . $i] == '') {
                return error('304', "请填写第【${n}】次价格", null);
            }
            if ($post["unitPrice_" . $i] > 2000) {
                return error('304', "第【${n}】次价格不能大于【2000】元", null);
            }
            if ($i > 0) {
                if ($pickNum < $post["unitPrice_" . $i]) {
                    return error('304', "第【${n}】次价格不能大于第【${i}】次 价格", null);
                }
            }
            $pickNum = $post["unitPrice_" . $i];
            $n += 1;
        }

        return 'success';
    }


    function SelectCart()
    {

        $post = Request::post();
        $oldcart = new OldCartModedl();
        $res = $oldcart->SelectOldClue($post);
        $data = $res['data'];
        $clue_id = implode(array_column($res['data'], 'clue_id'), "','");
        $tages = $oldcart->SelectTages($clue_id);
        $i = 0;
        foreach ($data as $item) {
            $tag = [];
            foreach ($tages as $it) {
                if ($item['clue_id'] == $it['clue_id']) {
                    $tag[] = $it;
                }
            }
            $data[$i]['child'] = $tag;
            $i++;
        }
        $res['data'] = $data;
        return success(200, '获取成功', $res);
    }


}