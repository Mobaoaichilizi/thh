<?php
// +----------------------------------------------------------------------
// | 用户管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class EvalabelController extends ManagerbaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->setting = M("Setting");
		$this->evalabel = D("Evalabel");
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
	}
    public function index()
	{
        $this->display('index');
    }
	public function get_index()
	{
		$username = I('request.username');
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
		if($username){
			$where['label_name'] = array('like',"%$username%");
		}
		$count=$this->evalabel->where($where)->count();
		$lists = $this->evalabel
            ->where($where)
            ->order("sort asc")
            ->limit($page.','.$limit)
            ->select();
		foreach($lists as &$row)
		{
			$row['cate_name']=$this->setting->where('id='.$row['cate_id'])->getField('title');
			$row['createtime']=date("Y-m-d H:i:s",$row['createtime']);
		}
		unset($data);
		$data['code']=0;
		$data['msg']=$page;
		$data['count']=$count;
		$data['data']=$lists;
		outJson($data);
	}
	public function addEvalabel()
	{
		if(IS_POST){
			if($this->evalabel->create()!==false) {
				$result=$this->evalabel->add();
				if ($result!==false) {
					$this->success("添加成功！", U("Evalabel/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$rolelist=$this->setting->where('parentid=19')->order('sort asc')->select();
			$this->assign('rolelist',$rolelist);
			$this->display('addEvalabel');
		}
	}
	public function editEvalabel()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->evalabel->create()!==false) {
				$result=$this->evalabel->where("id=".$id)->save();
				if ($result!==false) {
					$this->success("编辑成功！", U("Evalabel/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->evalabel->where("id=".$id)->find();
			$this->assign('result',$result);
			$rolelist=$this->setting->where('parentid=19')->order('sort asc')->select();
			$this->assign('rolelist',$rolelist);
			$this->display('editEvalabel');
		}
	}
	public function delEvalabel()
	{
		$id = I('post.id',0,'intval');
		if ($this->evalabel->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
}
?>