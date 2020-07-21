<?php
// +----------------------------------------------------------------------
// | 系统消息管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class SystemsinfoController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->systemsinfo = D("SystemsInfo");
		$this->user = D("User");
		$this->member = D('Member');
		$this->patientmember = D('Patientmember');
	}

	//列表显示
    public function index(){
		$title = I('request.title');
		$where['title'] = array('like',"%$title%");
		$count=$this->systemsinfo->where($where)->count();
		$page = $this->page($count,11);
		$systemsinfo = $this->systemsinfo
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($systemsinfo as $key => $value) {
			

        	$value['send'] = $this->user->where("id=".$value['send_uid'])->getField('username');
        	$value['receive'] = $this->user->where("id=".$value['receive_uid'])->getField('username');
        	$userst[]=$value;
        }
		$this->assign("page", $page->show());
		$this->assign("list",$userst);
        $this->display('index');
    }
    //添加系统消息
    public function addSystemsinfo()
	{
		if(IS_POST){
			$receive_users = I('post.receive_uid');
			if($receive_users == '00'){//全部
				$users = $this->user->where("id!=0 and id=190")->select();
				foreach ($users as $key => $value) {
					$title=I('post.title');
					$content = I('post.description');
					$device[] = $value['deviceid'];
					$extra = array("type" => "1", "type_id" => 16,'user_type' => 1,'status' => 1);
					$audience='{"alias":'.json_encode($device).'}';
					$extras=json_encode($extra);
					$os=$value['os'];
					$res=jpush_send($title,$content,$audience,$os,$extras);
				}
			}else if($receive_users == '01'){//医生
				$users = $this->user->where("id!=0 and role=1")->select();
				foreach ($users as $key => $value) {
					$title=I('post.title');
					$content = I('post.description');
					$device[] = $value['deviceid'];
					$extra = array("type" => "1", "type_id" => 16,'user_type' => 1,'status' => 1);
					$audience='{"alias":'.json_encode($device).'}';
					$extras=json_encode($extra);
					$os=$value['os'];
					$res=jpush_send($title,$content,$audience,$os,$extras);
				}
			}else if($receive_users == '02'){//患者
				$users = $this->user->where("id!=0 and role=2")->select();
				foreach ($users as $key => $value) {
					$title=I('post.title');
					$content = I('post.description');
					$device[] = $value['deviceid'];
					$extra = array("type" => "1", "type_id" => 16,'user_type' => 2,'status' => 1);
					$audience='{"alias":'.json_encode($device).'}';
					$extras=json_encode($extra);
					$os=$value['os'];
					$res=jpush_send($title,$content,$audience,$os,$extras);
				}
			}
			if($this->systemsinfo->create()!==false) {

				$result=$this->systemsinfo->add();

				if ($result!==false) {
					$this->success("添加成功！", U("Systemsinfo/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
		}else
		{
			$this->display('addSystemsinfo');
		}
	}
    
	

}