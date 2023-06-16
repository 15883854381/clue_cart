<?php

namespace app\controller;

use app\BaseController;
use app\controller\Payment;
use app\job\time24QueryOrder;
use app\model\Clue as ClueModel;
use think\db\exception\DbException;
use think\facade\Db;
use think\facade\Log;
use think\facade\Queue;
use think\facade\Request;
use app\controller\Ulits as Tool;


class Order extends BaseController
{
    // 生成本地支付订单和微信支付订单
    public function order()
    {
        $request = Request::instance()->post();
        // 权限验证
        $data = Tool::authority_verify();
        if ($data['code'] != 200 && $data['code'] != 400) return json($data);
        //购买名额验证
        $res = self::clueNumInventory();
        if (!$res) {
            return error(304, '以无购买名额', null);
        }

        // 验证发布者 和 购买者 是否为同一人
        $id = self::upAndDown($res);
        if (!$id) {
            return error(304, '不能购买自己发布的线索', null);
        }

        $out_trade_no = ordernum();//订单号
        $price = 0;
        $buy_num = 1;

        // 判断是全部购买还是 单条购买
        if ($request['buytype'] == 0) {
            $orderNum = $this->buyAll();
            $price = $orderNum['countMoney'];
            $buy_num = $orderNum['buy_num'];


        } else if ($request['buytype'] == 1) {
            $price = $res['unitPrice_' . ($res['Tosell'] + 1)];
        }
        if (!$price) {
            return error(304, "金额不正确", null);
        }


        // 生成微信订单 并保存 用于后期根据 订单号查询数据 并支付 ============= 开始
        $shop = [
            'description' => '汽车线索互助联盟',
            'out_trade_no' => $out_trade_no,
            'amount' => ['total' => $price * 100], //订单总金额，单位为分
            'payer' => ['openid' => $id]  //用户标识,用户在直连商户appid下的唯一标识
        ];
        Log::error($shop);
        $payment = new Payment();
        $prepay_id = $payment->getPrepayId($shop);
        if (!$prepay_id) {
            return error(304, "订单创建失败1", null);
        }
        // 生成微信订单 并保存 用于后期根据 订单号查询数据 并支付 ============= 结束
        $datas = [
            'cart_type' => $request['type'],// 区分是新车 还是二手车
            'clue_id' => $request['clue_id'],
            'buy_num' => $buy_num,
            'price' => $price,
            'up_openid' => $res['openid'],
            'description' => '汽车线索互助联盟',
            'out_trade_no' => $out_trade_no,
            'openid' => $id,  //用户标识,用户在直连商户appid下的唯一标识
            'prepay_id' => str_replace('"', '', $prepay_id)
        ];
        // 创建订单
        $orderState = self::createOrder($datas, $request['type']);
        if (!$orderState) {
            return error('304', "订单创建失败", null);
        }
        $Order = \think\facade\Config::get('WeixinConfig.Order');
        // 只用于改订单的状态
        Queue::later($Order['Close'], 'app\job\OrderPayment', ['out_trade_no' => $out_trade_no, 'clue_id' => $request['clue_id'], 'type' => $request['type']], null);
        return success(200, "订单创建成功", ['out_trade_no' => $out_trade_no]);
    }

    // 确认订单 并返回前端调用支付所需要的参数
    public function queryOrder()
    {
        $request = Request::instance();
        $payment = new Payment();
        $post = $request->post();
        $Db = Db::name('order_list');
        $res = $Db->where('out_trade_no', $post['out_trade_no'])->find();
        return $payment->payConfig($res['prepay_id']);
    }

