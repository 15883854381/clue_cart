<?php

namespace app\controller;

use app\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Ramsey\Uuid\Uuid;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\cache\driver\Redis;
use think\facade\Log;
use think\response\Json;
use WpOrg\Requests\Requests as http;

class AdminClue extends BaseController
{

    // 查询线索
    public function Clue_list()
    {
        $post = Request::instance()->post();
        $pageSize = @$post['pageSize'] ?? 10;
        $pageNumber = @$post['pageNumber'] ?? 1;
        $pageCount = ($pageNumber - 1) * $pageSize;
        $where = '1';
        if (isset($post['flag'])) {
            $where .= " AND flag = " . $post['flag'];
        }

        $sql = "SELECT a.clue_id,a.cart_type,CONCAT(user_name,IF(sex = 1 ,'先生','女士')) as user_name ,a.phone_number,PhoneBelongingplace,createtime,sales,Tosell,flag,b.`name` as brand,
                unitPrice_1,unitPrice_2,unitPrice_3,f.nickname,
                CONCAT(c.`name`,'.',e.`name`) as address,
                (unitPrice_1 + unitPrice_2+unitPrice_3) as amount 
                FROM  (SELECT * FROM clue UNION SELECT * FROM clue_old) a 
                    LEFT JOIN `user` f ON a.openid = f.openid
                LEFT JOIN t_car_brand b ON a.CartBrandID = b.id
                LEFT JOIN t_province c ON a.provinceID = c.id
                LEFT JOIN t_city e ON a.cityID = e.id WHERE $where
                 ORDER BY createtime DESC LIMIT $pageCount,$pageSize ";

        $clue = new \app\model\Clue();
        $oldClue = new \app\model\OldCart();
        $newCartcount = $clue->count();
        $oldCartCount = $oldClue->count();

        $res = DB::query($sql);
        return success(200, '查询成功', ['count' => ($newCartcount + $oldCartCount), 'data' => $res]);

    }


    // 获取线索总数量
    function ClueCount()
    {
        $clue = new \app\model\Clue();
        $count = $clue->count();
        return success(200, '获取成功', ['count' => $count]);
    }

    // 修改线索状态
    public function EditClueFlag()
    {
        $post = Request::instance()->post();

        if (!isset($post['clue_id']) || !isset($post['type'])) {
            return error('304', '参数错误', null);
        }

        try {
            if (intval($post['flag']) > 3 || intval($post['flag']) < 0) {
                return error('304', '参数异常，不要乱搞', null);
            }
        } catch (\Exception $e) {
            return error('305', '参数异常，不要乱搞', null);
        }

        $clue = new \app\model\Clue();
        $oldclue = new \app\model\OldCart();
        $DataB = $post['type'] == 1 ? $clue : $oldclue;

        $res = $DataB->where('clue_id', $post['clue_id'])->find();
        $res->flag = $post['flag'];
        $des = $res->save();
        if (!$des) {
            return error('304', '修改失败', null);
        }

        return success(200, '修改成功', null);

    }

    // 批量上传
    function batchUp()
    {
        $file = request()->file('file');
        if (!$file) {
            print_r('请选择需要导入的文件');
            die;
        }
        $Data = self::ReadExcel($file);

        // 判断是否有数据
        if (empty($Data)) {
            return error(304, '上传失败', null);
        }
        $token = decodeToken();

        $phoneNumber = [];
        $UpDataArray = [];
        foreach ($Data as $item) {
            if (empty($item['phone_number'])) {
                continue;
            }
            $phoneFalse = in_array($item['phone_number'], $phoneNumber);
            if ($phoneFalse) {
                continue;
            }


            $rse = $this->moneyVali($item, $token->id);
            if ($rse['code'] != 200) {
                $rse['data']['error_type'] = 1;
                $rse['data']['error'] = $rse['mes'];
            }
            $phoneNumber[] = $item['phone_number'];
            $UpDataArray[] = $rse['data'];
        }


        if (empty($UpDataArray)) {
            return error(304, '上传失败', null);
        }
        $redis = new Redis(Config::get('cache.stores.redis'));
        $res = $redis->set('UpDataArray' . $token->id, $UpDataArray, 7200);
        if (!$res) {
            return error(304, '上传失败', null);
        }
        return success(200, '上传成功', $UpDataArray);
    }

