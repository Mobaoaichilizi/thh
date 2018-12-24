<?php
// +----------------------------------------------------------------------
// | 店铺管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ChainController extends AdminbaseController {
	protected $chain;
	
	public function _initialize() {
		parent::_initialize();
		$this->chain = D("Chain");
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
		if($name){
			$where['name'] = array('like',"%$name%");
		}
		$count=$this->chain->where($where)->count();
		$shoplist = $this->chain
            ->where($where)
            ->order("createtime DESC")
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
	public function addChain()
	{
		if(IS_POST){
			if($this->chain->create()!==false) {
				if ($result!==false) {
					$result=$this->chain->add();
					$this->success("添加成功！", U("Chain/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$this->display('addChain');
		}
	}
	public function editChain()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->chain->create()!==false) {
				if ($result!==false) {
					$result=$this->chain->where("id=".$id)->save();
					$this->success("编辑成功！", U("Chain/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->chain->where("id=".$id)->find();
			$this->assign('result',$result);
			$this->display('editChain');
		}
	}
	
}
?>