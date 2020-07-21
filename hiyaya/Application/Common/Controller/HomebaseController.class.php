<?php
// +----------------------------------------------------------------------
// | APi公共文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Common\Controller;
use Common\Controller\BaseController;
class HomebaseController extends BaseController {
   public function __construct() {
		parent::__construct();
		$time=time();
		$this->assign("js_debug",APP_DEBUG?"?v=$time":"");
   }
   function _initialize(){
		 parent::_initialize();
		 $i=I('get.i');
		 if(empty($i))
		 {
			 $this->error("当前店铺不存在！");
		 }
		 $map    = array('id' => $i);
		 $shop_data   = M('Chain')->where($map)->field('name,address,phone,wx_appid,wx_appsecret')->find();
		 if(!$shop_data)
		 {
			 $this->error("当前店铺不存在！");
		 }
		
		 $host='http://'.$_SERVER['HTTP_HOST'];
		 $this->assign('host',$host);
		 
		 if($_SESSION['user_id']=='')
		 {
		 if(!isset($_GET['code']))
		 {
		 $customeUrl=$host.$_SERVER['REQUEST_URI'];
		 $scope='snsapi_userinfo';
		 $oauthUrl='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$shop_data['wx_appid'].'&redirect_uri='.urlencode($customeUrl).'&response_type=code&scope='.$scope.'&state=oauth#wechat_redirect';
		 header('Location:'.$oauthUrl);
		 exit;
		 }
		 $customeUrl=$host.$_SERVER['REQUEST_URI'];
		 $rt=curlGet('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$shop_data['wx_appid'].'&secret='.$shop_data['wx_appsecret'].'&code='.$_GET['code'].'&grant_type=authorization_code');
		 $jsonrt=json_decode($rt,1);
		 $openid=$jsonrt['openid'];
		 $access_token=$jsonrt['access_token'];
		 $user_rt=curlGet('https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN');
		 $user_jsonrt=json_decode($user_rt,1);
		 $member=M('ShopMember');
		 $count=$member->where("openid='".$user_jsonrt['openid']."'")->count();
		 if($count > 0)
		 {
			$result=$member->where("openid='".$user_jsonrt['openid']."'")->find();
			$_SESSION['wx_user']=$openid;
			$_SESSION['user_id']=$result['id'];
		 }else
		 {
			unset($data);
			$data['openid']= $user_jsonrt['openid'];
			if($data['openid']!='')
			{				
				$_SESSION['wx_user']=$openid;
				header('Location:'.U('Binding/index',array('i' => $i)));
				exit;
			}
		 } 
		 } 	 
	   }
}
?>