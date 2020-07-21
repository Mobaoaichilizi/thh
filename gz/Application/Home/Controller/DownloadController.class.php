<?php

namespace Home\Controller;
use Common\Controller\HomebaseController;
class DownloadController extends HomebaseController {
	public function _initialize() {
		$this->version = M('version');
		
		

	}
	public function index(){
		// $id= I('get.id');
		// $info = $this->version->where('id='.$id)->find();
		
		// $this->assign('info',$info);
		$this->display();
	}

   
   


}