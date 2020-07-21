<?php
// +----------------------------------------------------------------------
// | 用户管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class UserController extends AdminbaseController {
	protected $user,$role;
	
	public function _initialize() {
		parent::_initialize();
		$this->user = D("admin");
		$this->role = D("Role");
	}
    public function index(){
		$admin_login = I('request.admin_login');
		if($admin_login){
			$where['admin_login'] = array('like',"%$admin_login%");
		}
		$count=$this->user->where($where)->count();
		$page = $this->page($count,11);
		$users = $this->user
            ->where($where)
            ->order("sort asc,id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($users as $row)
		{
			$row['role_name']=$this->role->where('id='.$row['role_id'])->getField('name');
			$userst[]=$row;
		}
		$this->assign("page", $page->show());
		$this->assign("users",$userst);
        $this->display('index');
    }
	public function addUser()
	{
		if(IS_POST){
			$admin_login = I('post.admin_login');
			$isadmin=$this->user->where("admin_login='".$admin_login."'")->find();
			if($isadmin)
			{
				$this->error('用户已存在!');
			}
			if($this->user->create()!==false) {
				if ($result!==false) {
					$this->user->admin_pwd=sp_password(I('post.admin_pwd'));
					$result=$this->user->add();
					admin_log('add','user',$admin_login);
					$this->success("添加成功！", U("User/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$rolelist=$this->role->where('state=1')->order('sort asc')->select();
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
				if ($result!==false) {
					$result=$this->user->where("id=".$id)->save();
					$admin_login=$this->user->where("id=".$id)->getField("admin_login");
					admin_log('edit','user',$admin_login);
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
			$rolelist=$this->role->where('state=1')->order('sort asc')->select();
			$this->assign('rolelist',$rolelist);
			$this->display('editUser');
		}
	}
	public function delUser()
	{
		$id = I('post.id',0,'intval');
		if($id==1){
			$this->error("最高管理员不能删除！");
		}
		$admin_login=$this->user->where("id=".$id)->getField("admin_login");
		admin_log('del','user',$admin_login);
		if ($this->user->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
}
?>