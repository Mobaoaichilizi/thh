<?php

// +----------------------------------------------------------------------

// | 教师管理文件

// +----------------------------------------------------------------------

// | Copyright (c) 2017-2020 All rights reserved.

// +----------------------------------------------------------------------

// | Author: thh <752106024@qq.com>

// +----------------------------------------------------------------------

namespace app\admin\controller;



use app\common\controller\AdminBasic;

use app\admin\model\Teacher as TeacherModel;

use think\Db;

use think\Url;



class Teacher extends AdminBasic

{

    /**

     * 获取数据

     * @param int $page

     * @param null $limit

     * @return mixed

     * @throws \think\Exception

     * @throws \think\db\exception\DataNotFoundException

     * @throws \think\db\exception\ModelNotFoundException

     * @throws \think\exception\DbException

     */

    public function index($page = 1, $limit = null)

    {

        if ($this->request->param('act') == 'getData') {

            $param = input('get.');

            $map = [];

            if (!empty($param['name'])) {

                $map['name'] = ['like', "%" . $param['name'] . "%"];

            }

            $count = TeacherModel::where($map)->count();

            $res = TeacherModel::Where($map)->page($page, $limit)->order("sort asc,id asc")->select();

            foreach ($res as $key => &$value) {

                $value['count_vote'] = Db::name("Vote")->Join('dade_teacher','dade_vote.teacher_id = dade_teacher.id')->where("teacher_id=".$value['id'])->count();

            }

            unset($data);

            $data['code'] = 0;

            $data['msg'] = '获取成功！';

            $data['count'] = $count;

            $data['data'] = $res;

            outJson($data);

        }

        return $this->fetch();

    }



    /**

     * 新增

     */

    public function add()

    {

        if ($this->request->isPost()) {

            $param = input('post.');

            $result = TeacherModel::insertTeacher($param);

            if ($result['code'] == 1) {

                $this->success("添加成功！", Url::build("Teacher/index"));

            } else {

                $this->error($result['msg']);

            }



        } else {



            return $this->fetch();

        }

    }



    /**

     * 编辑

     */

    public function edit()

    {

        if ($this->request->isPost()) {

            $param = input('post.');
            
            $result = TeacherModel::updateTeacher($param);

            if ($result['code'] == 1) {

                $this->success("编辑成功！", Url::build("Teacher/index"));

            } else {

                $this->error($result['msg']);

            }

        } else {

            $id = input('get.id');

            $result = Db::name('Teacher')->where("id=" . $id)->find();

            $this->assign('result', $result);

            return $this->fetch();

        }

    }



    /**

     * 删除

     */

    public function del()

    {

        $id = input('post.id');

        $result = TeacherModel::where("id=" . $id)->find();

        if (Db::name('Teacher')->delete($id) !== false) {

            $this->success("删除成功！");

        } else {

            $this->error("删除失败！");

        }

    }





}

