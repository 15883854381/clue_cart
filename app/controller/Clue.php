<?php

namespace app\controller;

use app\BaseController;
use EasyTask\Queue;
use Ramsey\Uuid\Uuid;
use think\db\Where;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use app\model\Clue as ClueModel;
use think\cache\driver\Redis;
use WpOrg\Requests\Requests as http;
use think\facade\Log;

class Clue extends BaseController
{
    protected $middleware = [\app\middleware\CheckToken::class]; // 验证登录

    /**
     * 上传用户数据
     * @return \think\response\Json
     */
    public function upClue()
    {
        $request = Request::instance();
        $user = new ClueModel();
        $today = date("Y-m-d", strtotime("today"));

        $post = $request->post();
        if (!($request->isPost())) {
            return error('304', '请求出错', null);
        }
        try {
            validate(\app\validate\Clue::class)->scene('up')->check($post);
        } catch (\Exception $e) {
            return error('304', $e->getMessage(), null);
        }

        // 判断用户的手机号是否存在数据库

        $res = $user->where([
            ['phone_number', '=', $post['phone_number']],
            ['periodofvalidity', '>', $today]

        ])->findOrEmpty();
        $res = json_decode($res);


        if (!empty($res)) {
            return error('304', "当前用户的手机号码已存在", null); // 未过期 且 手机号已存在
        }

        $token = decodeToken();  // 解码token
        $upArr = [
            'openid' => $token->id,
            'unitPrice' => "50",
            'CartBrandID' => 1,
            'inventory' => 1, //库存
            'PhoneBelongingplace' => "四川省成都市",
            'detail' => "这个是一条售卖汽车的线索",
            'notes' => "用户急需使用汽车",
        ];

//        company  createtime Tosell

        $arrs = array_merge($upArr, $post);
        $res = $user->save($arrs);
        if ($res) {
            return error(200, '上传成功', null);
        } else {
            return success(304, '上传失败', null);
        }
    }

