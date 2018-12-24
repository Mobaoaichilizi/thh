<?php
// +----------------------------------------------------------------------
// | 店铺人员管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ChainuserController extends AdminbaseController {
	protected $chainuser,$chain;
	
	public function _initialize() {
		parent::_initialize();
		$this->chainuser = D("ChainUser");
		$this->chain = D("Chain");
	}
    public function index(){
		
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
		if($username){
			$where['username'] = array('like',"%$username%");
		}
		$count=$this->chainuser->where($where)->count();
		$users = $this->chainuser
            ->where($where)
            ->order("id DESC")
            ->limit($page.','.$limit)
            ->select();
		foreach($users as $row)
		{
			$row['shop_name']=$this->chain->where('id='.$row['chain_id'])->getField('name');
			$row['createtime']=date("Y-m-d H:i:s",$row['createtime']);
			$row['last_login_time']=date("Y-m-d H:i:s",$row['last_login_time']);
			$row['state'] = $row['state']==1 ? '启用' : '禁用';
			$row['role_id'] = $row['role_id']==0 ? '本店超管' : '普通管理员';
			$userst[]=$row;
		}
		unset($data);
		$data['code']=0;
		$data['msg']=$page;
		$data['count']=$count;
		$data['data']=$userst;
		outJson($data);
	}
	public function addChainuser()
	{
		if(IS_POST){
			$username = I('post.username');
			$isadmin=$this->chainuser->where("username='".$username."'")->find();
			if($isadmin)
			{
				$this->error('用户已存在!');
			}
			if($this->chainuser->create()!==false) {
				if ($result!==false) {
					$this->chainuser->password=sp_password(I('post.password'));
					$result=$this->chainuser->add();
					$this->success("添加成功！", U("Chainuser/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$shoplist=$this->chain->where('state=1')->order('id asc')->select();
			$this->assign('shoplist',$shoplist);
			$this->display('addChainuser');
		}
	}
	public function editChainuser()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->chainuser->create()!==false) {
				if ($result!==false) {
					$result=$this->chainuser->where("id=".$id)->save();
					$this->success("编辑成功！", U("Chainuser/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->chainuser->where("id=".$id)->find();
			$this->assign('result',$result);
			$shoplist=$this->chain->where('state=1')->order('id asc')->select();
			$this->assign('shoplist',$shoplist);
			$this->display('editChainuser');
		}
	}
	public function delChainuser()
	{
		$id = I('post.id',0,'intval');
		if ($this->chainuser->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
}
?>