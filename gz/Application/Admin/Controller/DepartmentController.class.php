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
class DepartmentController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->department = D("department");

	}

	//列表显示
    public function index(){
    	$name = I('request.name');
		if($name){
			$where['name'] = array('like',"%$name%");
		}
		$count=$this->department->where($where)->count();
		$page = $this->page($count,11);
		$department = $this->department
            ->where($where)
            ->order("sort asc,id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		$this->assign("page", $page->show());
		$this->assign("departmentlist",$department);
        $this->display('index');
    }
    //添加分类
    public function addDepartment(){
    
    	if(IS_POST){
    		
			if($this->department->create()!==false) {
				if ($department!==false) {
					$department=$this->department->add();
					admin_log('add','department',$_POST['name']);
					$this->success("添加成功！", U("Department/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			
			$this->display('addDepartment');
		}
    }
    //修改分类
    public function editDepartment()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->department->create()!==false) {
				if ($department!==false) {
					$department=$this->department->where("id=".$id)->save();
					admin_log('edit','department',$_POST['name']);
					$this->success("编辑成功！", U("Department/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$department=$this->department->where("id=".$id)->find();
			$this->assign('department',$department);
			$this->display('editDepartment');
		}

		
	}
	//删除分类
	public function delDepartment()
	{
		$id = I('post.id',0,'intval');
		$name=$this->department->where("id=".$id)->getField("name");
		admin_log('del','department',$name);
		$res = array(
			'id' => $id,
			'status' => 0,
		);
		if ($this->department->save($res)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}



}