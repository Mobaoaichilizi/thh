<?php
// +----------------------------------------------------------------------
// | 会员登录
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class HospitalController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->hospital = D("hospital");

	}

	//列表显示
    public function index(){
    	$name = I('request.name');
		if($name){
			$where['name'] = array('like',"%$name%");
		}
		$count=$this->hospital->where($where)->count();
		$page = $this->page($count,11);
		$hospital = $this->hospital
            ->where($where)
            ->order("sort asc,id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		$this->assign("page", $page->show());
		$this->assign("hospitallist",$hospital);
        $this->display('index');
    }
    //添加分类
    public function addHospital(){
    
    	if(IS_POST){
    		
			if($this->hospital->create()!==false) {
				if ($hospital!==false) {
					$hospital=$this->hospital->add();
					admin_log('add','hospital',$_POST['name']);
					$this->success("添加成功！", U("Hospital/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			
			$this->display('addHospital');
		}
    }
    //修改分类
    public function editHospital()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->hospital->create()!==false) {
				if ($hospital!==false) {
					$hospital=$this->hospital->where("id=".$id)->save();
					admin_log('edit','hospital',$_POST['name']);
					$this->success("编辑成功！", U("Hospital/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$hospital=$this->hospital->where("id=".$id)->find();
			$this->assign('hospital',$hospital);
			$this->display('editHospital');
		}

		
	}
	//删除分类
	public function delHospital()
	{
		$id = I('post.id',0,'intval');
		$name=$this->hospital->where("id=".$id)->getField("name");
		admin_log('del','hospital',$name);
		if ($this->hospital->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}



}