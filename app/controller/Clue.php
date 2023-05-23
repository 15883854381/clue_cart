<?php

namespace app\controller;

use app\BaseController;
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

        $sql = "SELECT a.clue_id,sales,Tosell,CONCAT(user_name,IF(sex = 1 ,'先生','女士')) as user_name , IF(sex = 1 ,'男','女') as sex ,
                                CONCAT_WS('*********',substring(a.phone_number, 1, 3),
                                substring(a.phone_number, 12, 4)) as Cluephone_number,b.name as cartName,
                                CONCAT(c.`name`,'.',e.`name`) AS provinceCity,
                                ROUND(100 / sales * Tosell) as progress,
                                (UNIX_TIMESTAMP(createtime)*1000) as createtime,
                                (CASE Tosell WHEN 0 THEN unitPrice_1  WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END) as Price,
                                upClueNum,IFNULL(notes_name,nickname) as nclueName FROM clue a 
                                LEFT JOIN t_car_brand b ON a.CartBrandID = b.id
                                LEFT JOIN t_province c ON  a.provinceID = c.id
                                LEFT JOIN t_city e ON  a.cityID = e.id
                                left JOIN user f ON a.openid = f.openid where $where ORDER BY createtime DESC  LIMIT $pageCount,$pageSize  ";
        $version = Db::query($sql);

        $clue = new \app\model\Clue();
        $count = $clue->where($countWhere)->count();
        Log::info($count);


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
            $BuyOrder = $order->where([['clue_id', '=', $post['clue_id']], ['openid', '=', $token->id]])->order('payment_time', 'DESC')->find();
        }
        Log::info($BuyOrder);

        if (isset($BuyOrder['flat'])) {
            if ($BuyOrder['flat'] == 1 || $BuyOrder['flat'] == 6) {
                $res = $clue->CluePhone($post['clue_id'], $post['type']);
            } else {
                $res = $clue->ClueNotPhone($post['clue_id'], $post['type']);
            }
        } else {
            $res = $clue->ClueNotPhone($post['clue_id'], $post['type']);
        }
//        // 结束
//        if ($BuyOrder['flat'] == 1 || $BuyOrder['flat'] == 7) {
//            $res = $clue->CluePhone($post['clue_id'], $post['type']);
//        } else {
//            $res = $clue->ClueNotPhone($post['clue_id'], $post['type']);
//        }
        $res[0]['flat'] = $BuyOrder['flat'];
        $clue_id = $post['clue_id'];
        $tags_sql = "SELECT tagName FROM tagsmap a LEFT JOIN tags b ON a.tags_id = b.id WHERE clue_id = '${clue_id}'";
        $tags = Db::query($tags_sql);
        $res[0]['tags'] = $tags;
        return success(200, '查询成功', $res);

    }


    // TODO 删除线索数据
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


        // TODO 检测手机号码是否存在 === 开始 用于上传时 检测是否有效
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
        Log::info($post);
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
        Log::info("数据=============");
        Log::info($res);
        if ($res) {
            return success(200, '获取成功', $res);
        }
        return error(304, '获取授权信息失败', $res);
    }


    // 查询线索已经售卖次数列表
    public function SearchClueBuyNUm()
    {
        $older = new \app\model\Order();
        $post = Request::post();
        if (!isset($post['clue_id'])) return error(304, '参数错误', null);
        $sql = "SELECT `buy_num`,`payment_time`,IFNULL(notes_name,nickname) as user_name FROM `order_list` a 
                LEFT JOIN `user` b  ON a.openid = b.openid
                WHERE  `flat` = 1 AND `clue_id` = '${post['clue_id']}'";
        $res = Db::query($sql);
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

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
