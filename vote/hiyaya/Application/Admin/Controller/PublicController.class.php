<?php
// +----------------------------------------------------------------------
// | 会员登录
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\BaseController;
class PublicController extends BaseController {
	//登录界面
    public function login(){
        $admin_id=session('admin_id');
		if(!empty($admin_id))
		{
			redirect(U("admin/index/index"));
		}else
		{
			$this->display("login");
		}
    }
	public function dologin()
	{
		$username = I("post.username");
    	if(empty($username)){
    		$this->error('请输入用户名！');
    	}
    	$password = I("post.password");
    	if(empty($password)){
    		$this->error('请输入密码！');
    	}
    	$verify = I("post.verify");
    	if(empty($verify)){
    		$this->error('请输入验证码！');
    	}
		if(!sp_check_verify_code($verify))
		{
			$this->error('验证码错误！'.$verify);
		}
		$user=D('admin');
		$result = $user->where("admin_login='".$username."'")->find();
		if(!empty($result))
		{
			if($result['admin_pwd']==sp_password($password))
			{
				session('admin_id',$result['id']);
				session('admin_name',$result["admin_login"]);
				$res['last_login_ip']=get_client_ip(0,true);
    			$res['last_login_time']=time();
				$user->where("id=".$result['id'])->save($res);
				$this->success('登录成功！',U('Index/index'));
				
			}else
			{
				$this->error('您输入的密码错误！');
			}
		}else{
			$this->error('该用户不存在！');
		}
		
	}
	//退出登录
	 public function logout(){
    	session('admin_id',null); 
		session('admin_name',null);
    	redirect(U('Admin/Public/login'));
		
    }
	//验证码
	public function verify()
	{
		$verify = new \Think\Verify(array('imageW'=>300,'fontSize'=>30));
        $verify->entry(1);
	}
	//清楚缓存
	public function clearCache(){
        destroy_dir(RUNTIME_PATH);  //删除缓存目录 
        $this->success('缓存清除成功',U('Admin/index/welcome'));
    }
}
?>