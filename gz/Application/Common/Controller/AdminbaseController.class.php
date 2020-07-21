<?php
// +----------------------------------------------------------------------
// | 后台公共文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Common\Controller;
use Common\Controller\BaseController;
class AdminbaseController extends BaseController {
   public function __construct() {
		parent::__construct();
		$time=time();
		$this->assign("js_debug",APP_DEBUG?"?v=$time":"");
		$this->assign("http_img_url",'http://sxgzzyy.oss-cn-shanghai.aliyuncs.com/');
   }
   function _initialize(){
		parent::_initialize();
		$session_admin_id=session('admin_id');
		if(!empty($session_admin_id))
		{
			$admin_user=M('Admin')->where(array('id'=>$session_admin_id))->find();
			$node=D('Menu');
			if($session_admin_id == 1){
				$menu_list= M('Menu')->where("parentid=0 and status=1")->order('listorder')->select(); 
				 foreach($menu_list as $n=>$val){
					  $menu_list[$n]['voo']=M('Menu')->where("parentid=".$val['id']." and status=1")->order('listorder')->select(); 
					  foreach($menu_list[$n]['voo'] as $k=>$row)
					 {
						$menu_list[$n]['voo'][$k]['ttt']=M('Menu')->where("parentid=".$row['id']." and status=1")->order('listorder')->select();  
					 }		  
				 }
			}else{
				 
				$menu_list= $node->where("parentid=0 and status=1 and id in (select node_id from lgq_access where role_id=".$admin_user['role_id'].")")->order('listorder')->select(); 
			   foreach($menu_list as $n=> $val){	      
				  $menu_list[$n]['voo']=$node->where("parentid=".$val['id']." and id in (select node_id from lgq_access where role_id=".$admin_user['role_id'].")")->order('listorder')->select();  
				  foreach($menu_list[$n]['voo'] as $k=> $row)
				 {
					$menu_list[$n]['voo'][$k]['ttt']=$node->where("parentid=".$row['id']." and id in (select node_id from lgq_access where role_id=".$admin_user['role_id'].")")->order('listorder')->select();  
				 }		
			   }
			}
			
			$datac   = M('Config')->field('type,name,value')->select();
			$configc = array();
			if($datac && is_array($datac)){	
				foreach ($datac as $value) {
						$configc[$value['name']] = $value['value'];
				}
			}
			C($configc); //添加配置
			$this->assign("admin_user",$admin_user);
			$this->assign("menu_list",$menu_list);
			if(!$this->check_access($session_admin_id)){
				$this->error("您没有访问权限！",U('admin/index/welcome'));
			}
			
		}else
		{
			$this->error("您还没有登录！",U('admin/public/login'));
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
		
		$rule=MODULE_NAME.CONTROLLER_NAME.ACTION_NAME;
		$rule=strtolower($rule);
		$no_need_check_rules=array("adminindexindex","adminindexwelcome");
		if(!in_array($rule,$no_need_check_rules) ){
			return sp_auth_check($uid);
		}else{
			return true;
		}
	}

}
?>