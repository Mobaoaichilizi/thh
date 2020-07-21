<?php
// +----------------------------------------------------------------------
// | 广告管理页面
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class AdvController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->adv = D("Adv");
		$this->setting = D("Setting");
	}
    public function index(){
		$title = I('request.title');
		$adv_id = I('request.adv_id');
		if($title){
			$where['title'] = array('like',"%$title%");
		}
		if($adv_id){
			$where['adv_id'] = array('eq',$adv_id);
		}
		$count=$this->adv->where($where)->count();
		$page = $this->page($count,11);
		$list = $this->adv
            ->where($where)
            ->order("sort asc,id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($list as $row)
		{
			$row['category_name']=$this->setting->where('id='.$row['adv_id'])->getField('title');
			$prolist[]=$row;
		}
		$categories = $this->setting->where('parentid=32')->order('sort asc')->select();
		$this->assign('categories',$categories);
		$this->assign("page", $page->show());
		$this->assign("list",$prolist);
        $this->display('index');
    }
	public function addAdv()
	{
		if(IS_POST){
			if($this->adv->create()!==false) {
				if ($result!==false) {
					$result=$this->adv->add();
					$this->success("添加成功！", U("Adv/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
		}else
		{
			$catelist=$this->setting->where('status=1 and parentid=32')->order('sort asc')->select();
			$this->assign('catelist',$catelist);
			$this->display('addAdv');
		}
	}
	public function editAdv()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->adv->create()!==false) {
				if ($result!==false) {
					$result=$this->adv->where("id=".$id)->save();
					$this->success("编辑成功！", U("Adv/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->adv->where("id=".$id)->find();
			$this->assign('result',$result);
			
			
			$catelist=$this->setting->where('status=1 and parentid=32')->order('sort asc')->select();
			$this->assign('catelist',$catelist);
			
			$this->display('editAdv');
		}
	}
	public function deladv()
	{
		$id = I('post.id',0,'intval');
		if ($this->adv->delete($id)!==false) {
			$this->success("删除成功！", U("Adv/index"));
		} else {
			$this->error("删除失败！");
		}
	}
	
}
?>