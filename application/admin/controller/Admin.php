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
use app\admin\model\Admin as AdminModel;
use think\Db;
use think\Url;

class Admin extends AdminBasic
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
    public function index($page = 1, $limit = null){
        if($this->request->param('act')=='getData')
        {
            $param = input('get.');
            $map = [];
            if(!empty($param['account'])) {
                $map['admin_login'] = ['like', "%" . $param['account'] . "%"];
            }
            if(!empty($param['name'])) {
                $map['admin_name'] = ['like', "%" . $param['name'] . "%"];
            }
            $count = AdminModel::where($map)->count();
            $res = AdminModel::Where($map)->page($page, $limit)->order("id desc")->select();
            foreach ($res as &$val)
            {
                if($val['role_id']==0)
                {
                    $val['role_name'] = '系统超级管理员';
                }else
                {
                    $val['role_name'] = Db::name('Role')->where('id='.$val['role_id'])->column('name');
                }

            }
            unset($data);
            $data['code']=0;
            $data['msg']='获取成功！';
            $data['count'] = $count;
            $data['data']=$res;
            outJson($data);
        }
        return $this->fetch();
    }
    /**
     * 新增
     */
    public function add()
    {
        if($this->request->isPost())
        {
            $param = input('post.');
            $result=AdminModel::insertAdmin($param);
            if($result['code']==1)
            {
                $this->success("添加成功！", Url::build("Admin/index"));
            }else
            {
                $this->error($result['msg']);
            }

        }else
        {
            $resultRole = Db::name("Role")->where("status=1")->order("sort asc")->select();
            $this->assign('resultRole',$resultRole);
            return $this->fetch();
        }
    }
    /**
     * 编辑
     */
    public function edit()
    {
        if($this->request->isPost())
        {
            $param = input('post.');
            $result=AdminModel::updateAdmin($param);
            if($result['code']==1)
            {
                $this->success("编辑成功！", Url::build("Admin/index"));
            }else
            {
                $this->error($result['msg']);
            }
        }else
        {
            $id = input('get.id');
            $result=Db::name('Admin')->where("id=".$id)->find();
            $this->assign('result',$result);
            $resultRole = Db::name("Role")->where("status=1")->order("sort asc")->select();
            $this->assign('resultRole',$resultRole);
            return $this->fetch();
        }
    }
    /**
     * 删除
     */
    public function del()
    {
        $id = input('post.id');
        $result = AdminModel::where("id=".$id)->find();
        if($result['role_id']==0)
        {
            $this->error("不能删除系统管理员！");
        }
        if (Db::name('Admin')->delete($id)!==false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }


}