    /**
     * 查询线索列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getClueList()
    {
        $request = Request::instance();
//        $user = new ClueModel();
//        $today = date("Y-m-d", strtotime("today"));
        if (!($request->isPost())) {
            return error('304', '请求出错', null);
        }
        $post = $request->post();
        $where = " flag = 1 ";
        $countWhere = 'flag = 1';
        if (isset($post['provinceID'])) {
            if ($post['provinceID'] != 0) {
                $where .= ' and a.provinceID =' . $post['provinceID'];
                $countWhere .= ' and provinceID =' . $post['provinceID'];
            }
        }

        // 是否购买完毕
        if (isset($post['buyNum'])) {
            if ($post['buyNum'] == 1) {
                $countWhere = $where .= ' and  sales > Tosell ';
            } elseif ($post['buyNum'] == 2) {
                $countWhere = $where .= ' and sales <= Tosell';
            }
        }
        $pageNum = $post['pageNum'] ?? 1;
        $pageSize = $post['pageSize'] ?? 10;
        $pageCount = (($pageNum - 1) * $pageSize);

        $sql = "SELECT a.clue_id,sales,a.cart_type,Tosell,CONCAT(user_name,IF(sex = 1 ,'先生','女士')) as user_name , IF(sex = 1 ,'男','女') as sex ,
                                CONCAT_WS('*********',substring(a.phone_number, 1, 3),
                                substring(a.phone_number, 12, 4)) as Cluephone_number,b.name as cartName,
                                CONCAT(c.`name`,'.',e.`name`) AS provinceCity,
                                ROUND(100 / sales * Tosell) as progress,
                                (UNIX_TIMESTAMP(createtime)*1000) as createtime,
                                (CASE Tosell WHEN 0 THEN unitPrice_1  WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END) as Price,
                                IFNULL(notes_name,nickname) as nclueName ,h.total as upClueNum,h.allTotal,CEIL(((h.total/h.allTotal)*100)) as percentage 
																FROM clue a 
                                LEFT JOIN t_car_brand b ON a.CartBrandID = b.id
                                LEFT JOIN t_province c ON  a.provinceID = c.id
                                LEFT JOIN t_city e ON  a.cityID = e.id
																LEFT JOIN (SELECT g.openid, COUNT(CASE WHEN g.flag = 1 THEN 1 END) as total,COUNT(g.openid) as allTotal  FROM clue g GROUP BY openid) h ON h.openid = a.openid
                                left JOIN user f ON a.openid = f.openid where $where ORDER BY createtime DESC  LIMIT $pageCount,$pageSize  ";
        $version = Db::query($sql);

        $clue = new \app\model\Clue();
        $count = $clue->where($countWhere)->count();

        $clue_id = implode(array_column($version, 'clue_id'), "','");
        $oldCart = new \app\model\OldCart();
        $tags = $oldCart->SelectTages($clue_id);
        $i = 0;
        foreach ($version as $item) {
            $tag = [];
            foreach ($tags as $it) {
                if ($item['clue_id'] == $it['clue_id']) {
                    $tag[] = $it;
                }
            }
            $version[$i]['child'] = $tag;
            $i++;
        }
        return success(200, '查询成功', ['data' => $version, 'count' => $count]);
    }


    public function getClueCount()
    {
        $clue = new \app\model\Clue();
        $data = $clue->where('flag', 1)->count();
        return success(200, '获取成功', $data);
    }


    /**
     * 查询线索详情
     * @return
     */
    public function getClueDetail()
    {
        $request = Request::instance();
        if (!($request->isPost())) {
            return error('304', '请求出错', null);
        }
        $post = $request->post();
        if (!isset($post['clue_id']) || !isset($post['type'])) {
            return error('304', '请求参数错误出错', null);
        }
        $token = decodeToken();  // 解码token
        $order = new \app\model\Order();
        $clue = new ClueModel();
        $BuyOrder = [];
        // 此处判断是否购买了 订单 开始
        if ($token) {
            $BuyOrder = $order->where([['clue_id', '=', $post['clue_id']], ['openid', '=', $token->id], ['flat', 'in', [1, 3, 5, 6]]])->find();
        }

        if ($BuyOrder) {
            $res = $clue->CluePhone($post['clue_id'], $post['type']);
            $res[0]['flat'] = $BuyOrder['flat'];
        } else {
            $res = $clue->ClueNotPhone($post['clue_id'], $post['type']);
        }


//        if (isset($BuyOrder['flat'])) {
//
//            $ifData = [1, 3, 5, 6];
//
//            if (in_array($BuyOrder['flat'], $ifData)) {
//                $res = $clue->CluePhone($post['clue_id'], $post['type']);
//            } else {
//                $res = $clue->ClueNotPhone($post['clue_id'], $post['type']);
//            }
//            $res[0]['flat'] = $BuyOrder['flat'];
//        } else {
//            $res = $clue->ClueNotPhone($post['clue_id'], $post['type']);
//        }

        $clue_id = $post['clue_id'];
        $tags_sql = "SELECT tagName FROM tagsmap a LEFT JOIN tags b ON a.tags_id = b.id WHERE clue_id = '${clue_id}'";
        $tags = Db::query($tags_sql);
        $res[0]['tags'] = $tags;
        return success(200, '查询成功', $res);

    }


    // 删除线索数据
    public function deleteCurl()
    {
        $request = Request::instance();

        if (!($request->isPost())) {
            return error('304', '请求出错', null);
        }
        $post = $request->post();
        if (!(isset($post['clue_id'])) || !(isset($post['type']))) {
            return error(304, '参数错误', null);
        }

        if ($post['type'] == '1') {
            $clue = new ClueModel();
            $res = $clue->where('clue_id', $post['clue_id'])->useSoftDelete('flag', 3)->delete();
        } else {
            $oldClue = new \app\model\OldCart();
            $res = $oldClue->where('clue_id', $post['clue_id'])->useSoftDelete('flag', 3)->delete();
        }


        if ($res) {
            return success(200, '删除成功', null);
        } else {
            return error(304, '删除失败，请刷新重试', null);
        }
    }


