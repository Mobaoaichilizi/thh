<?php
// +----------------------------------------------------------------------
// | 系统首页文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\controller\AdminBasic;
use think\Request;
use think\Url;
use think\Db;

class Index extends AdminBasic
{
    public function index()
    {
        $admin = Db::name('Admin');
        $admin_id = session('admin_id');
        $admin_list = $admin->where('id=' . $admin_id)->find();
        $this->assign('admin_list', $admin_list);
        return $this->fetch();
    }

    public function welcome()
    {
        return $this->fetch();
    }

    //修改密码
    public function editPwd(Request $request)
    {
        if ($request->isPost()) {
            $oldpwd = input('post.oldPwd');
            $password = input('post.newPwd');
            $newpwdd = input('post.newPwd1');
            if ($password != $newpwdd) {
                $this->error('两次输入的密码不同！');
            }
            $data = array(
                'admin_pwd' => sp_password($password),
            );
            $result = Db::name('Admin')->where("id=" . session('admin_id'))->update($data);
            if ($result !== false) {
                $this->success('修改成功！');
            } else {
                $this->error('修改失败！');
            }
        } else {
            return $this->fetch('editpwd');
        }
    }

    //退出登陆
    public function logout()
    {
        session('admin_id', null);
        session('admin_name', null);
        $this->success('退出成功！');
    }

    //清除缓存
    public function clearCache()
    {
        destroy_dir('../runtime');  //删除缓存目录 
        $this->success('缓存清除成功', Url::build('Admin/index/welcome'));
    }
}
