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
		
        $this->display('index');
    }
	public function get_index()
	{
		$admin_login = I('request.admin_login');
		$limit = I('request.limit');
		$page = I('request.page');
		if($page==1)
		{
			$page=0;
		}else
		{
			$page=($page-1)*$limit;
		}
		
		if($admin_login){
			$where['admin_login'] = array('like',"%$admin_login%");
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
			$row['last_login_time']=date("Y-m-d H:i:s",$row['last_login_time']);
			$row['create_time']=date("Y-m-d H:i:s",$row['create_time']);
			$userst[]=$row;
		}
		//$this->assign("page", $page->show());
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
		if ($this->user->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
}
?>