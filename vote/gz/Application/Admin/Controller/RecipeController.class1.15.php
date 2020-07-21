<?php
// +----------------------------------------------------------------------
// | 处方
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class RecipeController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->recipe = D("Recipe");
		$this->pre = D("Pre");
		$this->setting = D("Setting");
		$this->member = D("Member");
		$this->patient = D("Patientmember");
		$this->admin = D("Admin");

	}

	//列表显示
    public function index(){
		$doctor = I('post.doctor');
		$admin_id=session('admin_id');
		$admin_count = $this->admin->where("id=".$admin_id." and is_pharmacy = 1")->count();
    	if($doctor){
	    	$doctor_id = $this->member->where("name='".$doctor."'")->getfield('user_id');
	    	$where['doctor_id'] = array('like',"%$doctor_id%");
    	}
    	if($admin_count > 0){
			$where['admin_pre'] = array('eq',$admin_id);
		}
    	
    	
		$count=$this->pre->where($where)->count();
		$page = $this->page($count,11);
		$pre = $this->pre
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
            foreach ($pre as $key => &$value) {
			

        	$value['doctorname'] = $this->member->where('user_id='.$value['doctor_id'])->getfield('name');
        	$value['patientname'] = $this->patient->where('user_id='.$value['patient_id'])->getfield('name');
	    	$value['type'] = $this->setting->where('id='.$value['setting_id_class'])->getfield('title');
       
        }
        
		
		$this->assign('admin_id',$admin_id);
		$this->assign('admin_count',$admin_count);
		$this->assign("page", $page->show());
		$this->assign("list",$pre);
        $this->display('index');
    }
    
  
	

}