    // 验证上传时的数据是否正确
    private function moneyVali($item, $openid)
    {

        $item['unitPrice_1'] = 0;
        $item['unitPrice_2'] = 0;
        $item['unitPrice_3'] = 0;
        $item['openid'] = $openid;
        $item['error_type'] = 0;
        $item['error'] = '';


        $sql = "SELECT phone_number FROM (SELECT phone_number FROM clue UNION SELECT phone_number FROM clue_old) c WHERE c.phone_number = '${item['phone_number']}'";
        $phone = Db::query($sql);
        if ($phone) {
            return array('code' => 1, 'mes' => '该手机号码已存在', 'data' => $item);
        }

        if(empty($item['cart_type'])){
            return array('code' => 1, 'mes' => '没有定义线索类型 1 表示新车  2 表示二手车', 'data' => $item);
        }

        // 验证价格 和 售卖次数

        $item['sales'] = 0;
        for ($i = 1; $i <= 3; $i++) {

            if (empty($item['Price_' . $i])) {
                if ($i == 1) {
                    return array('code' => 1, 'mes' => "至少填写一次价格", 'data' => $item);
                }
                break;
            }

            if ($i != 1) {
                if ($item['Price_' . $i] > $priceKey) {
                    return array('code' => 1, 'mes' => "价格错误，价格不能比之前的高", 'data' => $item);
                }
            }
            $item['unitPrice_' . $i] = $item['Price_' . $i];
            $item['clue_id'] = Uuid::uuid6()->getHex()->toString();;
            $priceKey = $item['Price_' . $i];
            $item['sales'] += 1;
        }


        // 判断性别
        $item['sex'] = $item['sex'] ?? 1;


        try {
            validate(\app\validate\Clue::class)->scene('batchUp')->check($item);
        } catch (\Exception $e) {
            return array('code' => 1, 'mes' => $e->getMessage(), 'data' => $item);
        }

        // 判断号码归属地
        if (empty($item['PhoneBelongingplace'])) {
            $ulits = new Ulits($this->app);
            $phone_data = $ulits->batchUcheck($item['phone_number']);
            if (empty($phone_data)) {
                return array('code' => 1, 'mes' => '手机验证失败(平台)', 'data' => $item);
            }
            $item['PhoneBelongingplace'] = $phone_data['area'];
        }

        return array('code' => 200, 'mes' => null, 'data' => $item);
    }

    // 查询批量上传后存放在redis里面的数据
    public function SelectUpdata()
    {
        $token = decodeToken();
        $redis = new Redis(Config::get('cache.stores.redis'));
        $res = $redis->get('UpDataArray' . $token->id);
        if (!$res) {
            return error(304, '没有数据', null);
        }
        return success(200, '获取成功', $res);

    }

