<?php
// +----------------------------------------------------------------------
// | Copyright (c) lgq All rights reserved.
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;

/**
 * 首页
 */
class JpushController extends UserbaseController {
	
	function _initialize() {
		//vendor('JPush.JPush');
		//$token=$_REQUEST['token'];
		//accessTokenCheck($token);//身份验证
		parent::_initialize();
		$this->app_key = '2f6929959087b058c59bfac0';
		$this->master_secret = '6b69e59ad4106d9ee3e226fd';
		$this->systemsinfo =D("SystemsInfo");
	}
    //修改头像接口
	public function index() 
	{
		// 初始化
	Vendor('autoload');
	$client = new \JPush\Client($this->app_key, $this->master_secret);
	// 简单推送示例
	$result = $client->push()
    ->setPlatform('all')
    ->addRegistrationID(array('863934039858465'))
	->addAndroidNotification('点击进入查看', '您有一个新的订单，请注意查看',0,array('type'=>1))
	->setOptions(100000, 3600, null, false)
    ->send();
	print_r($result);
    }
	public function jpush()
	{
	
		$title="这是一个系统消息";
		$content = "这是一个系统消息";
		$device[] = '869581020008396';
		$extra = array("type" => "1", "type_id" => 16,'user_type' => 1,'status' => 1);
		$audience='{"alias":'.json_encode($device).'}';
		$extras=json_encode($extra);
		$os='android';
		$res=jpush_send($title,$content,$audience,$os,$extras);
		print_r($res);
		
	}
	public function demo()
	{
	
		$title="这是一个系统消息";
		$content = "这是一个系统消息";
		$device[] = '042E8B4EAB8D4D3799411C73B5289A89';
		$extra = array("type" => "1", "type_id" => 16,'user_type' => 1,'status' => 1);
		$audience='{"alias":'.json_encode($device).'}';
		$extras=json_encode($extra);
		$os='ios';
		$res=jpush_send($title,$content,$audience,$os,$extras);
		print_r($res);
		
	}
	
	
}


