<?php
// +----------------------------------------------------------------------
// | 商品管理页面
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class GoodsController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->goods = D("Goods");
		$this->setting = D("Setting");
	}
    public function index(){
		$title = I('request.title');
		$status = I('request.status');
		$category_id = I('request.category_id');
		if($title){
			$where['title'] = array('like',"%$title%");
		}
		if($category_id!== ''){
			$where['category_id'] = array('in',$category_id);
		}
		if($status!== ''){
			$where['status'] = array('eq',$status);
		}
		$count=$this->goods->where($where)->count();
		$page = $this->page($count,11);
		$list = $this->goods
            ->where($where)
            ->order("sort asc,id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($list as $row)
		{
			$row['category_name']=$this->setting->where('id='.$row['category_id'])->getField('title');
			$prolist[]=$row;
		}
		$tree = new \Think\Tree();
		$parentid = I("get.parentid",0,'intval');
		$result = $this->setting->order(array("sort" => "ASC"))->select();
		foreach ($result as $r) {
			$r['selected'] = $r['id'] == $parentid ? 'selected' : '';
			$array[] = $r;
		}
		$str = "<option value='\$id' \$selected>\$spacer \$title</option>";
		$tree->init($array);
		$categories = $tree->get_tree(14, $str);
		$this->assign("page", $page->show());
		$this->assign("list",$prolist);
		$this->assign("categories",$categories);
        $this->display('index');
    }
	public function addGoods()
	{
		if(IS_POST){
	
			if($this->goods->create()!==false) {
				if ($result!==false) {
					$this->goods->label=implode(',',$_POST['label']);
					$result=$this->goods->add();
					admin_log('add','goods',$_POST['title']);
					$this->success("添加成功！", U("Goods/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
		}else
		{
			$tree = new \Think\Tree();
			$parentid = I("get.parentid",0,'intval');
			$result = $this->setting->order(array("sort" => "ASC"))->select();
			foreach ($result as $r) {
				$r['selected'] = $r['id'] == $parentid ? 'selected' : '';
				$array[] = $r;
			}
			$str = "<option value='\$id' \$selected>\$spacer \$title</option>";
			$tree->init($array);
			$select_categorys = $tree->get_tree(14, $str);
			$this->assign("select_categorys", $select_categorys);
			
			$lable=$this->setting->where("parentid=134 and status=1")->order("sort asc")->select();
			$this->assign("lable", $lable);
			$this->display('addGoods');
		}
	}
	public function editGoods()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->goods->create()!==false) {
				if ($result!==false) {
					$this->goods->label=implode(',',$_POST['label']);
					$result=$this->goods->where("id=".$id)->save();
					admin_log('edit','goods',$_POST['title']);
					$this->success("编辑成功！", U("Goods/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->goods->where("id=".$id)->find();
			$this->assign('result',$result);
			
			
			$tree = new \Think\Tree();
			$parentid = $result['category_id'];
			$result = $this->setting->order(array("sort" => "ASC"))->select();
			foreach ($result as $r) {
				$r['selected'] = $r['id'] == $parentid ? 'selected' : '';
				$array[] = $r;
			}
			$str = "<option value='\$id' \$selected>\$spacer \$title</option>";
			$tree->init($array);
			$select_categorys = $tree->get_tree(14, $str);
			$this->assign("select_categorys", $select_categorys);
			
			$lable=$this->setting->where("parentid=134 and status=1")->order("sort asc")->select();
			$this->assign("lable", $lable);
			
			$this->display('editGoods');
		}
	}
	public function delGoods()
	{
		$id = I('post.id',0,'intval');
		$title=$this->goods->where("id=".$id)->getField("title");
		admin_log('del','goods',$title);
		if ($this->goods->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
	public function operateGoods()
	{
		$id = I('post.id',0,'intval');
		$status = I('post.status',0,'intval');
		$dataarray=array(
			'id' => $id,
			'status' => $status,
		);
		if ($this->goods->save($dataarray)!==false) {
			$this->success("修改成功！");
		} else {
			$this->error("修改失败！");
		}
	}
}
?>