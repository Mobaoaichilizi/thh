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
		$this->member = D("Member");

	}

	//列表显示
    public function index(){
    	$description = I('post.description');//所属分类
		
		$where['description'] = array('like',"%$description%");
		
		$count=$this->recipe->where($where)->count();
		$page = $this->page($count,11);
		$recipe = $this->recipe
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($recipe as $key => $value) {
			

        	$value1 = $this->member->join('lgq_user on lgq_user.id = lgq_member.user_id')->where('lgq_user.id='.$value['member_id'])->field('name,username')->find();
        	$value['doctorusername'] = $value1['username'];
	    	$value['doctorname'] = $value1['name'];
       
       
	    	
        	$userst[]=$value;
        }
        // dump($userst);
		$this->assign("page", $page->show());
		$this->assign("list",$userst);
        $this->display('index');
    }
    
  
	

}