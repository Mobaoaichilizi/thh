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
class VersionController extends AdminbaseController {
	protected $version;
	
	public function _initialize() {
		parent::_initialize();
		$this->version = D("Version");
		$this->user = D("User");
		$this->systemsinfo = D("SystemsInfo");
	}
    public function index(){
		$count=$this->version->count();
		$page = $this->page($count,11);
		$list = $this->version
            ->order("createtime DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		
		$this->assign("page", $page->show());
		$this->assign("list",$list);
        $this->display('index');
    }
    //添加版本信息
	public function addVersion(){
    
    	if(IS_POST){
			if($this->version->create()!==false) {
				if ($version!==false) {
					$version=$this->version->add();
					admin_log('add','version',$_POST['ver_desc']);


					$link = $this->version->where("id=".$version)->order("createtime desc")->find();
					$users = $this->user->where("id!=''")->select();
					foreach ($users as $key => $value) {
						if($value['deviceid']!='')
						{
							$title="这是一个系统消息";
							$content = "亲,有新的版本，请注意更新！";
							$device[] = $value['deviceid'];
							$extra = array("type" => "7", "type_id" => $link['ver_url'],'user_type' => 1,'status' => 1);
							$audience='{"alias":'.json_encode($device).'}';
							$extras=json_encode($extra);
							$os=$value['os'];
							jpush_send($title,$content,$audience,$os,$extras);
							$res_sys_info=array(
								"title" => $title,
								"description" => $content,
								"send_uid" => 0,
								"receive_uid" =>$value['id'],
								"type_id" => $version,
								"type" => 7,
								"createtime" => time(),
							);
							$this->systemsinfo->add($res_sys_info);
						}
					}
					




					$this->success("添加成功！", U("Version/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}else{
				$this->version->getError();
			}
			
		}else
		{
			$this->display('addVersion');
		}
    }
    //编辑版本消息
    public function editVersion()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			$res = array(
				"version" => $_POST['version'],
				"ver_desc" => $_POST['ver_desc'],
				"ver_url" => $_POST['ver_url'],
				"is_mandatory" => $_POST['is_mandatory'],
				"id" => $id,
			);
			if($this->version->create()!==false) {
				if ($result!==false) {
					$result=$this->version->where("id=".$id)->save($res);
					$this->success("编辑成功！", U("Version/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$version=$this->version->where("id=".$id)->find();
			$this->assign('version',$version);
			
			$this->display('editVersion');
		}
	}
    //删除版本消息
	public function delVersion()
	{
		$id = I('post.id',0,'intval');
		$ver_desc=$this->version->where("id=".$id)->getField("ver_desc");
		admin_log('del','version',$ver_desc);
		if ($this->version->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
}
?>