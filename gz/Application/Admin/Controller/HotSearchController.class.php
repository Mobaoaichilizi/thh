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
class HotSearchController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->search = D("Search");
	}
    public function index(){
		
		$where['is_hot'] = array('eq',"1");
		$count=$this->search->where($where)->count();
		$page = $this->page($count,11);
		$list = $this->search
            ->where($where)
            ->order("sort asc,id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		$this->assign("page", $page->show());
		$this->assign("list",$list);
        $this->display('index');
    }
	public function addHotSearch()
	{
		if(IS_POST){
			if($this->search->create()!==false) {
				if ($this->search->add()) {
					$this->success("添加成功！", U("search/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
		}else
		{
			$this->display('addHotSearch');
		}
	}
	public function editHotSearch()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->search->create()!==false) {
				if ($result!==false) {
					$result=$this->search->where("id=".$id)->save();
					$this->success("编辑成功！", U("search/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->search->where("id=".$id)->find();
			$this->assign('result',$result);
			
			
			$this->display('editHotSearch');
		}
	}
	public function delHotSearch()
	{
		$id = I('post.id',0,'intval');
		if ($this->search->delete($id)!==false) {
			$this->success("删除成功！", U("search/index"));
		} else {
			$this->error("删除失败！");
		}
	}
	
}
?>