    /**
     * 上传新车线索
     * @return
     */
    public function upNewCartClue()
    {
        $request = Request::instance();
        $tool = Ulits::class;
        $clue = new ClueModel();
        if (!($request->isPost())) {
            return error('304', '请求出错', null);
        }
        $post = $request->post();

        // 验证用户是否有权限 == 开始
        $data = $tool::authority_verify();
        if ($data['code'] != 200) return json($data);
        // 验证用户是否有权限 == 结束


        // 判断用户的手机号是否存在数据库 == 开始
        $res = $clue->where([
            ['phone_number', '=', $post['phone_number']],
        ])->findOrEmpty();
        $res = json_decode($res);
        if (!empty($res)) {
            return error('304', "当前用户的手机号码已存在", null); // 未过期 且 手机号已存在
        }
        // 判断用户的手机号是否存在数据库 == 结束

        $number_city = self::batchUcheck($post['phone_number']);
        if (!$number_city) {
            return error('304', '请输入有效的手机号', $number_city);
        }
        // 检测手机号码是否存在 === 结束

        $token = decodeToken();  // 解码token
        $clue_id = Uuid::uuid6()->getHex()->toString();
        $post['clue_id'] = $clue_id;
        $post['openid'] = $token->id;
        $post['PhoneBelongingplace'] = $number_city['area'];
        // 验证数据是否正确
        try {
            validate(\app\validate\Clue::class)->scene('Clueup')->check($post);
        } catch (\Exception $e) {
            return error('304', $e->getMessage(), null);
        }


        //  验证价格是否填写正确 === 开始
        $sales = $post['sales'];
        if ($sales > 3) {
            return error('304', "数据最多只能售卖【3】次", null);
        }
        $pickNum = 0;
        $pickArr = [];
        for ($i = 0; $i < $sales; $i++) {
            $n = $i + 1;
            if (!array_key_exists("unitPrice_" . $n, $post)) {
                return error('304', "请填写第【${n}】次价格", null);
            }

            if ($post["unitPrice_" . $n] == '') {
                return error('304', "请填写第【${n}】次价格", null);
            }

            if ($post["unitPrice_" . $n] > 2000) {
                return error('304', "第【${n}】次价格不能大于【2000】元", null);
            }

            if ($i > 0) {
                if ($post["unitPrice_" . $n] > $pickNum) {
                    return error('304', "第【${n}】次价格不能大于【${i}】 价格", null);
                }
            }
            $pickArr[] = "unitPrice_" . $n;

            $pickNum = $post["unitPrice_" . $n];
        }
        //  验证价格是否填写正确 === 结束
        $field = array_merge(['openid', 'user_name', 'clue_id', 'sex', 'phone_number', 'CartBrandID', 'provinceID', 'cityID', 'PhoneBelongingplace', 'sales'], $pickArr);
        //上传
        $res = $clue->allowField($field)->save($post);

        if ($res) {
            if (isset($post['userTags'])) {
                self::upUserTags($post['userTags'], $clue->clue_id);
            }
            Db::name('user')->where('openid', $token->id)->inc('upClueNum', 1)->update(); // 用户的数据 增加
            return success('200', '上传成功', null);
        } else {
            return error('304', '添加失败', null);
        }
    }


    /**
     * 上传用户 tags 标签
     * @param $userTags array 数组
     * @param $id  String 线索id
     * @return void
     */
    private function upUserTags($userTags, $id)
    {
        foreach ($userTags as $item) {
            $upItem = [
                'clue_id' => $id,
                'tags_id' => $item['id'],

            ];
            Db::table('tagsmap')->save($upItem);
        }

    }

    /**
     *短信平台 检测手机号码 有效
     * @param $mobiles
     * @return false|mixed
     */
    public function batchUcheck($mobiles)
    {
        $Code = \think\facade\Config::get('WeixinConfig.Code');
        $params = [
            'appId' => $Code['appId'], // appId,登录万数平台查看
            'appKey' => $Code['appKey'], // appKey,登录万数平台查看
            'mobiles' => $mobiles, // 要检测的手机号，多个手机号码用英文半角逗号隔开
        ];
        $response = http::post('https://api.253.com/open/unn/batch-ucheck', [], $params);

        $data = json_decode($response->body, true);
        if (!isset($data['code']) or $data['code'] != '200000') {
            Log::error($data);
            return false;
        }
        // 手机状态
        if ($data['data'][0]['status'] != 1 && $data['data'][0]['status'] != 4) {
            return false;
        }
        return $data['data'][0];
    }

    // 分享线索
    public function shareClue()
    {

        $data = new Ulits($this->app);
        $res = $data->signJsapi();
        Log::info($res);
        if ($res) {
            return success(200, '获取成功', $res);
        }
        return error(304, '获取授权信息失败', $res);
    }


    // 查询线索已经售卖次数列表
    public function SearchClueBuyNUm()
    {
        $post = Request::post();
        if (!isset($post['clue_id'])) return error(304, '参数错误', null);
        $sql = "SELECT `buy_num`,`payment_time`,IFNULL(notes_name,nickname) as user_name FROM `order_list` a 
                LEFT JOIN `user` b  ON a.openid = b.openid
                WHERE  `flat` not in (7,8,9)  AND `clue_id` = '${post['clue_id']}'";
        $res = Db::query($sql);
        if (!$res) {
            return error(304, '没有数据', null);
        }
        return success(200, '获取成功', $res);

    }


