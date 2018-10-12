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
class DoctorbaseController extends BaseController {
   public function __construct() {
		parent::__construct();
		$time=time();
		// $this->host="http://192.168.0.200";
		$this->assign("js_debug",APP_DEBUG?"?v=$time":"");
		// $this->host_url = "http://gz.51dade.com/";
		$this->host_url = "http:/api.sxgzyl.com/";
   }
   function _initialize(){
		parent::_initialize();
		$version=$_REQUEST['version'];
		accessVersionCheck($version);//版本控制
   }
}
?>