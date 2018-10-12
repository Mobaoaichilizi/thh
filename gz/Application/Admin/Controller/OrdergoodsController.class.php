<?php
// +----------------------------------------------------------------------
// | 订单管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class OrdergoodsController extends AdminbaseController {
	protected $order,$ordergoods,$user;
	
	public function _initialize() {
		parent::_initialize();
		$this->order = D("Order");
		$this->ordergoods = D("OrderGoods");
		$this->user = D("User");
	}
    public function index(){
		$order_sn = I('request.order_sn');
		if($order_sn){
			$where['order_sn'] = array('like',"%$order_sn%");
		}
		$count=$this->order->where($where)->count();
		$page = $this->page($count,11);
		$list = $this->order
            ->where($where)
            ->order("createtime DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		foreach($list as $row)
		{
			$row['member_name']=$this->user->where('id='.$row['user_id'])->getField('username');
			$listt[]=$row;
		}
		$this->assign("page", $page->show());
		$this->assign("list",$listt);
        $this->display('index');
    }
	public function editOrder()
	{
		if(IS_POST){
			$id = I('post.id',0,'intval');
			$order_status = I('post.order_status',0,'intval');
			unset($data);
			$data=array(
				'order_status' => $order_status,
			);
			$result=$this->order->where("id=".$id)->save($data);
			if ($result!==false) {
				$this->success("操作成功！", U("Order/index"));
			}else
			{
				$this->error('操作失败!');
			}
		}else
		{
			
			$id = I('get.id',0,'intval');
			$result=$this->order->where("id=".$id)->find();
			$result['order_phone']=$this->user->where('id='.$result['user_id'])->getField('username');
			$goods_list=$this->ordergoods->where("order_id=".$result['id'])->select();
			$total=0;
			foreach($goods_list as $row)
			{
				$row['goods_total']=$row['number']*$row['price'];
				$total+=$row['goods_total'];
				$goods_l[]=$row;
			}
			$this->assign('goods_list',$goods_l);
			$this->assign('result',$result);
			$this->assign('total',$total);
			$this->display('editOrder');
		}
	}
	public function editpayOrder()
	{
		if(IS_POST){
			$id = I('post.id',0,'intval');
			$order_status = I('post.order_status',0,'intval');
			$pay_status = I('post.pay_status',0,'intval');
			unset($data);
			$data=array(
				'order_status' => $order_status,
				'pay_status' => $pay_status,
			);
			$result=$this->order->where("id=".$id)->save($data);
			if ($result!==false) {
				$this->success("操作成功！", U("Order/index"));
			}else
			{
				$this->error('操作失败!');
			}
		}
	}
	public function delOrder()
	{
		$id = I('post.id',0,'intval');
		unset($data);
		$data=array(
			'is_show' => 1,
		);
		$order_sn=$this->order->where("id=".$id)->getField("order_sn");
		admin_log('cancel','goodsorder',$order_sn);
		$result=$this->order->where("id=".$id)->save($data);
		if ($result!==false) {
			$this->success("操作成功！", U("Order/index"));
		}else
		{
			$this->error('操作失败!');
		}
	}
	
}
?>