    // 确认批量上传 选中
    public function queryBatch()
    {

        $token = decodeToken();
        $post = Request::post();
        $redis = new Redis(Config::get('cache.stores.redis'));
        $subData = $redis->get('UpDataArray' . $token->id);
        $new_cart = [];
        $old_cart = [];

        // 选中的数据
        if (isset($post['data'])) {
            // 上传用户选中的数据
            $data = explode(',', $post['data']);
            foreach ($data as $item) {
                foreach ($subData as $it) {
                    if (!isset($it['clue_id'])) {
                        continue;
                    }
                    if ($item == $it['clue_id']) {
                        $CityAndBrand = [];
                        // 获取城市 ID
                        if (!empty($it['buyCarCity']) || !empty($it['carBrand'])) {
                            $CityAndBrand = $this->CityOrBrand($it['buyCarCity'], $it['carBrand']);
                        }
                        if ($it['cart_type'] == 1) {
                            $new_cart[] = array_merge($it, $CityAndBrand);
                        } elseif ($it['cart_type'] == 2) {
                            $old_cart[] = array_merge($it, $CityAndBrand);
                        }
                    }
                }
            }
        } else {
            // 批量上传
            foreach ($subData as $it) {
                if (!isset($it['clue_id'])) {
                    continue;
                }
                if ($it['error_type'] == 1) continue;
                $CityAndBrand = [];
                if (!empty($it['buyCarCity']) || !empty($it['carBrand'])) {
                    $CityAndBrand = $this->CityOrBrand($it['buyCarCity'], $it['carBrand']);
                }
                if ($it['cart_type'] == 1) {
                    $new_cart[] = array_merge($it, $CityAndBrand);
                } elseif ($it['cart_type'] == 2) {
                    $old_cart[] = array_merge($it, $CityAndBrand);
                }
            }
        }

        if (empty($new_cart) && empty($old_cart)) {
            return error(304, '没有数据', null);
        }

        $clue = new \app\model\Clue();
        $oldClue = new \app\model\OldCart();


        $num = 100;//每次导入条数

        // 旧车
        $limit_old = ceil(count($old_cart) / $num);
        for ($i = 1; $i <= $limit_old; $i++) {
            $offset = ($i - 1) * $num;
            $data = array_slice($old_cart, $offset, $num);
            $res = $oldClue->strict(false)->insertAll($data);
        };

        // 新车
        $limit_new = ceil(count($new_cart) / $num);
        for ($i = 1; $i <= $limit_new; $i++) {
            $offset = ($i - 1) * $num;
            $data = array_slice($new_cart, $offset, $num);
            $res = $clue->strict(false)->insertAll($data);
        };

        if (!$res) {
            return error(304, '上传失败', $data);
        }

        $redis = new Redis(Config::get('cache.stores.redis'));
        $redis->delete('UpDataArray' . $token->id);
        return success(200, '上传成功', $res);
    }


    // 获取城市 和 品牌的id
    private function CityOrBrand($buyCarCity, $carBrand)
    {
        $ulits = new Ulits($this->app);
        $data = ['provinceID' => null, 'cityID' => null, 'CartBrandID' => null];
        if (!empty($buyCarCity)) {
            $res = $ulits->FuzzyQueriesCity($buyCarCity);
            if ($res) {
                $data['provinceID'] = $res[0]['province_id'];
                $data['cityID'] = $res[0]['id'];
            }
        }
        // 获取品牌的 ID
        if (!empty($carBrand) && preg_match("/[\x7f-\xff]/", $carBrand)) {

            $res = $ulits->FuzzyQueriesCarBrand($carBrand);
            if ($res) {
                $data['CartBrandID'] = $res[0]['b_id'];
            }
        }

        return $data;
    }


    // 解析excel 数据
    function ReadExcel($file)
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();