    // 验证手机号是否存在 和 是否有效
    public function Phonecheck()
    {
        $post = Request::post();
        if (!isset($post['phone_number'])) {
            return error(304, '参数错误', null);
        }

        $clue = new \app\model\Clue();
        $phone_res = $clue->where('phone_number', $post['phone_number'])->find();
        if (!empty($phone_res)) {
            return error(304, '当前手机号码已存在', null);
        }

        $phoneState = $this->batchUcheck($post['phone_number']);
        if (!$phoneState) {
            return error(304, '请输入有效得到手机号码', null);
        }

        return success(200, '手机正确', null);
    }


    // 查询线索的通话录音
    public function DetailPhoneRecording()
    {
        $post = Request::post();

        $Recording = Db::table('notifyurl');
        $res = $Recording->where([['out_trade_no', '=', $post['clue_id']], ['status', '=', 1]])->field('record_file_url')->find();
        if (!$res) {
            return error(304, '当前线索没有通话录音', null);
        }
        return success(200, '获取成功', $res);

    }


    // 用户后台管理查看线索
    public function AdminClueDataList()
    {

        $post = Request::post();
        $pageSize = $post['pageSize'] ?? 10;
        $pageNumber = $post['pageNumber'] ?? 1;
        $pageCount = ($pageNumber - 1) * $pageSize;
        $token = decodeToken();
        $sql = "SELECT 
                    province,city,cart_type,user_name,sex,phone_number,a.createtime,flag,sales,Tosell,unitPrice_1,unitPrice_2,unitPrice_3
                FROM 
                    (SELECT * FROM clue UNION SELECT * FROM  clue_old) a
                LEFT JOIN 
                    (SELECT t_city.id,t_province.name as province,t_city.name as city FROM t_province LEFT JOIN t_city ON t_province.id = t_city.province_id) as c 
                ON c.id = a.cityID WHERE openid = '$token->id' ORDER BY createtime DESC LIMIT  $pageCount ,$pageSize";

        $clue = new \app\model\Clue();
        $total = $clue->where('openid', $token->id)->count();
        $res = Db::query($sql);
        if (!$res) {
            return error(304, '你还没有上传线索', null);
        }
        return success(200, '获取成功', ['data' => $res, 'total' => $total]);
    }


    // 线索推荐
    public function ClueRecommended()
    {

        $post = Request::post();

        $sql = "SELECT a.clue_id,sales,a.CartBrandID,a.cityID,a.cart_type,Tosell,CONCAT(user_name,IF(sex = 1 ,'先生','女士')) as user_name , IF(sex = 1 ,'男','女') as sex ,
                                CONCAT_WS('*********',substring(a.phone_number, 1, 3),
                                substring(a.phone_number, 12, 4)) as Cluephone_number,b.name as cartName,
                                CONCAT(c.`name`,'.',e.`name`) AS provinceCity,
                                ROUND(100 / sales * Tosell) as progress,
                                (UNIX_TIMESTAMP(createtime)*1000) as createtime,
                                (CASE Tosell WHEN 0 THEN unitPrice_1  WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END) as Price,
                                IFNULL(notes_name,nickname) as nclueName ,h.total as upClueNum,h.allTotal,CEIL(((h.total/h.allTotal)*100)) as percentage 
								FROM (SELECT * FROM clue WHERE clue_id != '${post['clue_id']}'  ORDER BY cityID = ${post['cityID']} DESC , provinceID = ${post['provinceID']} DESC LIMIT 10) a 
                                LEFT JOIN t_car_brand b ON a.CartBrandID = b.id
                                LEFT JOIN t_province c ON  a.provinceID = c.id
                                LEFT JOIN t_city e ON  a.cityID = e.id
								LEFT JOIN (SELECT g.openid, COUNT(CASE WHEN g.flag = 1 THEN 1 END) as total,COUNT(g.openid) as allTotal  FROM clue g GROUP BY openid) h ON h.openid = a.openid
                                left JOIN user f ON a.openid = f.openid";

        $res = Db::query($sql);

        if (!$res) {
            return error(304, '没有数据', null);
        }
        return success(200, '获取成功', $res);

    }


