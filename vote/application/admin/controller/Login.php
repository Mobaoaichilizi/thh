<?php
// +----------------------------------------------------------------------
// | 会员登录
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Url;
use think\Db;
use app\admin\model\LoginLog as LoginLogModel;

class Login extends Controller
{
	//登录界面
	public function index()
	{
		$admin_id=session('admin_id');
		if(!empty($admin_id))
		{
			$this->redirect(Url::build("admin/index/index"));
		}else
		{
			return $this->fetch();
		}
		
	}
	//登陆提交
	public function dologin()
	{
		$username = input('post.username');
    	if(empty($username)){
    		$this->error('请输入用户名！');
    	}
    	$password = input('post.password');
    	if(empty($password)){
    		$this->error('请输入密码！');
    	}
		$verify = input('post.verify');
    	if(empty($verify)){
    		$this->error('请输入验证码！');
    	}
		if(!sp_check_verify_code($verify))
		{
			$this->error('验证码错误！'.$verify);
		}
		$user=Db::name('Admin');
		$result = $user->where("admin_login='".$username."' and status=1")->find();
		if(!empty($result))
		{
			if($result['admin_pwd']==sp_password($password))
			{
                LoginLogModel::insertLog($username,'1','该帐号登陆成功！');
				session('admin_id',$result['id']);
				session('admin_name',$result["admin_login"]);
				$res['last_login_ip']=Request::instance()->ip();
    			$res['last_login_time']=time();
				$user->where("id=".$result['id'])->update($res);
				$this->success('登录成功！',Url::build('Index/index'));
				
			}else
			{
                LoginLogModel::insertLog($username,'-1','该帐号密码错误');
				$this->error('您输入的密码错误！');
			}
		}else{
            LoginLogModel::insertLog($username,'-1','该帐号不存在');
			$this->error('该用户不存在！');
		}
		
	}
	public function verify()
	{
		$verify = new \think\Verify(array('imageW'=>300,'fontSize'=>30));
        $verify->entry(1);
	}
}
