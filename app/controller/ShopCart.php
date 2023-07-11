<?php

namespace app\controller;

use app\BaseController;
use app\Request;
use think\facade\App;
use think\facade\Db;
use think\Log;


class ShopCart extends BaseController
{


    // 查询购物车
    function SelectShopCart(Request $request)
    {
        $post = $request->post();
        $page = pageData($post);//分页
        $token = decodeToken();
        $where = " a.flag = 1 AND sc.openid = '$token->id'";
        $sql = "SELECT  sc.clue_id,user_name,sex,PhoneBelongingplace,cart_type,sales,Tosell,
                    (CASE Tosell WHEN 0 THEN unitPrice_1  WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END) as unitPrice_1,
                    b.city,b.province,record_file_url,d.`name` as brand, IF(Tosell < sales,1,0) as nonstock,
                    CONCAT_WS('****',substring(a.phone_number, 1, 3),substring(a.phone_number, 8, 4)) as phone_number FROM shop_cart sc 
                LEFT JOIN 	(SELECT * FROM clue UNION SELECT * FROM clue_old) a ON a.clue_id = sc.clue_id
                LEFT JOIN (SELECT a.name as province,b.`name` as city,b.id,b.province_id FROM t_province a LEFT JOIN  t_city b  ON a.id = b.province_id) b ON b.id =  a.cityID							
                LEFT JOIN t_car_brand d ON d.id = a.CartBrandID
                LEFT JOIN notifyurl c ON c.out_trade_no = sc.clue_id  WHERE  $where  LIMIT ${page['pageCount']}, ${page['pageSize']}";
        $res = Db::query($sql);
        if (!$res) {
            return error(304, '没有数据', null);
        }

        $pageTotal = Db::table('shop_cart')->where('openid', $token->id)->count();// 获取总数
        $priceTotal = $this->Totalprice($token);
        return error(200, '获取成功', ['data' => $res, 'pageTotal' => $pageTotal, 'priceTotal' => $priceTotal]);
    }

    // 获取当前用在购物车的数据总价
    public function Totalprice($token)
    {

        $sql = " SELECT 
                    SUM(IF(Tosell < sales,(CASE Tosell WHEN 0 THEN unitPrice_1  WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END),0)) as price
                FROM shop_cart a LEFT JOIN clue b ON a.clue_id = b.clue_id WHERE a.openid = '$token->id'";
        $res = Db::query($sql);
        return $res[0]['price'] * 100;

    }


    // 添加购物车
    function addShopCar(Request $request)
    {
        if (!$request->isPost()) {
            return error(304, '请求出错', null);
        }
        $post = $request->post();
        if (empty($post['clue_id'])) {
            return error(304, '参数错误', null);
        }
        $token = decodeToken();
        $data = ['openid' => "$token->id", 'clue_id' => "${post['clue_id']}"];
        $shopcart = Db::table('shop_cart');
        $resData = $shopcart->where($data)->find();
        if ($resData) {
            return error(304, '仓库已存在', null);
        }
        $res = $shopcart->insert($data);
        trace($shopcart->getLastSql());

        if (!$res) {
            return error(304, '添加失败', null);
        }
        return success(200, '添加仓库成功', null);
    }

    // 删除购物车
    function deleteShopCar(Request $request)
    {
        if (!$request->isPost()) {
            return error(304, '请求出错', null);
        }
        $post = $request->post();
        if (empty($post['clue_id'])) {
            return error(304, '参数错误', null);
        }
        // 判单是 批量删除 还是 单个删除
        if (is_array($post['clue_id'])) {
            $strClue_id = implode("','", $post['clue_id']);
            $where = "'$strClue_id'";
        } else {
            $where = $post['clue_id'];
        }
        $token = decodeToken();
        $res = Db::table('shop_cart')->where([['openid', '=', $token->id], ['clue_id', 'in', $where]])->delete();
        if (!$res) {
            return error(304, '删除失败', null);
        }
        return success(200, '删除成功', null);
    }

    // 提交购买订单
    function submitOrder()
    {
        $clue = new Clue($this->app);
        return $clue->SubmitOrder($this->CreateTemporaryOrder(), 'cart');
    }

    // 创建临时订单
    private function CreateTemporaryOrder()
    {
        $token = decodeToken();
        $out_trade_no = ordernum();//订单号
        $sql = " SELECT b.openid,a.clue_id,cart_type,
                (CASE Tosell WHEN 0 THEN unitPrice_1  WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END) as price FROM shop_cart a 
                LEFT JOIN clue b ON a.clue_id = b.clue_id WHERE Tosell < sales  AND a.openid = '$token->id'";
        $res = Db::query($sql);
        $TemporaryOrderData = [];
        $total = 0;
        foreach ($res as $item) {
            $TemporaryOrderData[] = [
                'out_trade_no' => $out_trade_no,
                'clue_id' => $item['clue_id'],
                'openid' => $token->id,
                'buy_num' => 1,
                'price' => $item['price'],
                'up_openid' => $item['openid'],
                'ExpirationTime' => date("Y-m-d H:i:s", strtotime("now  +  5 hour ")),
                'cart_type' => $item['cart_type'],
            ];
            $total += $item['price'];
        }
        return [
            'priceTotal' => $total,// 价格总量
            'TemporaryOrderData' => $TemporaryOrderData,// 存放在临时库的数据
            'out_trade_no' => $out_trade_no,// 订单号
            'openid' => $token->id,// 购买者的id
        ];
    }
}