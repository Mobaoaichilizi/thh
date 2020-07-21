<?php
// +----------------------------------------------------------------------
// | Copyright (c) lgq All rights reserved.
// +----------------------------------------------------------------------
namespace Masseur\Controller;
use Common\Controller\BaseController;

/**
 * 首页
 */
class JpushController extends BaseController {
	
	function _initialize() {
		//vendor('JPush.JPush');
		//$token=$_REQUEST['token'];
		//accessTokenCheck($token);//身份验证
		parent::_initialize();
		$this->app_key = '59e81ce7067e1cb5f6525ee8';
		$this->master_secret = 'c3087eb57873ce8ff58c8b74';
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
    ->addRegistrationID(array('863612043966819'))
	->addAndroidNotification('点击进入查看', '您有一个新的订单，请注意查看',0,array('type'=>1))
	->setOptions(100000, 3600, null, false)
    ->send();
	print_r($result);
    }
	public function jpush()
	{
	
		$title="这是一个系统消息";
		$content = "这是一个系统消息";
		$device[] = '863026032834054';
		$extra = array("push_type" => 1, "age" => 16);
		$audience='{"alias":'.json_encode($device).'}';
		$extras=json_encode($extra);
		$os='android';
		$res=jpush_send($title,$content,$audience,$os,$extras);
		print_r($res);
		
	}
}