        $dictionary = [
            'user_name' => '姓',
            'phone_number' => '手机号码',
            'sex' => '性别',
            'Price_1' => '【1】次价',
            'Price_2' => '【2】次价',
            'Price_3' => '【3】次价',
            'PhoneBelongingplace' => '号码归属地',
            'cart_type' => '汽车类型',
            'buyCarCity' => '购车地区',
            'carBrand' => '汽车品牌',
        ];
        $title = [];
        $data = [];
        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex = $row->getRowIndex();
            $cellIterator = $row->getCellIterator();
            $row = [];
            $i = 0;
            foreach ($cellIterator as $cell) {
                if ($rowIndex == 1) {
                    $resFlase = array_search($cell->getValue(), $dictionary);
                    if ($resFlase) {
                        $title[] = $resFlase;
                    }
                } else {
                    $row[$title[$i]] = $cell->getValue();
                    $i += 1;
                }
            }
            if ($rowIndex == 1) continue;
            $data[] = $row;
        }
        return $data;
    }


    // 线索均分 给客服
    function Clue_allocation()
    {
        $request = Request::instance();
        if (!$request->isPost()) {
            return error(304, '请求出错', null);
        }
        $post = $request->post();
        if (!isset($post['userid'])) {
            return error(304, '参数错误', null);
        }
        $user_list = explode(',', $post['userid']); // 此处以后需要做安全验证  判断数据库是否存在这些数据


        $sql = "SELECT a.clue_id FROM (SELECT clue_id,flag FROM clue UNION SELECT clue_id,flag FROM clue_old) a 
                LEFT JOIN admin_customer b ON a.clue_id = b.clue_id WHERE b.clue_id is null AND a.flag = 2";
        $res = Db::query($sql);

        $resCount = count($res);
        $userListCount = count($user_list);

        if ($resCount <= 0) {
            return error(304, "以无可以分配的线索", null);
        }
        if ($userListCount > $resCount) {
            return error(304, "当前选中的客服数量【大于】 线索数量，不能平均分配线索", null);
        }

        //region description 均分算法

        // 数组均分算法 将数据平均分配给 用户 不能整除的分配给最后一个用户
        $arr = [];
        $user_num = 0;// 取user 数组
        $item_num = intval($resCount / $userListCount); // 每个人的数量
        for ($i = 1; $i <= $userListCount; $i++) {
            $pagecount = ($i - 1) * $item_num;
            $new_mini = [];
            if ($i == $userListCount) {
                $mini_arr = array_slice($res, $pagecount, $resCount); // 最后一次循环 将所有的数据添加到 最后一个用户
            } else {
                $mini_arr = array_slice($res, $pagecount, $item_num);
            }
            foreach ($mini_arr as $item) {
                $item['admin_id'] = $user_list[$user_num];
                $new_mini[] = $item;
            }
            $user_num += 1;
            $arr[] = $new_mini;
        }
        //endregion

        // 已分配完成 上传数据
        $adminCustomer = new \app\model\adminCustomer();
        foreach ($arr as $item) {
            $adminCustomer->insertAll($item);
        }


        return success(200, '分配完成', $arr);


    }


    // 外呼线索列表 ========================

    // 线索列表审核
    function Clue_list_Audit()
    {
        $adminClue = new \app\model\AdminClue();
        $res = $adminClue->outbound_clue();
        if (!$res) {
            return error(304, '没有数据', null);
        }
        return success(200, '获取成功', $res);

    }

    // 审核线索拨打电话
    function Clue_CallPhone()
    {
        $token = decodeToken();
        if (!$token) {
            return error(304, '非法访问', null);
        }

        $request = Request::instance();
        if (!$request->isPost()) {
            return error(304, '请求出错', null);
        }

        $post = $request->post();
        if (!isset($post['clue_id'])) {
            return error(304, '参数错误', null);
        }


        $clue = new \app\model\Clue();
        $res = $clue->where('clue_id', $post['clue_id'])->find();
        if (!$res) {
            return error(304, '没有数据', null);
        }

        $phone = \think\facade\Config::get('WeixinConfig.phone');
        $updata = [
            "AccessKey" => $phone['accesskey'],
            "TelA" => $token->id,//主动发起人
            "TelX" => $phone['TelX'],// 中间人
            "TelB" => $res['phone_number'],// 被动发起人
            "Expiration" => 15,
            "NotifyUrl" => $phone['NotifyUrl'] . 'CluePhoneNotifyUrl/' . $res['clue_id'],
            "Signature" => strtoupper(md5($phone['accesskey'] . $token->id . $phone['TelX'] . $res['phone_number'] . $phone['appSecret']))
        ];
        $response = http::post($phone['url'] . '/api/call/bind', [], $updata);
        $res = json_decode($response->body, true);
        if ($res['code'] != 0) {
            return error(304, $res['msg'], null);
        }
        return success(200, '电话接通中,请注意接听', null);
    }

    // 电话接通后的回调地址 Clue_CallPhone
    function Clue_Phone_NotifyUrl($clueId)
    {
        try {
            $request = Request::instance()->post();
            $request['out_trade_no'] = $clueId;// 此处的 out_trade_no 为线索ID
            if ($request['bind_id']) {
                Db::table('notifyurl')->save($request);
                return json(["code" => "0", "message" => "success"]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    // 线索详情
    function Clue_Item_Detail()
    {

    }


    // 批量上传时，数据不完善 客服审核时 完善线索数据
    function EditClueData()
    {


        $request = Request::instance();
        if (!$request->isPost()) {
            return error(304, '请求出错', null);
        }
        $post = $request->post();

        if (!isset($post['flag'])) {
            return error(304, '参数错误', null);
        }
        if (!isset($post['cart_type'])) {
            return error(304, '参数错误', null);
        }

        // 新车 和 二手车 的验证
        if ($post['cart_type'] == 1) {
            $clue = new \app\model\Clue();
        } elseif ($post['cart_type'] == 2) {
            $clue = new \app\model\OldCart();
        } else {
            return error(304, '参数错误', null);
        }
        // 只有审核通过的情况下 才做数据的完全验证
        if ($post['flag'] == 1) {
            try {
                validate(\app\validate\Clue::class)
                    ->scene('edit')
                    ->check($post);
            } catch (\Exception $e) {
                return error(304, $e->getMessage(), null);
            }
        }
        $PriceType_res = $clue->where('clue_id', $post['clue_id'])->find(); //

        $updata = [
            'user_name' => $post['user_name'],
            'CartBrandID' => $post['CartBrandID'],
            'cityID' => $post['cityID'],
            'provinceID' => $post['provinceID'],
            'sex' => $post['sex'],
        ];
        // 状态为【1】且未改过价格
        if ($post['flag'] == 1 && $PriceType_res['PriceType'] == 0) {
            $updata['flag'] = 4;// 未改价 者为待上线 直到全部审核完成
        } else {
            $updata['flag'] = $post['flag']; // 改过价格则为正常审核状态
        }


        $res = $clue->where('clue_id', $post['clue_id'])->save($updata);// 数据库做更改
        if ($res === false) {
            return error(304, '修改失败', null);
        }


        // 上传标签
        $this->upTages($post);
        // 如果日期是当天则不做价格的更改
        if (date('Y-m-d', strtotime($PriceType_res['createtime'])) != date('Y-m-d')) {
            $this->Price_compute(); // 在此处计算有效率 并做出修改
        }

        // 修改录音
        if (!empty($post['notifyurlid'])) {
            $notifyurl = Db::table('notifyurl');
            $notifyurl->where('out_trade_no', $post['clue_id'])->save(['flag' => 0]);
            $notifyurl->where('id', $post['notifyurlid'])->save(['flag' => 1]);
        }

        return success(200, '修改成功', null);
    }

    // 上传tags 标签
    private function upTages($post)
    {
        // 上传标签
        if (isset($post['tages'])) {
            $tages = Db::table('tagsmap');
            $tages->where('clue_id', $post['clue_id'])->delete();
            $upDataArr = [];
            foreach ($post['tages'] as $item) {
                $upDataArr[] = ['clue_id' => $post['clue_id'], 'tags_id' => $item];
            }
            Db::table('tagsmap')->insertAll($upDataArr);
        }
    }

    // EditClueData
    private function Price_compute()
    {
        $request = Request::instance();
        $post = $request->post();

        // 新车 和 二手车 的验证
        if ($post['cart_type'] == 1) {
            $clue = new \app\model\Clue();
        } elseif ($post['cart_type'] == 2) {
            $clue = new \app\model\OldCart();
        } else {
            return error(304, '参数错误', null);
        }

        $dataRes = $clue->where('clue_id', $post['clue_id'])->find();// 查询单挑线索 查出所需数据


        // 正在审核线索的数量
        $where = $this->SelectWhere($dataRes, ['flag', '=', '2']);
        $CountRes = $clue->where($where)->count(); // 正在审核的线索总数
        if ($CountRes > 0) return false;

        // 有效线索的数量  有效线索 应 包括 4 待上线 1 审核通过
        $where_percentage = $this->SelectWhere($dataRes, ['flag', 'in', ['4', '1']]);
        $successCount = $clue->where($where_percentage)->count(); // 有效线索的数量

        // 线索总数量
        $AllClueWhere = $this->SelectWhere($dataRes);
        $Allres = $clue->where($AllClueWhere)->select();// 所有的线索

        $count = count($Allres);// 线索数量
        // 计算有效率  数量÷总数×100
        $percentage = ($successCount / $count); // 有效率

        foreach ($Allres as $item) {
            if ($item['PriceType'] == 1) {
                continue;
            }
            if ($item['flag'] == 4) {
                $updateArr = [];
                $updateArr['PriceType'] = 1;
                $updateArr['unitPrice_1'] = ceil($item['unitPrice_1'] + 2 / $percentage); //计算得出的价格
                $updateArr['flag'] = $item['flag'] == 4 ? 1 : $item['flag'];
                $clue->where('clue_id', $item['clue_id'])->update($updateArr);
            }
        }
    }

    // SelectWhere
    private function SelectWhere($dataRes, $arr = [])
    {
        $startDate = date('Y-m-d', strtotime($dataRes['createtime'])) . ' 00:00:00';
        $endDate = date('Y-m-d', strtotime($dataRes['createtime'])) . ' 23:59:59';
        $data = [
            ['createtime', 'between', [$startDate, $endDate]],
            ['openid', '=', $dataRes['openid']],
        ];
        if ($arr) {
            $data[] = $arr;
        }

        return $data;
    }

    // 获取单条 tages
    public function singularTags()
    {
        $post = Request::post();
        if (!isset($post['clue_id'])) {
            return error(304, '', null);
        }
        $sql = "SELECT  a.sortid,t.tags_id FROM tagsmap t LEFT JOIN tags a ON a.id = t.tags_id WHERE clue_id = '${post['clue_id']}'";
        $res = Db::query($sql);
        if (!$res) {
            return error(304, '', null);
        }

        $downArr = [];
        foreach ($res as $item) {
            $downArr[] = [$item['sortid'], $item['tags_id']];
        }
        return success(200, '修改成功', $downArr);
    }


    // 定时任务修改
    public function timingEdit()
    {
        $sql = "SELECT openid,DATE_FORMAT(createtime, '%Y-%m-%d') as createtime FROM  (SELECT * FROM  clue UNION SELECT * FROM clue_old) a  WHERE flag = 4 GROUP BY openid ,createtime";
        $UserTime = Db::query($sql); // 查询有待上线线索的用户 和 日期
        if (!$UserTime) {
            return false;
        }

        // 半天为一个单位 用于遍历sql语句
        $timeArr = [];
        foreach ($UserTime as $item) {
            $timeArr[] = ['openid' => $item['openid'], 'startTime' => $item['createtime'] . ' 00:00:00', 'endTime' => $item['createtime'] . ' 12:00:00'];
            $timeArr[] = ['openid' => $item['openid'], 'startTime' => $item['createtime'] . ' 12:00:00', 'endTime' => $item['createtime'] . ' 23:59:59'];
        }

        foreach ($timeArr as $item) {
            // 此处验证是否存在 有未审核的数据 有则跳过
            $sql = "SELECT COUNT(id) as total FROM  (SELECT * FROM  clue UNION SELECT * FROM clue_old) a  WHERE createtime BETWEEN '${item['startTime']}' and '${item['endTime']}' and openid = '${item['openid']}'";
            $isFlagSql = $sql . ' and flag = 2'; // 查询所有 状态 等于 2 的线索
            $res = Db::query($isFlagSql); // COUNT
            // 如果此处有等于 2 的数据 就跳出
            if ($res[0]['total'] > 0) {
                continue;
            }
            // 查询所有的数据 的数量
            $resAll = DB::query($sql);  // COUNT


            // 获取 状态等于 4 的线索
            $flag_4 = "SELECT * FROM  (SELECT * FROM  clue UNION SELECT * FROM clue_old) a  WHERE  createtime BETWEEN '${item['startTime']}' and '${item['endTime']}' and openid = '${item['openid']}' and flag = 4";
            $flagRes4 = Db::query($flag_4);
            // 如果没有等于 4  线索就跳出
            if (!$flagRes4) {
                continue;
            }


            $ClueCount = $resAll[0]['total']; // 所有的数据
            $fla4Count = count($flagRes4);// 等于 4 的线索数量
            $clueEfficient = round($fla4Count / $ClueCount, 2); // 有效率

            // 只更改等于 4 的数据
            foreach ($flagRes4 as $itm) {
                if ($itm['cart_type'] == 1) {
                    $clue = new \app\model\Clue();
                } else {
                    $clue = new \app\model\OldCart();
                }
                $itm['unitPrice_1'] = ceil($itm['unitPrice_1'] + 2 / $clueEfficient);
                $update = ['unitPrice_1' => $itm['unitPrice_1'], 'flag' => 1, 'PriceType' => 1];
                $clue->where([['clue_id', '=', $itm['clue_id']]])->save($update);
            }
        }


    }


}