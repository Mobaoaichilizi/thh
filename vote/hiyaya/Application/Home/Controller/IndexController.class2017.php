<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;
class IndexController extends HomebaseController {
	public function _initialize() {
		parent::_initialize();
		$this->mylabel=M('Mylabel');
		$this->banner=M('Banner');
	}
/*
    public function index(){
			Vendor('jssdk');
			$wx_jssdk = new \JSSDK(C('WX_APPID'),C('WX_APPSECRET'));
			$signPackage = $wx_jssdk->GetSignPackage();
			$this->assign('signPackage',$signPackage);
			$this->display();
	}
  */
	public function index()
	{
		$result=$this->mylabel->order('sort asc')->select();
		$this->assign('result',$result);
		$banner=$this->banner->where("banner_id=2")->order('sort asc')->select();
		$this->assign('banner',$banner);
		$this->display();
	}


}