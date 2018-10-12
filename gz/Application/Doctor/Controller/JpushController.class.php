<?php
// +----------------------------------------------------------------------
// | Copyright (c) lgq All rights reserved.
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;

/**
 * 首页
 */
class JpushController extends DoctorbaseController {
	
	function _initialize() {
		//vendor('JPush.JPush');
		//$token=$_REQUEST['token'];
		//accessTokenCheck($token);//身份验证
		parent::_initialize();
		$this->app_key = '1e09113895396900144f0dd7';
		$this->master_secret = '76fda688f46d02354887d9dc';
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
	
    }
	public function jpush_post()
	{
		$title="标题";
		$content = "亲,您有新的回复";
        $device = array('D540CAC914404179910C62515DEDDA1D');
		$extra = array("type" => "1", "type_id" => 5);
		$audience='{"alias":'.json_encode($device).'}';
		$extras=json_encode($extra);
		$os='ios';
		$back_arr=jpush_send($title,$content,$audience,$os,$extras);
		print_r($back_arr);
	}

}


