<?php

namespace Home\Controller;
use Common\Controller\HomebaseController;
class ForumController extends HomebaseController {
	public function _initialize() {
		$this->forum = 	M('Forum');
		
		

	}
	public function share(){
		$id= I('get.id');
		$info = $this->forum->where('id='.$id)->find();
		$this->assign('info',$info);
		$this->display();
	}

   
   


}