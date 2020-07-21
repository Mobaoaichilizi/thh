<?php
// +----------------------------------------------------------------------
// | 药材库管理页面
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class SinglehrebsController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->singlehrebs = D("SingleHrebs");
		$this->setting = D("Setting");
		$this->admin = D("Admin");
	}
    public function index(){
    	$hrebs_name = I('request.hrebs_name');
		$admin_id=session('admin_id');
		$yaopu=I('yaopu');
		$setting_id_model=I('setting_id_model');
		$admin_count = $this->admin->where("id=".$admin_id." and is_pharmacy = 1")->count();
		if($admin_count > 0){
			$where['admin_id'] = array('eq',$admin_id); 
		}
		if($yaopu){
			$where['admin_id'] = array('eq',$yaopu);
		}
		if($setting_id_model){
			$where['setting_id_model'] = array('eq',$setting_id_model);
		}
		if($hrebs_name){
			$where['hrebs_name'] = array('like',"%$hrebs_name%");
		}
		$where['lgq_single_hrebs.status'] = array('eq',1);
		$count=$this->singlehrebs->join('lgq_admin on lgq_admin.id=lgq_single_hrebs.admin_id')->field('lgq_admin.admin_name,lgq_single_hrebs.*')->where($where)->count();
		$page = $this->page($count,11);
		$list = $this->singlehrebs
			->join('lgq_admin on lgq_admin.id=lgq_single_hrebs.admin_id')->field('lgq_admin.admin_name,lgq_single_hrebs.*')
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($list as $row)
		{
			$row['category_name']=$this->setting->where('id='.$row['setting_id_model'])->getField('title');
			$prolist[]=$row;
		}
		$categories = $this->admin->where('is_pharmacy=1')->select();
		$models = $this->setting->where('parentid = 49 and status=1')->select();
		$this->assign("page", $page->show());
		$this->assign("list",$prolist);
		$this->assign("admin_count",$admin_count);
		$this->assign("categories",$categories);
		$this->assign("models",$models);
        $this->display('index');
    }
	public function addSinglehrebs()
	{
		if(IS_POST){
			if($this->singlehrebs->create()!==false) {
				if ($result!==false) {
					$result=$this->singlehrebs->add();
					$this->success("添加成功！", U("Singlehrebs/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
		}else
		{
			$catelist=$this->setting->where('status=1 and parentid=49')->order('sort asc')->select();
			$admin_id=session('admin_id');
			$admin_count = $this->admin->where("id=".$admin_id." and is_pharmacy = 1")->count();
			$this->assign('catelist',$catelist);
			$this->assign('admin_id',$admin_id);
			$this->assign('admin_count',$admin_count);
			$this->display('addSinglehrebs');
		}
	}
	public function editSinglehrebs()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->singlehrebs->create()!==false) {
				if ($result!==false) {
					$result=$this->singlehrebs->where("id=".$id)->save();
					$this->success("编辑成功！", U("Singlehrebs/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->singlehrebs->where("id=".$id)->find();
			$this->assign('result',$result);
			
			
			$catelist=$this->setting->where('status=1 and parentid=49')->order('sort asc')->select();
			$this->assign('catelist',$catelist);
			
			$this->display('editSinglehrebs');
		}
	}
	public function delSinglehrebs()
	{
		$id = I('post.id',0,'intval');
		$res = array(
			'id' => $id,
			'status' => 0,
		);
		if ($this->singlehrebs->save($res)!==false) {
			$this->success("删除成功！", U("Singlehrebs/index"));
		} else {
			$this->error("删除失败！");
		}
	}
	
}
?>