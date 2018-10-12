<?php
// +----------------------------------------------------------------------
// | Copyright (c) lgq All rights reserved.
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
use JPush\Client as JPushClient;
/**
 * 首页
 */
class JpushController extends UserbaseController {
	
	function _initialize() {
		//vendor('JPush.JPush');
		//$token=$_REQUEST['token'];
		//accessTokenCheck($token);//身份验证
		parent::_initialize();
		$this->app_key = 'c1edae5a67d1c8018ed822dc';
		$this->master_secret = '532e12b89db6869641a1a0c5';
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
		$device[] = '866985035736805';
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
		$content = "亲,有新的版本，请注意更新！";
		$device[] = 'D540CAC914404179910C62515DEDDA1D';
		// $extra = array("type" => "1", "type_id" => 16,'user_type' => 1,'status' => 1);
		$extra = array("type" => "7", "type_id" => 'http:/api.sxgzyl.com/index.php?g=Home&m=download&a=index','user_type' => 1,'status' => 1);
		$audience='{"alias":'.json_encode($device).'}';
		$extras=json_encode($extra);
		$os='ios';
		$res=jpush_send($title,$content,$audience,$os,$extras);
		print_r($res);
		
	}
	
	
}