    // 批量购买 --- 查询线索
    public function Bulkbuying()
    {

        $where = $this->BulkbuyingWhere();
        $post = Request::post();

        $page = pageData($post);

        $sql = "SELECT 
                    clue_id,user_name,sex,PhoneBelongingplace,cart_type,sales,Tosell,
                    (CASE Tosell WHEN 0 THEN unitPrice_1  WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END) as unitPrice_1,
                    b.city,b.province,record_file_url,d.`name` as brand,
                    CONCAT_WS('****',substring(a.phone_number, 1, 3),substring(a.phone_number, 8, 4)) as phone_number
                FROM (SELECT * FROM clue UNION SELECT * FROM clue_old) a
                    LEFT JOIN (SELECT a.name as province,b.`name` as city,b.id,b.province_id FROM t_province a LEFT JOIN  t_city b  ON a.id = b.province_id) b ON b.id =  a.cityID
                    LEFT JOIN t_car_brand d ON d.id = a.CartBrandID
                    LEFT JOIN notifyurl c ON c.out_trade_no = clue_id WHERE $where  ORDER BY  a.id DESC  LIMIT ${page['pageCount']},${page['pageSize']} ";
        $res = Db::query($sql);

        $sqlCount = "SELECT COUNT(clue_id) as total from (SELECT * FROM clue UNION SELECT * FROM clue_old) a WHERE  $where";
        $total = Db::query($sqlCount);


        if ($res) {
            return success(200, '获取成功', ['data' => $res, 'total' => $total[0]['total']]);
        } else {
            return error(304, '没有筛选的数据', null);
        }
    }

    // 计算筛选价格的数据
    public function price_compute()
    {
        $post = Request::post();
        $where = $this->BulkbuyingWhere();
        $sql = "SELECT clue_id,(unitPrice_1+ unitPrice_2 + unitPrice_3) as totalPrice,cart_type,sales,Tosell,
                (CASE Tosell WHEN 0 THEN unitPrice_1  WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END) as unitPrice_1
                from (SELECT * FROM clue UNION SELECT * FROM clue_old) a  WHERE $where";
        $res = Db::query($sql);
        $total = 0;
        foreach ($res as $item) {
            $total += $item['unitPrice_1'];
        }
        return success(200, '获取成功', $total * 100);
    }


