<?php
// +----------------------------------------------------------------------
// | 店铺管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Chain\Controller;
use Common\Controller\ChainbaseController;
class ShopController extends ChainbaseController {
	protected $shop;
	
	public function _initialize() {
		parent::_initialize();
		$this->shop = D("Shop");
		$this->chain = D("Chain");
		$this->chainid=session('chain_id');
	}
    public function index()
	{
        $this->display('index');
    }
	public function get_index()
	{
		$name = I('request.name');
		$limit = I('request.limit');
		$page = I('request.page');
		if($page==1)
		{
			$page=0;
		}else
		{
			$page=($page-1)*$limit;
		}
		$where['chain_id']=$this->chainid;
		if($name){
			$where['shop_name'] = array('like',"%$name%");
		}
		$count=$this->shop->where($where)->count();
		$shoplist = $this->shop
            ->where($where)
            ->order("id DESC")
            ->limit($page.','.$limit)
            ->select();
		foreach($shoplist as &$val)
		{
			$val['createtime']=date("Y-m-d H:i:s",$val['createtime']);
			$val['state'] = $val['state']==1 ? '启用' : '禁用';
		}
		unset($data);
		$data['code']=0;
		$data['msg']=$page;
		$data['count']=$count;
		$data['data']=$shoplist;
		outJson($data);
	}
	public function addShop()
	{
		if(IS_POST){
			if($this->shop->create()!==false) {
				$result=$this->shop->add();
				if($result!==false) {
					$this->success("添加成功！", U("Shop/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$this->display('addShop');
		}
	}
	public function editShop()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->shop->create()!==false) {
				$result=$this->shop->where("id=".$id)->save();
				if ($result!==false) {
					$this->success("编辑成功！", U("Shop/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->shop->where("id=".$id)->find();
			$this->assign('result',$result);
			$this->display('editShop');
		}
	}
	
}
?>