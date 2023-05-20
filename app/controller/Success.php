<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Request;

class Success extends BaseController
{

    // 增加案例
    public function AddCase()
    {

        $post = Request::instance()->post();
        $post['content'] = htmlspecialchars($post['content']);
        $DB = DB::table('success_case');
        $case = $DB->insertGetId($post);
        if ($case) {
            return success(200, '添加成功', $case);
        }
        return error(304, '添加失败', null);
    }

    // 删除案例
    public function DeleteCase()
    {

        $post = Request::instance()->post();
        $case = DB::table('success_case')->where('id', $post['id'])->delete();
        if ($case) {
            return success(200, '删除成功', null);
        }
        return error(304, '删除失败', null);
    }


    // 查询案例
    public function SelectCase()
    {
        $case = DB::table('success_case')->page(1, 10)->select()->each(function ($item, $key) {
            $item['content'] = htmlspecialchars_decode($item['content']);
            return $item;
        });
        if (empty(json_decode($case))) {
            return error(304, '没有数据', null);
        }
        return success(200, '获取成功', $case);

    }

    // 修改文档
    public function EditCase()
    {
        $post = Request::instance()->post();
        $post['content'] = htmlspecialchars($post['content']);
        $case = DB::table('success_case')->where('id', $post['id'])->save($post);
        if ($case) {
            return success(200, '修改成功', null);
        }
        return error(304, '修改失败', null);
    }


    function SelectSucessCase()
    {
        $post = Request::instance()->post();

        $res = Db::table('success_case')->when(!empty($post['id']), function ($query) {
            $post = Request::instance()->post();
            $query->where('id', '=', $post['id']);
        }, function ($query) {
            $query->select();
        })->where('flag', 1)->select()->each(function ($item) {
            $item['content'] = htmlspecialchars_decode($item['content']);
            return $item;
        });

        return success(200, '获取成功', $res);


    }

}