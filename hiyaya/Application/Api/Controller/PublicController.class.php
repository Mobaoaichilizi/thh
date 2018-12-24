<?php
// +----------------------------------------------------------------------
// | 会员登录
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\BaseController;
class PublicController extends BaseController {
	//登录界面
    public function login(){
        $user_id=session('user_id');
		if(!empty($user_id))
		{
			redirect(U("Manager/index/index"));
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
		$user=D('ShopUser');
		$result = $user->where("username='".$username."' and state=1")->find();
		if(!empty($result))
		{
			if($result['password']==sp_password($password))
			{
				session('user_id',$result['id']);
				session('user_name',$result["username"]);
				session('shop_id',$result["shop_id"]);
                session('chain_id',$result["chain_id"]);
                session('role_id',$result["role_id"]);
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
    	session('user_id',null); 
		session('user_name',null);
		session('shop_id',null);
		session('role_id',null);
		session('chain_id',null);
    	redirect(U('Public/login'));
		
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
        $this->success('缓存清除成功',U('index/welcome'));
    }
}
?>