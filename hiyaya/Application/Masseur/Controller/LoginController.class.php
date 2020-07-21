<?php
/**
 * Created by PhpStorm.
 * User: Alina
 * Date: 2018/9/17
 * Time: 10:06
 */
namespace Masseur\Controller;
use Common\Controller\BaseController;
class LoginController extends BaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->user = M("ShopMasseur");
        $this->chain = M("Chain");
        $this->shop = M("Shop");
        $this->ordersproject = M("OrdersProject");
    }
    function index()
    {
        $errors = array(
            0=>'',
            1=>'密码错误',
            2=>'账号已停用',
            3=>'用户名不存在'
        );
        $code = 1;
        $data = array();
        $account    = trim(I('post.account','','htmlspecialchars'));
        $password   = trim(I('post.password','','htmlspecialchars'));
        $device     = trim(I('post.deviceid','','htmlspecialchars'));
        $os         = trim(I('post.os','','htmlspecialchars'));
        if(is_phone($account)===false)
        {
            $result=array("code"=>1,'data'=>$data,"msg"=>'手机号码格式不正确');
            outJson($result);
            die();
        }
        if(!empty($account) && !empty($password))
        {
            $password   = sp_password($password);
            $user_model = new \Masseur\Model\AccountModel();
            $result=$user_model->existaccount($account);
            if($result==false)
            {
                $code = 3;
            }
            else
            {
                $where=" tel='".$account."' AND password='".$password."' ";
                $user = $this->user->where($where)->find();
                if(!empty($user))
                {
                    if(1 == $user['state'])
                    {
                        $data=array("device"=>$device,"os"=>$os);
                        $this->user->where('token="'.$user['token'].'"')->save($data);
                        $data=array("device"=>'',"os"=>'');
                        $this->user->where('device="'.$device.'" and token!="'.$user['token'].'"')->save($data);
//                        $row = array(
//                            'chain_id'=> $user['chain_id'],
//                            'shop_id'=>  $user['shop_id'],
//                            'account'=>  $user['masseur_name'],
//                            'id'=>        $user['id']
//                        );
                        session("account",$user['username']);
                        session("shop_id",$user['shop_id']);
                        session("chain_id",$user['chain_id']);
                        session("user_id",$user['id']);
                        session("token",$user['token']);
                        //dump($_SESSION);
                        // $data['token'] =session_id();

                        $data['token'] = $user['token'];
                        $data['chain_id'] = $this->chain->where('id='.$user['chain_id'])->getfield('name');
                        $data['shop_id'] = $this->shop->where('id='.$user['shop_id'])->getfield('shop_name');
                        $data['masseur_name'] = $user['nick_name'];
                        $data['masseur_sn'] = $user['masseur_sn'];
                        $data['tel'] = $user['tel'];
                        $code = 0;
                    }
                    else
                    {
                        $code =2;
                    }
                }
            }
        }
        $result=array("code"=>$code,'data'=>$data,"msg"=>$errors[$code]);
        outJson($result);
    }
    //退出登录
    public function logout(){
        $token     = trim(I('post.token','','htmlspecialchars'));
        $data=array("device"=>'',"os"=>'');
        $res=$this->user->where('token="'.$token.'"')->save($data);
        if($res!==false)
        {
            $result=array("code"=>0,"msg"=>"退出成功！");
        }
        else
        {
            $result=array("code"=>1,"msg"=>"退出失败！");
        }

        outJson($result);
    }
}