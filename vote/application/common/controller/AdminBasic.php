<?php
// +----------------------------------------------------------------------
// | 系统公共文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace app\common\controller;
use app\common\controller\Basic;
use think\Url;
use think\Db;
use think\Request;

class AdminBasic extends Basic
{

    public function __construct() {
		parent::__construct();
		$time=time();
		$this->assign("js_debug",$time);
   }
   public function _initialize(){
		parent::_initialize();
		$session_admin_id=session('admin_id');
		if(!empty($session_admin_id))
		{
			$admin_user=Db::name('Admin')->where(array('id'=>$session_admin_id))->find();
			$node=Db::name('Menu');
			if($session_admin_id == 1){
				$menu_list= $node->where("parentid=0 and status=1")->order('listorder')->select();
				 foreach($menu_list as $n=>$val){
					  $menu_list[$n]['voo']=$node->where("parentid=".$val['id']." and status=1")->order('listorder')->select();
					  foreach($menu_list[$n]['voo'] as $k=>$row)
					  {
						$menu_list[$n]['voo'][$k]['ttt']=$node->where("parentid=".$row['id']." and status=1")->order('listorder')->select();
					  }
				 }
			}else{

				$menu_list= $node->where("parentid=0 and status=1 and id in (select node_id from sys_access where role_id=".$admin_user['role_id'].")")->order('listorder')->select();
			   foreach($menu_list as $n=> $val){
				  $menu_list[$n]['voo']=$node->where("parentid=".$val['id']."  and status=1 and id in (select node_id from sys_access where role_id=".$admin_user['role_id'].")")->order('listorder')->select();
				  foreach($menu_list[$n]['voo'] as $k=> $row)
				 {
					$menu_list[$n]['voo'][$k]['ttt']=$node->where("parentid=".$row['id']."  and status=1 and id in (select node_id from sys_access where role_id=".$admin_user['role_id'].")")->order('listorder')->select();
				 }
			   }
			}
			$this->assign("admin_user",$admin_user);
			$this->assign("menu_list",$menu_list);
			if(!$this->check_access($session_admin_id)){
				$this->error("您没有访问权限！",Url::build('admin/index/welcome'));
			}

		}else
		{
			$this->redirect(Url::build("admin/login/index"));
		}
   }
   /**
	 *  检查后台用户访问权限
	 * @param int $uid 后台用户id
	 * @return boolean 检查通过返回true
	 */
	private function check_access($uid){
		//如果用户角色是1，则无需判断
		if($uid == 1){
			return true;
		}
        $request = Request::instance();
		$rule=$request->module().$request->controller().$request->action();
		$rule=strtolower($rule);
		$no_need_check_rules=array("adminindexindex","adminindexwelcome");
		if(!in_array($rule,$no_need_check_rules) ){
			return sp_auth_check($uid);
		}else{
			return true;
		}
	}
}
