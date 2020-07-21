<?php
// +----------------------------------------------------------------------
// | 优惠券管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class DiscountController extends AdminbaseController {
	protected $discount;
	
	public function _initialize() {
		parent::_initialize();
		$this->discount = D("Discount");
	}
    public function index(){
		$name = I('request.name');

		$is_status = I('request.is_status');
		if($name){
			$sqlwhere.=" and title like '%$name%' ";
		}
		
		if($is_status!=0){
			$sqlwhere.= " and status=".$is_status;
		}
		
		$count=$this->discount->where("1=1 ".$sqlwhere)->count();
		$page = $this->page($count,11);
		$list = $this->discount
            ->where("1=1 ".$sqlwhere)
            ->order("create_time DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		$this->assign("page", $page->show());
		$this->assign("list",$list);
        $this->display('index');
    }
	public function addDiscount()
	{
		if(IS_POST){
			if($this->discount->create()!==false) {
				$result=$this->discount->add();
				if ($result!==false) {
					$this->success("添加成功！", U("Discount/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$this->display('addDiscount');
		}
	}
	public function editDiscount()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->discount->create()!==false) {
				$result=$this->discount->where("id=".$id)->save();
				if ($result!==false) {
					$this->success("编辑成功！", U("Discount/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->discount->where("id=".$id)->find();
			$this->assign('result',$result);
			$this->display('editDiscount');
		}
	}
	public function delDiscount()
	{
		$id = I('post.id',0,'intval');
		if ($this->discount->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
}
?>