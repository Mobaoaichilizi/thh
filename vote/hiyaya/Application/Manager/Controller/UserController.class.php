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
class UserController extends ManagerbaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->role = D("ShopRole");
		$this->user = D("ShopUser");
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
			$where['username'] = array('like',"%$username%");
		}
		$count=$this->user->where($where)->count();
		$users = $this->user
            ->where($where)
            ->order("id DESC")
            ->limit($page.','.$limit)
            ->select();
		foreach($users as $row)
		{
			$row['role_name']=$this->role->where('id='.$row['role_id'])->getField('name');
			$row['createtime']=date("Y-m-d H:i:s",$row['createtime']);
			$row['last_login_time']=date("Y-m-d H:i:s",$row['last_login_time']);
			if(!$row['role_name'])
			{
				$row['role_name']="顶级管理员";
			}
			
			$userst[]=$row;
		}
		unset($data);
		$data['code']=0;
		$data['msg']=$page;
		$data['count']=$count;
		$data['data']=$userst;
		outJson($data);
	}
	public function addUser()
	{
		if(IS_POST){
			$username = I('post.username');
			$isuser=$this->user->where("username='".$username."'")->find();
			if($isuser)
			{
				$this->error('用户已存在!');
			}
			if($this->user->create()!==false) {
				$this->user->password=sp_password(I('post.password'));
				$result=$this->user->add();
				$this->user->where("id=".$result)->save(array('token' => md5($result.time().'lovefat')));
				if ($result!==false) {
					$this->success("添加成功！", U("User/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$rolelist=$this->role->where('state=1 and shop_id='.$this->shopid)->order('sort asc')->select();
			$this->assign('rolelist',$rolelist);
			$this->display('addUser');
		}
	}
	public function editUser()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->user->create()!==false) {
				$result=$this->user->where("id=".$id)->save();
				if ($result!==false) {
					$this->success("编辑成功！", U("User/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->user->where("id=".$id)->find();
			$this->assign('result',$result);
			$rolelist=$this->role->where('state=1 and shop_id='.$this->shopid)->order('sort asc')->select();
			$this->assign('rolelist',$rolelist);
			$this->display('editUser');
		}
	}
	public function delUser()
	{
		$id = I('post.id',0,'intval');
		$res=$this->user->where("id=".$id)->find();
		if($res['role_id']==0){
			$this->error("最高管理员不能删除！");
		}
		if ($this->user->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
}
?>