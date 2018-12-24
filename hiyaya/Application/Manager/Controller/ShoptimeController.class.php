<?php
// +----------------------------------------------------------------------
// | 用户组管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class ShoptimeController extends ManagerbaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->shoptime = D("ShopTime");
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
	}
    public function index(){
		
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
		$where['shop_id']=$this->shopid;
		if($name){
			$where['name'] = array('like',"%$name%");
		}
		$count=$this->shoptime->where($where)->count();
		$rolelist = $this->shoptime
            ->where($where)
            ->order("sort asc,id DESC")
            ->limit($page.','.$limit)
            ->select();
		foreach($rolelist as &$row)
		{
			$row['status'] = $row['status']==1 ? '<button class="layui-btn layui-btn-xs">启用</button>' : '<button class="layui-btn layui-btn-primary layui-btn-xs">禁用</button>';
			$row['is_day'] = $row['is_day']==1 ? '<button class="layui-btn layui-btn-xs">是</button>' : '<button class="layui-btn layui-btn-primary layui-btn-xs">否</button>';
		}
		unset($data);
		$data['code']=0;
		$data['msg']=$page;
		$data['count']=$count;
		$data['data']=$rolelist;
		outJson($data);
	}
	public function addShoptime()
	{
		if(IS_POST){
			$count=$this->shoptime->where("shop_id=".$this->shopid." and types=".I('post.types'))->count();
			if($count > 0)
			{
				$this->error('每个班次只能添加一次!');
			}
			if($this->shoptime->create()!==false) {
				$this->shoptime->status=I("post.status",0,'intval');
				$this->shoptime->is_day=I("post.is_day",0,'intval');
				$result=$this->shoptime->add();
				if ($result!==false) {
					$this->success("添加成功！", U("Shoptime/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}	
		}else
		{
			$this->display('addShoptime');
		}
	}
	public function editShoptime()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->shoptime->create()!==false) {
				$this->shoptime->status=I("post.status",0,'intval');
				$this->shoptime->is_day=I("post.is_day",0,'intval');
				$result=$this->shoptime->where("id=".$id)->save();
				if ($result!==false) {
					$this->success("编辑成功！", U("Shoptime/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->shoptime->where("id=".$id)->find();
			$this->assign('result',$result);
			$this->display('editShoptime');
		}
	}
	public function delShoptime()
	{
		$id = I('post.id',0,'intval');
		if ($this->shoptime->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
}
?>