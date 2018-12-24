<?php
// +----------------------------------------------------------------------
// | 系统配置
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class SystemController extends AdminbaseController {
	protected $config;
	
	public function _initialize() {
		parent::_initialize();
		$this->config = D("config");
	}
    public function index(){
		$web_config=$this->config->where('groupid=1')->order('sort asc')->select();
		$this->assign('web_config',$web_config);
		$email_config=$this->config->where('groupid=2')->order('sort asc')->select();
		$this->assign('email_config',$email_config);
		$sms_config=$this->config->where('groupid=3')->order('sort asc')->select();
		$this->assign('sms_config',$sms_config);
        $this->display('index');
    }
	public function dopost($config)
	{
		if($config && is_array($config)){
			 foreach ($config as $name => $value) {
                $map = array('name' => $name);
                $this->config->where($map)->setField('value', $value);
            }
		}
		$this->success('保存成功！');
	}
	public function editPwd()
	{
		if(IS_POST)
		{
			$id = I("post.id",0,'intval');
			$admin_pass = I("post.admin_pass");
			$password = I("post.password");
			if($admin_pass!=$password)
			{
				$this->error('两次输入的密码不同！');
			}
			$data=array(
				'admin_pwd' => sp_password($admin_pass),
			);
			$result=M('Admin')->where("id=".$id)->save($data);
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