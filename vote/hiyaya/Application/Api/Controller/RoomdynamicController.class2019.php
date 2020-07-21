<?php
// +----------------------------------------------------------------------
// | 房间管理
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: love_fat <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class RoomdynamicController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
		$this->shoproom=M("ShopRoom"); //房间表
		$this->shopbed=M("ShopBed"); //床位表
		$this->roomcategory=M("RoomCategory"); //房间分类
		$this->shopfloor=M("ShopFloor"); //床位表
		$this->shopid=$this->shop_id;
		$this->chainid=$this->chain_id;
		$this->userid=$this->user_id;
    }
    public function index()
    {
        $category_id = I('post.category_id');
		$floor_id = I('post.floor_id');
		$state = I('post.state');
		if(!empty($category_id))
		{
			$where['category_id']=$category_id;
		}
		if(!empty($floor_id))
		{
			$where['floor_id']=$floor_id;
		}
		if(!empty($state))
		{
			$where['state']=$state;
		}
		$where['shop_id']=$this->shopid;
		$result=$this->shoproom->field("id,room_name,category_id,floor_id,state")->where($where)->order("id asc")->select();
		foreach($result as &$val)
		{
			$val['category_name']=$this->roomcategory->where("id=".$val['category_id'])->getField('category_name');
		}
		$category_list=$this->roomcategory->field("id,category_name,sort")->where("shop_id=".$this->shopid)->order("sort asc")->select();
		$floor_list=$this->shopfloor->field("id,floor")->where("shop_id=".$this->shopid)->order("id asc")->select();
		$count=$this->shoproom->where($where)->count();
		if($result)
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['count']=$count;
			$data['data']=$result;
			$data['category_list']=$category_list;
			$data['floor_list']=$floor_list;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['msg']='获取成功！';
			$data['count']=$count;
			$data['data']=$result;
			$data['category_list']=$category_list;
			$data['floor_list']=$floor_list;
			outJson($data);
		}
		
    }

}
?>