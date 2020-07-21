<?php
// +----------------------------------------------------------------------
// | 用户管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2020 All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\controller\AdminBasic;
use app\admin\model\User as UserModel;
use app\admin\model\Department as DepartmentModel;
use think\Db;
use think\Url;

class User extends AdminBasic
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
            if (!empty($param['account'])) {
                $map['username'] = ['like', "%" . $param['account'] . "%"];
            }
            if (!empty($param['name'])) {
                $map['name'] = ['like', "%" . $param['name'] . "%"];
            }
            $count = UserModel::where($map)->count();
            $res = UserModel::Where($map)->page($page, $limit)->order("id desc")->select();
            foreach ($res as &$val) {
                $val['department_name'] = Db::name('Department')->where('id=' . $val['department_id'])->column('name');
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
			$array=array(
				'telephone' => $param['phone'],
				'password' => md5($param['password']),
				'time' => time(),
				'nickname' => $param['username'],
				'secret' => md5('wlgc706466'.time())
			);
			$result_im = curlGet("https://www.1386.tech:18092/user/register?telephone=".urlencode($array['telephone'])."&password=".urlencode($array['password'])."&time=".urlencode($array['time'])."&nickname=".urlencode($array['nickname'])."&secret=".urlencode($array['secret']));
            $im_array = json_decode($result_im,true);
			if($im_array['resultCode']!=1)
			{
				$this->error($im_array['resultMsg']);
			}else
			{
				$param['im_id'] = $im_array['data']['userId'];
			}
			$result = UserModel::insertUser($param);
            if ($result['code'] == 1) {
                $this->success("添加成功！", Url::build("User/index"));
            } else {
                $this->error($result['msg']);
            }

        } else {
			$department = new DepartmentModel();
            $department->getDepartmentList(0, '');
            $resultDepartment = $department->departmentList;
			$this->assign('resultDepartment', $resultDepartment);
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
            $result = UserModel::updateUser($param);
            if ($result['code'] == 1) {
                $this->success("编辑成功！", Url::build("User/index"));
            } else {
                $this->error($result['msg']);
            }
        } else {
            $id = input('get.id');
            $result = Db::name('User')->where("id=" . $id)->find();
            $this->assign('result', $result);
            $department = new DepartmentModel();
            $department->getDepartmentList(0, '');
            $resultDepartment = $department->departmentList;
			$this->assign('resultDepartment', $resultDepartment);
            return $this->fetch();
        }
    }

    /**
     * 删除
     */
    public function del()
    {
        $id = input('post.id');
        $result = UserModel::where("id=" . $id)->find();
        if (Db::name('User')->delete($id) !== false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }


}
