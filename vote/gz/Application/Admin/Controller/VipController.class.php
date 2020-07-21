<?php
// +----------------------------------------------------------------------
// | 版本管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class VipController extends AdminbaseController {
	protected $vip;
	
	public function _initialize() {
		parent::_initialize();
		$this->vip = D("Vip");
		$this->setting = D("Setting");
	}
    public function index(){
    	$os = I('request.os','1');
    	$where['os'] = array("eq",$os);
		$count=$this->vip->where($where)->count();
		$page = $this->page($count,11);
		$list = $this->vip
			->where($where)
            ->order("createtime asc")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach ($list as $key => &$value) {
			$value['setting_id'] = $this->setting->where("id=".$value['setting_id'])->getField('title');
		}
		$this->assign("page", $page->show());
		$this->assign("list",$list);
        $this->display('index');
    }
    //添加版本信息
	public function addVip(){
    
    	if(IS_POST){
			if($this->vip->create()!==false) {
				if ($this->vip->add()!==false) {

					admin_log('add','vip',$_POST['title']);

					$this->success("添加成功！", U("Vip/index"));
				}else
				{
					$this->error($this->vip->getError());
					// $this->error('添加失败!');
				}
			}
			
		}else
		{
			$category = $this->setting->where("parentid = 139")->select();
			$this->assign("category",$category);
			$this->display('addVip');
		}
    }
    //编辑版本消息
    public function editVip()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			
			if($this->vip->create()!==false) {
				$result=$this->vip->where("id=".$id)->save($res);
				if ($result!==false) {
					
					$this->success("编辑成功！", U("Vip/index"));
				}else
				{	
					$this->error($this->vip->getError());
					// $this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$vip=$this->vip->where("id=".$id)->find();
			$this->assign('vip',$vip);
			$category = $this->setting->where("parentid = 139")->select();
			$this->assign("category",$category);
			$this->display('editVip');
		}
	}
    //删除版本消息
	public function delVip()
	{
		$id = I('post.id',0,'intval');
		$ver_desc=$this->vip->where("id=".$id)->getField("title");
		admin_log('del','vip',$title);
		if ($this->vip->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
}
?>