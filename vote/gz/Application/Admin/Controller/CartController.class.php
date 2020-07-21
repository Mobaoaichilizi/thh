<?php
// +----------------------------------------------------------------------
// | 购物车管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class CartController extends AdminbaseController {
	protected $cart;
	
	public function _initialize() {
		parent::_initialize();
		$this->cart = D("Cart");
		$this->user = D("User");
		$this->goods = D("Goods");
	}
    public function index(){
		$count=$this->cart->count();
		$page = $this->page($count,11);
		$list = $this->cart
            ->order("createtime DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($list as $row)
		{
			$row['user_name']=$this->user->where('id='.$row['user_id'])->getField('username');
			$row['goods_name']=$this->goods->where('id='.$row['goods_id'])->getField('title');
			$listt[]=$row;
		}
		$this->assign("page", $page->show());
		$this->assign("list",$listt);
        $this->display('index');
    }
	
}
?>