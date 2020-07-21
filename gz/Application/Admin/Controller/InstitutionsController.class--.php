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
class InstitutionsController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->institutions = D("Institutions");
		$this->setting = D("Setting");

	}

	//列表显示
    public function index(){
    	$name = I('post.name');
		if($name){
			$where['name'] = array('like',"%$name%");
		}
		$count=$this->institutions->where($where)->count();
		$page = $this->page($count,11);
		$institutions = $this->institutions
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($institutions as $row)
		{
			$row['category_name']=$this->setting->where('id='.$row['settimg_id'])->getField('title');
			$prolist[]=$row;
		}
		$this->assign("page", $page->show());
		$this->assign("institutionslist",$prolist);
        $this->display('index');
    }
    //添加分类
    public function addInstitutions(){
    
    	if(IS_POST){
    		
			if($this->institutions->create()!==false) {
				if ($institutions!==false) {
					$institutions=$this->institutions->add();
					$this->success("添加成功！", U("Institutions/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$catelist=$this->setting->where('status=1 and parentid=97')->order('sort asc')->select();
			$this->assign('catelist',$catelist);
			$this->display('addInstitutions');
		}
    }
    //修改分类
    public function editInstitutions()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->institutions->create()!==false) {
				if ($institutions!==false) {
					$institutions=$this->institutions->where("id=".$id)->save();
					$this->success("编辑成功！", U("Institutions/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$institutions=$this->institutions->where("id=".$id)->find();
			$this->assign('institutions',$institutions);
			
			$catelist=$this->setting->where('status=1 and parentid=97')->order('sort asc')->select();
			$this->assign('catelist',$catelist);
			
			$this->display('editInstitutions');
		}

		
	}
	//删除分类
	public function delInstitutions()
	{
		$id = I('post.id',0,'intval');
		if ($this->institutions->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}



}