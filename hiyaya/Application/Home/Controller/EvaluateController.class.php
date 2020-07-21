<?php
namespace Home\Controller;
use Common\Controller\BaseController;
class EvaluateController extends BaseController {
	public function _initialize() {
		parent::_initialize();
		$this->evaluation=M('Evaluation');
		$this->shopmasseur=M('ShopMasseur');
		$this->orders=M('Orders');
		$this->ordersproject=M('OrdersProject');
		$this->evalabel=M('Evalabel');
		$this->setting = M("Setting");
	}
	//绑定账号
	public function index()
	{
		$order_id = I('get.order_id'); 
		$masseur_id = I('get.masseur_id'); 
		
		if($order_id=='')
		{
			$this->error("订单号不存在！");
		}
		if($masseur_id=='')
		{
			$this->error("技师不存在");
		}
		
		$masseur_info=$this->shopmasseur->where("id=".$masseur_id)->find();
		if($masseur_info['cover']!='')
		{
			$masseur_info['cover']=substr($masseur_info['cover'], 1);
		}
		$order_info=$this->orders->where("id=".$order_id)->find();
		if(!$order_info)
		{
			$this->error("订单不存在！");
		}
		$project_order_info=$this->ordersproject->where("order_id=".$order_id." and masseur_id=".$masseur_id)->find();
		
		
		$evalabel_list=$this->setting->where('parentid=19')->order('sort asc')->select();
		foreach($evalabel_list as &$val)
		{
			$val['evalabel_info']=$this->evalabel->where("shop_id=".$order_info['shop_id']." and cate_id=".$val['id'])->order("sort asc")->select();
		}
		
		$this->assign("evalabel_list",$evalabel_list);
		$this->assign("project_order_info",$project_order_info);
		$this->assign("masseur_info",$masseur_info);
		$this->assign("order_info",$order_info);
		$this->display();
	}
	//提交绑定账号
	public function do_evaluation()
	{
		$order_id = I('post.order_id'); //订单ID
		$masseur_id=I('post.masseur_id'); //技师ID
		$project_order_id=I('post.project_order_id'); //项目订单ID
		$content=I('post.content'); //内容
		$score=I('post.score',0); //分数
		$lable_list=I('post.lable_list'); //热门标签
		if($score==0)
		{
			$this->error("请选择星级！");
		}
		if($order_id=='' || $masseur_id=='' || $content=='' || $score=='')
		{
			$this->error("内容不能为空！");
		}
		
		
		
		$is_downtime=$this->ordersproject->where("order_id=".$order_id." and masseur_id=".$masseur_id." and down_time=0")->find();
		if($is_downtime)
		{
			$this->error("技师还没有下钟！");
		}
		
		$is_eval=$this->ordersproject->where("order_id=".$order_id." and masseur_id=".$masseur_id." and is_evaluation=1")->find();
		if($is_eval)
		{
			$this->error("已经评价过了！");
		}
		$order_info=$this->orders->where("id=".$order_id)->find();
		if(!$order_info)
		{
			$this->error("订单不存在！");
		}
		unset($data_array);
		$data_array=array(
			'chain_id' => $order_info['chain_id'],
			'shop_id' => $order_info['shop_id'],
			'order_id' => $order_id,
			'order_project_id' => $project_order_id,
			'score' => $score,
			'lable_list' => $lable_list,
			'content' => $content,
			'masseur_id' => $masseur_id,
			'createtime' => time(),
		);
		
		$result=$this->evaluation->add($data_array);
		if($result)
		{
			$this->ordersproject->where("order_id=".$order_id." and masseur_id=".$masseur_id." and is_evaluation=0")->save(array('is_evaluation' => 1));
			$masseur_info=$this->shopmasseur->where("id=".$masseur_id)->find();
			if($masseur_info['device']!='')
			{
				unset($title);
				unset($content);
				$title="您有一个新的评价，请注意查看！";
				$content = "您有一个新的评价，请注意查看！";
				$device[] = $masseur_info['device'];
				$extra = array("push_type" => 2, "order_id" => $order_id);
				$audience='{"alias":'.json_encode($device).'}';
				$extras=json_encode($extra);
				$os=$masseur_info['os'];
				$res=jpush_send($title,$content,$audience,$os,$extras);
			}	
			$this->success("评价成功！");
		}else
		{
			$this->error("评价失败！");
		}
		
		
	}
		
}