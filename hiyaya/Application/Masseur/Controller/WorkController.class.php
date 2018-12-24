<?php
// +----------------------------------------------------------------------
// | 排钟列表
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: ALina
// +----------------------------------------------------------------------
namespace Masseur\Controller;
use Common\Controller\MasseurbaseController;
class WorkController extends MasseurbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopLeave = M("ShopLeave");//请假表
        $this->shopscheduling=M("ShopScheduling"); //排班表
        $this->ordersproject=M("OrdersProject"); //订单项目表
        $this->ordersproduct=M("OrdersProduct"); //订单产品表
        $this->where="shop_id=".$this->shop_id;
        $this->arr=array("1"=>"早班","2"=>"中班","3"=>"晚班","4"=>"休假","5"=>"请假");
    }
    //排钟列表
    public function index()
    {
        $date=strtotime(I('post.date')) ? strtotime(I('post.date')):time();
        $beginThismonth=mktime(0,0,0,date('m',$date),1,date('Y',$date));
        $endThismonth=mktime(23,59,59,date('m',$date),date('t',$date),date('Y',$date));
        $worklist=$this->shopscheduling->where("start_time > '".$beginThismonth."' and start_time <'".$endThismonth."'  and masseur_id=$this->masseur_id and  shop_id=".$this->shop_id )->field("chain_id,shop_id,masseur_id,start_time,status")->order("start_time asc")->select();
        $moring=0;
        $afternoon=0;
        $night=0;
        $workoff=0;
        $leave=0;
        foreach ($worklist as $k=>$v) {
            if($v['status']==1)
            {
                $moring+=1;
            }
            else if($v['status']==2)
            {
                $afternoon+=1;
            }
            else if($v['status']==3)
            {
                $night+=1;
            }
           else  if($v['status']==4)
            {
                $workoff+=1;
            }
            else if($v['status']==5)
            {
                $leave+=1;
            }
            $worklist[$k]['start_time']=date('Y-m-d',$v['start_time']);
            $worklist[$k]['y'] = date('Y', $v['start_time']);
            $worklist[$k]['status'] = $this->arr[$v['status']];
            $worklist[$k]['m'] = date('m', $v['start_time']);
            $worklist[$k]['d'] = date('d', $v['start_time']);


        }
        if(count($worklist)>0)
        {
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = $worklist;
            $data['count']=array("moring"=>$moring,"afternoon"=>$afternoon,"night"=>$night,"workoff"=>$workoff,"leave"=>$leave);
        }
        else
        {
            $data['code'] = 1;
            $data['msg'] = 'error';
            $data['data'] = '';
            $data['count']='';
        }
        outJson($data);
    }

}
?>