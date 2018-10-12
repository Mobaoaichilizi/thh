<?php
// +----------------------------------------------------------------------
// | 电话预约排班管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class TelconsultationController extends AdminbaseController {
	protected $telconsultation,$user;
	
	public function _initialize() {
		parent::_initialize();
		$this->telconsultation = D("TelConsultation");
		$this->user = D("User");
	}
    public function index(){
		$count=$this->telconsultation->count();
		$page = $this->page($count,11);
		$list = $this->telconsultation
            ->order("createtime DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($list as $row)
		{
			$row['user_name']=$this->user->where("id=".$row['doctor_id']." and role = 1")->getField('username');
			$userst[]=$row;
		}
		$this->assign("page", $page->show());
		$this->assign("list",$userst);
        $this->display('index');
    }
	public function addTelconsultation()
	{
		if(IS_POST){
			if($this->telconsultation->create()!==false) {
				if ($result!==false) {
					$result=$this->telconsultation->add();
					$this->success("添加成功！", U("Telconsultation/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$userlist=$this->user->where("role = 1")->order('id desc')->select();
			$this->assign('userlist',$userlist);
			$this->display('addTelconsultation');
		}
	}
	public function editTelconsultation()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->telconsultation->create()!==false) {
				if ($result!==false) {
					$result=$this->telconsultation->where("id=".$id)->save();
					$this->success("编辑成功！", U("Telconsultation/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->telconsultation->where("id=".$id)->find();
			$this->assign('result',$result);
			$userlist=$this->user->where("role = 1")->order('id desc')->select();
			$this->assign('userlist',$userlist);
			$this->display('editTelconsultation');
		}
	}
	public function delTelconsultation()
	{
		$id = I('post.id',0,'intval');
		if ($this->telconsultation->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
}
?>