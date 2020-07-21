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
class VideoconsultationController extends AdminbaseController {
	protected $videoconsultation,$user;
	
	public function _initialize() {
		parent::_initialize();
		$this->videoconsultation = D("VideoConsultation");
		$this->user = D("User");
	}
    public function index(){
		$count=$this->videoconsultation->count();
		$page = $this->page($count,11);
		$list = $this->videoconsultation
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
	public function addVideoconsultation()
	{
		if(IS_POST){
			if($this->videoconsultation->create()!==false) {
				if ($result!==false) {
					$result=$this->videoconsultation->add();
					$this->success("添加成功！", U("Videoconsultation/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$userlist=$this->user->where("role=1")->order('id desc')->select();
			$this->assign('userlist',$userlist);
			$this->display('addVideoconsultation');
		}
	}
	public function editVideoconsultation()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->videoconsultation->create()!==false) {
				if ($result!==false) {
					$result=$this->videoconsultation->where("id=".$id)->save();
					$this->success("编辑成功！", U("Videoconsultation/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->videoconsultation->where("id=".$id)->find();
			$this->assign('result',$result);
			$userlist=$this->user->where("role = 1")->order('id desc')->select();
			$this->assign('userlist',$userlist);
			$this->display('editVideoconsultation');
		}
	}
	public function delVideoconsultation()
	{
		$id = I('post.id',0,'intval');
		if ($this->videoconsultation->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
}
?>