    //支付成功后修改订单状态
    public function notify($name)
    {

        $post = Request::instance()->post();
        $Order = \think\facade\Config::get('WeixinConfig.Order');
        $AesUtil = new AesUtil();
        $row = $AesUtil->decryptToString($post['resource']['associated_data'], $post['resource']['nonce'], $post['resource']['ciphertext']);
        $row = json_decode($row, true);
        $payment_time = date("Y-m-d H:i:s");// 交易时间
        $ExpirationTime = date("Y-m-d H:i:s", strtotime("$payment_time + 1 day"));// 订单过期时间
        $Db = Db::table('order_list');
        try {
            $res = $Db->where('out_trade_no', $name)->update([
                'flat' => 3,
                'payment_time' => $payment_time,
                'ExpirationTime' => $ExpirationTime,
                'transaction_id' => $row['transaction_id'],
            ]);
            Queue::later($Order['Success'], time24QueryOrder::class, ['transaction_id' => $row['transaction_id']]);
            if (!$res) {
                trace('交易是失败====' . $payment_time . '=====' . $res, 'error');
            }
        } catch (DbException $e) {
            trace('交易是失败====' . $payment_time . '=====' . $e, 'error');
        }
    }


//    全部购买
    public function buyAll()
    {
        $clue = new \app\model\Clue();
        $olClue = new \app\model\OldCart();

        $post = Request::post();
        $DataB = $post['type'] == 1 ? $clue : $olClue;
        $res = $DataB->where('clue_id', $post['clue_id'])->find();

        Log::error($res);
        $tosell = $res['Tosell'];
        if ($tosell >= $res['sales']) {
            return false;
        }
        $count = 0; // 全部购买的总金额
        for ($i = (int)$tosell + 1; $i <= $res['sales']; $i++) {
            $count += $res['unitPrice_' . $i];

        }
        $num = $res['sales'] - $tosell; // 还可以购买的次数
        return ['countMoney' => $count, 'buy_num' => $num];
    }

    // 用户手动确认订单有效
    public function OrderEditQuery()
    {
        $request = Request::instance();
        $post = $request->post();
        if (!isset($post['id'])) {
            return error(200, '参数错误', null);
        }
        $res = Db::table('order_list')->where('id', $post['id'])->update(['flat' => '1']);
        if (!$res) {
            return error(304, '订单状态修改失败', null);
        }
        return success(200, '确认成功', null);
    }


    // 提交申诉
    public function refund_reason()
    {
        $request = Request::instance();
        $post = $request->post();
        if (!isset($post['id'])) {
            return error(200, '参数错误', null);
        }
        $updata = ['flat' => '5'];
        $updata['refund_reason'] = '1';
        if (!empty($post['refund_reason'])) {
            $updata['refund_reason'] = $post['refund_reason'];
        }
        $res = Db::table('order_list')->where('id', $post['id'])->update($updata);
        if (!$res) {
            return error(304, '申诉提交失败', null);
        }
        return success(200, '提交成功', null);

    }


    /**
     * 查询当前用户的 订单数据
     * @return \think\response\Json
     */
    public function orderSelect()
    {
        $token = decodeToken();  // 解码token
        $sql = " SELECT
                	a.id,
                	a.clue_id,
                	a.cart_type,
                	out_trade_no,
                	creat_time,
                	flat,
                	callPhoneNumber,
                	CONCAT(
                		b.user_name,
                	IF
                	( sex = 1, '先生', '女士' )) AS user_name,
                	a.price,
                	CONCAT( c.`name`, '.', d.`name` ) AS provinceCity,
                	e.`name` AS brandname 
                FROM
                	order_list a
                	LEFT JOIN ( SELECT user_name, clue_id, sex, provinceID, CartBrandID FROM clue UNION SELECT user_name, clue_id, sex, provinceID, CartBrandID FROM clue_old ) b ON a.clue_id = b.clue_id
                	LEFT JOIN t_province c ON b.provinceID = c.id
                	LEFT JOIN t_city d ON b.provinceID = d.id
                	LEFT JOIN t_car_brand e ON b.CartBrandID = e.id
                WHERE a.openid = '$token->id' ORDER BY creat_time DESC ";
        $res = Db::query($sql);
        if ($res) {
            return success(200, '查询成功', $res);
        } else {
            return error(304, '还没有您的订单信息', $res);
        }
    }


    // 根据订单号查询 线索的手机号码
    function getPhone_number()
    {
        $post = Request::post();
        if (empty($post['out_trade_no'])) {
            return error(304, '参数错误', null);
        }

        $sql = "SELECT  b.phone_number FROM order_list	a 
                LEFT JOIN  ( SELECT clue_id,phone_number FROM clue UNION SELECT clue_id, phone_number FROM clue_old ) b ON a.clue_id = b.clue_id 
                WHERE flat in (1,3,5,6) AND out_trade_no='${post['out_trade_no']}'";
        $res = Db::query($sql);
        if ($res) {
            return success(200, '查询成功', $res);
        } else {
            return success(304, '查询失败', null);
        }

    }





    // ==========================================  订单验证 开始  ======================================================

    // 线索数量验证
    private function clueNumInventory()
    {
        $request = Request::instance()->post();
        $type = $request['type'] == 1 ? 'clue' : 'clue_old';

        $sql = "SELECT * FROM ${type} WHERE `clue_id` = '${request['clue_id']}' AND `Tosell` < sales LIMIT 1";
        $res = Db::query($sql);

        if ($res) return $res[0]; else {
            return false;
        }
    }

