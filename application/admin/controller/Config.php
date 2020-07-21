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
use app\admin\model\Config as ConfigModel;
use think\Db;
use think\Url;

class Config extends AdminBasic
{
    /**
     * 获取数据
     */
    public function index(){
        if($this->request->isPost())
        {
            $param = input('post.');
            $result=ConfigModel::updateConfig($param);
            if($result['code']==1)
            {
                $this->success("更新成功！", Url::build("Config/index"));
            }else
            {
                $this->error($result['msg']);
            }
        }else
        {
            $result = Db::name("Config")->where("id=1")->find();
            $this->assign('result',$result);
            return $this->fetch();
        }
    }


}
