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
class MasseurbaseController extends BaseController {
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
      $session_user=M("ShopMasseur")->field('shop_id,chain_id,id,device')->where('token="'.$token.'"')->find();
    
      $this->shop_id=$session_user['shop_id'];
      $this->chain_id=$session_user['chain_id'];
      $this->masseur_id=$session_user['id'];
      if(!empty($session_user['shop_id']))
      {
           
		  //单点登陆
		  /*
		  if($session_user['device']!=$deviceid){
		     $result['code']=1001; 
		     $result['msg']='请登录';
		     outJson($result);
		     die;
		  }
		  */
		  
      }
      else
      {
          $result=array("code"=>1001,"msg"=>"请登陆");
          outJson($result);
          die();
      }
      //版本更新
      $versioncode = I('post.versioncode');
      $res=M("Version")->where("types=2")->order("createtime desc")->find();
      if($res['versioncode'] > $versioncode){
        $res_t=M("Version")->where("types=2")->order("createtime desc")->find();
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
  

}
?>