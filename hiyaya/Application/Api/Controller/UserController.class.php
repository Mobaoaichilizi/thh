<?php
// +----------------------------------------------------------------------
// | 用户管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class UserController extends ApibaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->role = D("ShopRole");
		$this->user = D("ShopUser");
        $this->where=" chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
        $this->data=array('chain_id'=>$this->chain_id,'shop_id'=>$this->shop_id);
	}
	public function index()
	{
        $pagesize    = I('post.pagesize','','intval');
        $pagesize    = !empty($pagesize) ? $pagesize :10; //每页显示个数
        $pagecur     = I('post.pagecur','','intval');
        $pagecur     = !empty($pagecur)? $pagecur:1;//当前第几个页
        $pagestart   =($pagecur-1)*$pagesize;
        $keywords    = trim(I('post.keywords','','htmlspecialchars'));
        if(!empty($keywords))
        {
            $this->where.=" AND  username like '%".$keywords."%'";
        }
		$count=$this->user->where($this->where)->count();
		$users = $this->user
            ->field('shop_id,chain_id,name,username,nickname,head_img,createtime,state,last_login_time,role_id,device,token')
            ->where($this->where)
            ->order("id DESC")
            ->limit($pagestart.','.$pagesize)
            ->select();
		foreach($users as $row)
		{
            $row['index']         = Getzimu($row['nickname']);
		    $row['role_name']=$this->role->where('id='.$row['role_id'])->getField('name');
			$row['createtime']=date("Y-m-d H:i:s",$row['createtime']);
			$row['last_login_time']=date("Y-m-d H:i:s",$row['last_login_time']);
			if(!$row['role_name'])
			{
				$row['role_name']="顶级管理员";
			}
			$userst[]=$row;
		}
        $datapages=intval($count/$pagesize);
        if($count%$pagesize>0)
        {
            $datapages=$datapages+1;
        }
        if($count>0)
        {
            $result=array("code"=>0,"datapages"=>$datapages,"count"=>$count,"msg"=>"","data"=>$userst);
        }
        else
        {
            $result=array("code"=>0,"datapages"=>0,"count"=>0,"msg"=>"暂无数据");
        }
		outJson($result);
	}
}
?>