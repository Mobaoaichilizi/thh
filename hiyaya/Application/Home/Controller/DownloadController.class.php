<?php
namespace Home\Controller;
use Common\Controller\BaseController;
class DownloadController extends BaseController {
	public function _initialize() {
		parent::_initialize();
	}
	//下载页面
	public function index()
	{
		$this->display();
	}
	//管家下载
	public function housekeeper()
	{
		$this->display();
	}
	
		
}