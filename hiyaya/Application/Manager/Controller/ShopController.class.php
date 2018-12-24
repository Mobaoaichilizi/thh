<?php
// +----------------------------------------------------------------------
// | 用户管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class ShopController extends ManagerbaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->Shop = D("shop");
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
	}
    public function index()
	{
        $where=" chain_id=".$this->chainid." and id=".$this->shopid;
	    $info=$this->Shop->where($where)->find();
	    $this->assign('info',$info);
        $this->display('index');
    }
    public function edit()
    {
        if(IS_POST)
        {
            $id = I('post.id',0,'intval');
            if($this->Shop->create()!==false)
            {
                $result=$this->Shop->where("id=".$id)->save();
                if ($result!==false)
                {
                    echo "success";
                }
                else
                {
                    echo "error";
                }
            }
        }
    }

}
?>