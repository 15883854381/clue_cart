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

        $UpDataArray = [];
        foreach ($Data as $item) {
            $rse = $this->moneyVali($item, $token->id);
            if ($rse['code'] != 200) {
                $rse['data']['error_type'] = 1;
                $rse['data']['error'] = $rse['mes'];
            }
            $UpDataArray[] = $rse['data'];
        }

        if (empty($UpDataArray)) {
            return error(304, '上传失败', null);
        }
        $redis = new Redis(Config::get('cache.stores.redis'));
        $res = $redis->set('UpDataArray', $UpDataArray, 7200);
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

        $clue = new \app\model\Clue();
        $phone = $clue->where('phone_number', $item['phone_number'])->find();
        if ($phone) {
            return array('code' => 1, 'mes' => '该手机号码已存在', 'data' => $item);
        }


        // 判断价格是否正确
        if (is_int($item['sales'])) {

            if ($item['sales'] < 0 || $item['sales'] > 3) {
                return array('code' => 1, 'mes' => "售卖次数不正确", 'data' => $item);
            }

            $priceKey = 0;

            for ($i = 1; $i < $item['sales'] + 1; $i++) {
                if (empty($item['Price_' . $i])) {
                    return array('code' => 1, 'mes' => "价格未填写完整", 'data' => $item);
                }

                if ($i != 1) {
                    if ($item['Price_' . $i] > $priceKey) {
                        return array('code' => 1, 'mes' => "价格错误，价格不能比之前的高", 'data' => $item);
                    }
                }
                $item['unitPrice_' . $i] = $item['Price_' . $i];
                $item['clue_id'] = Uuid::uuid6()->getHex()->toString();;
                $priceKey = $item['Price_' . $i];
            }
        } else {
            return array('code' => 1, 'mes' => "价格参数错误", 'data' => $item);
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
        $redis = new Redis(Config::get('cache.stores.redis'));
        $res = $redis->get('UpDataArray');
        if (!$res) {
            return error(304, '没有数据', null);
        }
        return success(200, '获取成功', $res);

    }

    // 确认批量上传
    public function queryBatch()
    {

        $post = Request::post();
        if (!isset($post['type'])) {
            return error(304, '参数不完整', null);
        }
        $data = explode(',', $post['data']);

        $redis = new Redis(Config::get('cache.stores.redis'));

        $subData = $redis->get('UpDataArray');
        $itemDatas = [];
        foreach ($data as $item) {
            foreach ($subData as $it) {
                if (!isset($it['clue_id'])) {
                    continue;
                }
                if ($item == $it['clue_id']) {
                    $itemDatas[] = $it;
                }
            }
        }
        Db::startTrans();
        try {

            $clue = new \app\model\Clue();
            $res = $clue->strict(false)->insertAll($itemDatas);
            Db::commit();
            if ($res) {
                $redis = new Redis(Config::get('cache.stores.redis'));
                $redis->delete('UpDataArray');
                return success(200, '上传成功', $res);
            } else {
                return error(304, '上传失败', $data);
            }
        } catch (\Exception $e) {
            Db::rollback();
            if ($e->getCode() == 10501) {
                return error(304, '上传失败', null);
            }
            return error(304, '上传失败', $e->getCode());
        }


//        Db::startTrans();
//        try {
//            $redis = new Redis(Config::get('cache.stores.redis'));
//            $data = $redis->get('UpDataArray');
//            Log::info($data[0]);
//            $clue = new \app\model\Clue();
//
//            $res = $clue->strict(false)->insertAll($data);
//
////            // 提交事务
//            Db::commit();
//            if ($res) {
//                return success(200, '上传成功', $res);
//            } else {
//                return error(304, '上传失败', $data);
//            }
//
//        } catch (\Exception $e) {
//            // 回滚事务
//            Db::rollback();
//            return error(304, '上传失1败', $e->getMessage());
//        }
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
            'sales' => '售卖次数',
            'Price_1' => '【1】次价',
            'Price_2' => '【2】次价',
            'Price_3' => '【3】次价',
            'PhoneBelongingplace' => '号码归属地',
            'cart_type' => '汽车类型',
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


}