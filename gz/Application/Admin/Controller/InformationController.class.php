<?php
// +----------------------------------------------------------------------
// | 信息列表
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class InformationController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->information = D("Information");
		$this->setting = D("Setting");
		$this->user = D("User");
		$this->systemsinfo = D("SystemsInfo");
	}

	//列表显示
    public function index(){
    	$description = I('request.description');//所属分类
		$setting_id = I('request.setting_id');
		$where['description'] = array('like',"%$description%");
		if($setting_id){
			$where['setting_id'] = array('eq',$setting_id);
		}
		
		$count=$this->information->where($where)->count();
		$page = $this->page($count,11);
		$information = $this->information
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $categories = $this->setting->where('parentid=18')->select();
		$this->assign("page", $page->show());
		$this->assign("informationlist",$information);
		$this->assign('categories',$categories);
        $this->display('index');
    }
    
    public function addInformation()
	{
		if(IS_POST){
			if($this->information->create()!==false) {

				$information=$this->information->add();

				if ($information!==false) {
					if($_POST['setting_id'] == '119'){
						$users = $this->user->where("id!=0")->select();
						foreach ($users as $key => $value) {
							$title=I('post.title');
							$content = I('post.description');
							$device[] = $value['deviceid'];
							$extra = array("type" => "1", "type_id" => $information,'user_type' => 1,'status' => 1);
							$audience='{"alias":'.json_encode($device).'}';
							$extras=json_encode($extra);
							$os=$value['os'];
							$res=jpush_send($title,$content,$audience,$os,$extras);
						}
						$res_sys_info=array(
							"title" => $title,
							"description" => $content,
							"send_uid" => "0",
							"receive_uid" =>"0_0" ,
							"type_id" => $information,
							"type" => 1,
							"createtime" => time(),
						);
						$this->systemsinfo->add($res_sys_info);
					}else if($_POST['setting_id'] == '228'){
						$users = $this->user->where("id!=0 and role=1")->select();
						foreach ($users as $key => $value) {
							$title=I('post.title');
							$content = I('post.description');
							$device[] = $value['deviceid'];
							$extra = array("type" => "1", "type_id" => $information,'user_type' => 1,'status' => 1);
							$audience='{"alias":'.json_encode($device).'}';
							$extras=json_encode($extra);
							$os=$value['os'];
							$res=jpush_send($title,$content,$audience,$os,$extras);
						}
						$res_sys_info=array(
							"title" => $title,
							"description" => $content,
							"send_uid" => "0",
							"receive_uid" =>"0_1" ,
							"type_id" => $information,
							"type" => 1,
							"createtime" => time(),
						);
						$this->systemsinfo->add($res_sys_info);
					}else if($_POST['setting_id'] == '229'){
						$users = $this->user->where("id!=0 and role=2")->select();
						foreach ($users as $key => $value) {
							$title=I('post.title');
							$content = I('post.description');
							$device[] = $value['deviceid'];
							$extra = array("type" => "1", "type_id" => $information,'user_type' => 2,'status' => 1);
							$audience='{"alias":'.json_encode($device).'}';
							$extras=json_encode($extra);
							$os=$value['os'];
							$res=jpush_send($title,$content,$audience,$os,$extras);
						}
						$res_sys_info=array(
							"title" => $title,
							"description" => $content,
							"send_uid" => "0",
							"receive_uid" =>"0_2" ,
							"type_id" => $information,
							"type" => 1,
							"createtime" => time(),
						);
						$this->systemsinfo->add($res_sys_info);
					}
					


					admin_log('add','information',$_POST['title']);
					$this->success("添加成功！", U("Information/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
			
		}else
		{
			$settings = $this->setting->field("id,title,parentid")->where("parentid=18")->select();
			$this->assign("setting",$settings);
			$this->display('addInformation');
		}
	}
	public function editInformation()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			if($this->information->create()!==false) {
				if ($information!==false) {
					$information=$this->information->where("id=".$id)->save();
					admin_log('edit','information',$_POST['title']);
					$this->success("编辑成功！", U("Information/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$information=$this->information->where("id=".$id)->find();
			$this->assign('information',$information);
			$settings = $this->setting->field("id,title,parentid")->where("parentid=18")->select();
			$this->assign("setting",$settings);
			$this->display('editInformation');
		}
	}
	public function delInformation()
	{
		$id = I('post.id',0,'intval');
		$title=$this->information->where("id=".$id)->getField("title");
		admin_log('del','information',$title);
		if ($this->information->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
		
	}
	

}