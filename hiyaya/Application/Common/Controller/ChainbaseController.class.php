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
class ChainbaseController extends BaseController {
   public function __construct() {
		parent::__construct();
		$time=time();
		$this->assign("js_debug",APP_DEBUG?"?v=$time":"");
   }
   function _initialize(){
		parent::_initialize();
		$session_user_id=session('chain_user_id');
		if(!empty($session_user_id))
		{
			$chain_user=M('ChainUser')->where(array('id'=>$session_user_id))->find();			
			$node=D('ChainMenu');
            //print_r($chain_user);
			if($chain_user['role_id'] == 0){
				$menu_list= $node->where("parentid=0 and status=1")->order('listorder')->select(); 
				 foreach($menu_list as $n=>$val){
					  $menu_list[$n]['voo']=$node->where("parentid=".$val['id']." and status=1")->order('listorder')->select(); 
					  foreach($menu_list[$n]['voo'] as $k=>$row)
					 {
						$menu_list[$n]['voo'][$k]['ttt']=$node->where("parentid=".$row['id']." and status=1")->order('listorder')->select();  
					 }		  
				 }
			}else{

                $menu_list= $node->where("parentid=0 and status=1 and id in (select node_id from dade_chain_access where chain_id=".$chain_user['chain_id']." and role_id=".$chain_user['role_id'].")")->order('listorder')->select();
			   foreach($menu_list as $n=> $val){	      
				  $menu_list[$n]['voo']=$node->where("parentid=".$val['id']." and status=1 and id in (select node_id from dade_chain_access where chain_id=".$chain_user['chain_id']." and role_id=".$chain_user['role_id'].")")->order('listorder')->select();  
				  foreach($menu_list[$n]['voo'] as $k=> $row)
				 {
					$menu_list[$n]['voo'][$k]['ttt']=$node->where("parentid=".$row['id']." and status=1 and id in (select node_id from dade_chain_access where chain_id=".$chain_user['chain_id']." and role_id=".$chain_user['role_id'].")")->order('listorder')->select();  
				 }		
			   }
			}
			$this->assign("chain_user",$chain_user);
			$this->assign("menu_list",$menu_list);						
			$this->assign("chain_id",$chain_user['chain_id']);
			if(!$this->check_access($session_user_id,$chain_user['role_id'])){
				$this->error("您没有访问权限！",U('Chain/index/welcome'));
			}
			
		}else
		{
			$this->error("您还没有登录！",U('Chain/public/login'));
		}
   }
   /**
	 *  检查后台用户访问权限
	 * @param int $uid 后台用户id
	 * @return boolean 检查通过返回true
	 */
	private function check_access($uid,$role_id){
		//如果用户角色是1，则无需判断
		if($role_id == 0){			
		return true;
		}
		$rule=MODULE_NAME.CONTROLLER_NAME.ACTION_NAME;
		$rule=strtolower($rule);
		$no_need_check_rules=array("chainindexindex","chainindexwelcome");
		if(!in_array($rule,$no_need_check_rules) ){
			return chain_sp_auth_check($uid);
		}else{
			return true;
		}
	}

}
?>