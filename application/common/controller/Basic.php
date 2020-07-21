<?php
// +----------------------------------------------------------------------
// | 系统公共文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace app\common\controller;
use think\Controller;
use think\Url;

class Basic extends Controller
{
	
   function _initialize() {
       	$time=time();
       // $this->assign("js_debug",APP_DEBUG?"?v=$time":"");
   }
   /**
     * 检查操作频率
     * @param int $duration 距离最后一次操作的时长
     */
    protected function check_last_action($duration){
    	
    	$action=MODULE_NAME."-".CONTROLLER_NAME."-".ACTION_NAME;
    	$time=time();
    	
    	$session_last_action=session('last_action');
    	if(!empty($session_last_action['action']) && $action==$session_last_action['action']){
    		$mduration=$time-$session_last_action['time'];
    		if($duration>$mduration){
    			$this->error("您的操作太过频繁，请稍后再试~~~");
    		}else{
    			session('last_action.time',$time);
    		}
    	}else{
    		session('last_action.action',$action);
    		session('last_action.time',$time);
    	}
    }
}
