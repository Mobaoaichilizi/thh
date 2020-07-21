<?php
// +----------------------------------------------------------------------
// | 预约排班管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ConsultationController extends AdminbaseController {
	protected $consultation,$user;
	
	public function _initialize() {
		parent::_initialize();
		$this->consultation = D("Consultation");
		$this->user = D("User");
	}
    public function index(){
		$count=$this->consultation->count();
		$page = $this->page($count,11);
		$list = $this->consultation
            ->order("id desc,createtime DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($list as $row)
		{
			$row['user_name']=$this->user->where('id='.$row['doctor_id'])->getField('username');
			$userst[]=$row;
		}
		$this->assign("page", $page->show());
		$this->assign("list",$userst);
        $this->display('index');
    }
	public function addConsultation()
	{
		if(IS_POST){
			if($this->consultation->create()!==false) {
				if ($result!==false) {
					$result=$this->consultation->add();
					$this->success("添加成功！", U("Consultation/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$userlist=$this->user->where("role = 1")->order('id desc')->select();
			$this->assign('userlist',$userlist);
			$this->display('addConsultation');
		}
	}
	public function editConsultation()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->consultation->create()!==false) {
				if ($result!==false) {
					$result=$this->consultation->where("id=".$id)->save();
					$this->success("编辑成功！", U("Consultation/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$result=$this->consultation->where("id=".$id)->find();
			$this->assign('result',$result);
			$userlist=$this->user->where("role = 1")->order('id desc')->select();
			$this->assign('userlist',$userlist);
			$this->display('editConsultation');
		}
	}
	public function delConsultation()
	{
		$id = I('post.id',0,'intval');
		if ($this->consultation->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
}
?>