    // 创建临时订单
    private function CreateTemporaryOrder()
    {
        $out_trade_no = ordernum();//订单号
        $token = decodeToken();// 获取用户 token
        $where = $this->BulkbuyingWhere();
        $sql = "SELECT clue_id,(unitPrice_1+ unitPrice_2 + unitPrice_3) as totalPrice,a.openid,cart_type,sales,Tosell,
                (CASE Tosell WHEN 0 THEN unitPrice_1  WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END) as price
                from (SELECT * FROM clue UNION SELECT * FROM clue_old) a  WHERE $where";
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

//    提交订单
    public function SubmitOrder($data,$attach='123')
    {
        $Weixin = Config::get('WeixinConfig.Weixin');
//        $data = $this->CreateTemporaryOrder();
        // 生成微信订单 并保存 用于后期根据 订单号查询数据 并支付 ============= 开始
        $shop = [
            'description' => '汽车线索互助联盟',
            'out_trade_no' => $data['out_trade_no'],
            'amount' => ['total' => $data['priceTotal'] * 100], //订单总金额，单位为分
            'payer' => ['openid' => $data['openid']],  //用户标识,用户在直连商户appid下的唯一标识
            'attach' => $attach, //附加数据,在查询API和支付通知中原样返回
        ];
        // 获取用户创建订单的  PrepayId
        $notify_url = $Weixin['notify_url'] . 'notifyBatch/' . $data['out_trade_no']; // 支付成功反馈 后端
        $payment = new Payment();
        $prepay_id = $payment->getPrepayId($shop, $notify_url);
        if (!$prepay_id) {
            return error(304, '获取失败', null);
        }
        // 创建临时订单
        $res = Db::table('order_temporary')->insertAll($data['TemporaryOrderData']);
        if (!$res) {
            return error(304, '创建订单失败', null);
        }

        $this->SubInventory($data['TemporaryOrderData']);
        // 反馈 数据给前端调用 支付
        $payment = new Payment();
        $prepayData = $payment->payConfig($prepay_id)->getData();
        $prepayData['notofyurl'] = $Weixin['notify_url'] . 'errorNotifyBath/' . $data['out_trade_no'];
//        $prepayData['notofyurl'] = $notify_url;
        \think\facade\Queue::later(60, 'app\job\OrderBatchPayment@Task1', ['out_trade_no' => $data['out_trade_no']], null);

        return json($prepayData);
    }

    // 立即下单购买 并返回前端调用jsdk 所需的参数
    public function BuyNow()
    {
        $data = $this->CreateTemporaryOrder();
        return $this->SubmitOrder($data,'123456');
    }

    // 批量减库存 创建订单 并减去库存
    private function SubInventory($TemporaryOrderData)
    {
        foreach ($TemporaryOrderData as $item) {
            $type = $item['cart_type'] == '1' ? 'clue' : 'clue_old';
            Db::table($type)
                ->where('clue_id', $item['clue_id'])
                ->save(['Tosell' => Db::raw('Tosell+' . $item['buy_num'])]);
        }
    }


    /**
     * 批量购买支付成功后返回状态
     * @param $name string 订单号
     * @return
     */
    public function notifyBatch($name)
    {
        $post = Request::post();
        $AesUtil = new AesUtil();
        $row = $AesUtil->decryptToString($post['resource']['associated_data'], $post['resource']['nonce'], $post['resource']['ciphertext']);
        $row = json_decode($row, true);
        trace($row);
        $res = Db::table('order_temporary')->where('out_trade_no', $name)->select();
        $order = new \app\model\Order();
        $insertData = [];
        foreach ($res as $item) {
            //  支付成功后需要将所有的数据更改为已修改状态 避免超时任务再次修改
            Db::table('order_temporary')->where([['clue_id', '=', $item['clue_id']], ['out_trade_no', '=', $name]])->save(['state' => 1]);
            $rif = $order->where([['clue_id', '=', $item['clue_id']], ['out_trade_no', '=', $name]])->find();
            if ($rif) continue;
            $insertData[] = [
                'out_trade_no' => $name,
                'clue_id' => $item['clue_id'],
                'openid' => $row['payer']['openid'],
                'buy_num' => 1,
                'price' => $item['price'],
                'payment_time' => date("Y-m-d H:i:s"),
                'flat' => 1,
                'up_openid' => $item['up_openid'],
                'transaction_id' => $row['transaction_id'],
                'cart_type' => $item['cart_type'],
            ];
        }
        $res = $order->insertAll($insertData);
        if($row['attach'] == 'cart'){
            Db::table('shop_cart')->where('openid',$row['payer']['openid'])->delete();
        }

        if ($res) {
            return json_encode([
                "code" => "SUCCESS",
                "message" => "成功"
            ]);
        }
    }

    // 支付失败的时候，调用，修改状态 前端
    public function errorNotifyBath($name)
    {
        $res = Db::table('order_temporary')->where([['out_trade_no', '=', $name], ['state', '=', 0]])->select()->toArray();
        if ($res) {
            foreach ($res as $item) {
                $type = $item['cart_type'] == '1' ? 'clue' : 'clue_old';
                Db::table($type)->where('clue_id', $item['clue_id'])->save(['Tosell' => Db::raw('Tosell-' . $item['buy_num'])]);
                Db::table('order_temporary')->where([['clue_id', '=', $item['clue_id']], ['out_trade_no', '=', $item['out_trade_no']]])
                    ->save(['state' => 1]);
            }
        }
        try {
            (new Payment())->closeOutTradeNo($name);
        } catch (\Exception $e) {
            trace($e->getMessage());
        }
    }


    // 批量购买条件 拼接 sql
    public function BulkbuyingWhere()
    {
        $post = Request::post();
        $token = decodeToken();

        $where = " a.flag = 1 AND Tosell < sales AND a.openid != '$token->id'";

        // 汽车类型
        !empty($post['cartType']) ? $where .= " AND cart_type = ${post['cartType']}" : $where .= " AND cart_type = 1";

        // 城市数据
        if (!empty($post['cityId'])) {
            $cityStr = implode("','", $post['cityId']);
            $where .= " AND a.cityID in ('$cityStr')";
        }

        // 品牌数据
        if (!empty($post['brandId'])) {
            $cityStr = implode("','", $post['brandId']);
            $where .= " AND a.CartBrandID  in ('$cityStr') ";
        }

        // 最低价格
        if (!empty($post['minPric'])) $where .= " AND unitPrice_1 >= '${post['minPric']}'";
        // 最高价格
        if (!empty($post['maxPric'])) $where .= " AND unitPrice_1 <= '${post['maxPric']}'";

        // 时间范围
        if (!empty($post['activeDate'])) {
            $date = explode(' - ', $post['activeDate']);
            $where .= " AND a.createtime BETWEEN '$date[0]' AND '$date[1]'";
        }
        return $where;
    }


    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
