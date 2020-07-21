<?php
// +----------------------------------------------------------------------
// | 系统配置
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Chain\Controller;
use Common\Controller\ChainbaseController;
class SystemController extends ChainbaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->chain = D("Chain");
		$this->chainuser = D("ChainUser");
		$this->chainid=session('chain_id');
		$this->userid=session('user_id');
	}
    public function index()
	{
		$result=$this->chain->where("id=".$this->chainid)->find();
		$this->assign("result",$result);
        $this->display('index');
    }
	public function dopost()
	{
		$id = I("post.id",0,'intval');
		if($this->chain->create()!==false) {
		if ($result!==false) {
			$result=$this->chain->where("id=".$id)->save();
			$this->success("保存成功！", U("System/index"));
		}else
		{
			$this->error('保存失败!');
		}
		}
	}
	public function editPwd()
	{
		if(IS_POST)
		{
			$id = I("post.id",0,'intval');
			$pwd = I("post.pwd");
			$password = I("post.password");
			if($pwd!=$password)
			{
				$this->error('两次输入的密码不同！');
			}
			$data=array(
				'password' => sp_password($pwd),
			);
			$result=$this->chainuser->where("id=".$id)->save($data);
			if($result!==false)
			{
				$this->success('修改成功！');
			}else
			{
				$this->error('修改失败！');
			}
		}else
		{
			
			$this->display("editPwd");
		}
	}

}
?>