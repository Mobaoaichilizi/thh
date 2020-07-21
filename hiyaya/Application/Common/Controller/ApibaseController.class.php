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
class ApibaseController extends BaseController {
   public function __construct() {
		parent::__construct();
		$time=time();
		$this->host_url="http://s.haiyaya.vip";
		$this->assign("js_debug",APP_DEBUG?"?v=$time":"");
   }
   function _initialize()
   {
	  parent::_initialize();
      $token=I('post.token');
      $deviceid = I('post.deviceid');
      $session_user=M("ShopUser")->field('shop_id,chain_id,id,role_id,device')->where('token="'.$token.'"')->find();
      $this->shop_id=$session_user['shop_id'];
      $this->chain_id=$session_user['chain_id'];
	  $this->user_id=$session_user['id'];
      if(!empty($session_user['shop_id']))
       {
          if(!$this->check_access($session_user['id'],$session_user['role_id'])){
              $result=array("code"=>1003,"msg"=>"您没有访问权限");
              outJson($result);
              die();
          }
          //单点登陆
          if($session_user['device']!=$deviceid){
             $result['code']=1001; 
             $result['msg']='请登录';
             outJson($result);
             die;
          }
      }
      else
      {
          $result=array("code"=>1001,"msg"=>"请登陆");
          outJson($result);
          die();
      }
      //版本更新
      $versioncode = I('post.versioncode');
      $res=M("Version")->where("types=1")->order("createtime desc")->find();
      if($res['versioncode'] > $versioncode){
        $res_t=M("Version")->where("types=1")->order("createtime desc")->find();
        $result['code']=1002; 
        $result['msg']='请更新版本';
        $result['info']=array(
          'version' => $res_t['version'],
          'ver_desc' => $res_t['ver_desc'],
          'ver_url' => $res_t['ver_url'],
		  'android_url' => $res_t['android_url'],
		  'ios_url' => $res_t['ios_url'],
          'is_mandatory' => $res_t['is_mandatory'],
        );
        outJson($result);
        die();
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
		$no_need_check_rules=array("apiindexindex","apiindexwelcome");
		if(!in_array($rule,$no_need_check_rules) ){
			return api_sp_auth_check($uid);
		}else{
			return true;
		}
	}

}
?>