    // 价格验证
    private
    function moneyInventory($res)
    {
        $request = Request::instance()->post();

        try {
            $money = $res['unitPrice_' . ($res['Tosell'] + 1)]; // 购买金额
        } catch (\Exception $e) {
            return error('304', '购买金额出错', null);
        }
        if ($money != $request['price']) {
            return error(304, '价格不一致', null);
        }
        return 'success';
    }

    // 验证发布者的id 和 购买者的id 不能相同
    private
    function upAndDown($res)
    {
        $token = decodeToken();
        if ($res['openid'] == $token->id) {
            return false;
        }
        return $token->id;
    }

    // 创建订单
    private
    function createOrder($datas, $type)
    {

        Db::startTrans();
        /**
         * 执行正常(提交事务)
         */

        $type = $type == '1' ? 'clue' : 'clue_old';

        try {
            $a = Db::table($type)->where('clue_id', $datas['clue_id'])->save(['Tosell' => Db::raw('Tosell+' . $datas['buy_num'])]);
            Db::table('order_list')->save($datas);
            Db::commit();
        } catch (\Exception $e) {
            Db::table($type)->where('clue_id', $datas['clue_id'])->save(['Tosell' => Db::raw('Tosell- ' . $datas['buy_num'])]);
            Db::rollback();
            return false;
        }
        return true;
    }

// ==========================================  订单验证 结束  ======================================================

// 订单界面的订单详情
    function orderDetail()
    {
        $request = Request::instance()->post();
        if (!isset($request['out_trade_no'])) {
            return error(304, '参数错误', null);
        }
        if (empty($request['out_trade_no'])) {
            return error(304, '参数错误', null);
        }
        $order = new \app\model\Order();
        $dataB = $order->OldType();
        if (!$dataB) {
            return error(304, '没有改订单的数据', null);
        }


        $sql = "select a.clue_id,a.cart_type,price,buy_num,creat_time,a.flat ,
       out_trade_no,
                CONCAT(user_name,IF(sex = 1 ,'先生','女士')) as user_name,
                CONCAT(c.`name`,'.',d.`name`) AS provinceCity,
                e.`name` as brandname
                FROM  order_list a
                LEFT JOIN ${dataB} b ON a.clue_id = b.clue_id
                LEFT JOIN t_province c ON b.provinceID = c.id  	
                LEFT JOIN t_city d ON b.cityID = d.id  
                Left JOIN t_car_brand e ON b.CartBrandID = e.id
                WHERE out_trade_no=" . $request['out_trade_no'];
        $res = Db::query($sql)[0];
        if ($res) {
            return success(200, '', $res);
        }
        return error(304, '查询失败', null);
    }


    // 推荐价格
    public function recommend_price()
    {
        $post = Request::post();

        $order = new \app\model\Order();
        if (isset($post['CartBrandID'])) {
            $sql = "SELECT AVG(price) AS total FROM order_list a LEFT JOIN clue b ON a.clue_id = b.clue_id WHERE b.CartBrandID =" . $post['CartBrandID'];
            $res = Db::query($sql);
            Log::error($res);
            if (!empty($res[0]['total'])) {
                $CountPrice = $res[0]['total'];
            } else {
                $CountPrice = $order->avg('price');
            }
            $ThreePrice = [
                'unitPrice_1' => intval($CountPrice * 0.8),
                'unitPrice_2' => intval($CountPrice * 0.7),
                'unitPrice_3' => intval($CountPrice * 0.6)
            ];
            return success(200, '获取成功', $ThreePrice);

        } else {
            return success(304, '参数错误', null);
        }
    }

    // 收益明细 和 收益金额
    public function incomeDetail()
    {
        $token = decodeToken();

        $sql = "SELECT a.price,buy_num, DATE_ADD(payment_time, INTERVAL 1 DAY) AS payment_time,user_name,sex FROM order_list a 
                LEFT JOIN clue b ON a.clue_id = b.clue_id
                WHERE (flat = 1 or flat = 6) and up_openid ='$token->id' ";
        $res = Db::query($sql);
        $total = 0;
        if (!$res) {
            return error(304, '没有你的收益数据', ['total' => 0, 'data' => $res]);
        }

        foreach ($res as $item) {
            $total += $item['price'];
        }

        return success(200, '获取成功', ['total' => $total, 'data' => $res]);

    }

}