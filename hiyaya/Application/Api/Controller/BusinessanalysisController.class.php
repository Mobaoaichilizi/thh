<?php
// +----------------------------------------------------------------------
// | 营业日报分析
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Th2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class BusinessanalysisController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->shopfinancial=M("ShopFinancial"); //消费记录
        $this->shoporders=M("Orders"); //订单记录
        $this->shopmember=M("ShopMember"); //会员表
        //------start Alina-----
        $this->OrdersProject=M("OrdersProject"); //项目消费统计
        $this->OrdersProduct=M("OrdersProduct"); //产品消费统计
        $this->ShopItem=M("ShopItem"); //项目
        $this->ShopProduct=M("ShopProduct"); //产品
        //------end Alina-----

        $this->shopmasseur = M('ShopMasseur');//技师表
        $this->ordersreward = M('OrdersReward');//推荐提成表
        $this->orderspaytype = M('OrdersPaytype');//订单付款方式表
        $this->shopid=$this->shop_id;

        $this->start_time = I('request.start_time');
        $this->end_time = I('request.end_time');
        if(!empty($this->start_time)&&$this->end_time=='')
        {
            // $this->start_time=strtotime($this->start_time);
            //$this->end_time=strtotime($this->start_time)+24*3600-1;

            $this->start_time=mktime(0,0,0,date('m',$this->start_time),date('d',$this->start_time),date('Y',$this->start_time));
            $this->end_time=mktime(0,0,0,date('m',$this->start_time),date('d',$this->start_time)+1,date('Y',$this->start_time))-1;

            $this->assign("start_time",date('Y-m-d',$this->start_time));
            $this->assign("end_time",date('Y-m-d',$this->end_time));
        }
        else if($this->start_time=='' || $this->end_time=='')
        {
            //$this->start_time=time();
            //$this->end_time=time()+24*3600-1;

            $this->start_time=mktime(0,0,0,date('m'),date('d'),date('Y'));
            $this->end_time=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

            $this->assign("start_time",date('Y-m-d',$this->start_time));
            $this->assign("end_time",date('Y-m-d',$this->end_time));

        }else
        {
            $this->assign("start_time",$this->start_time);
            $this->assign("end_time",$this->end_time);
            $this->start_time=strtotime($this->start_time);
            $this->end_time=strtotime($this->end_time);

        }
    }
    //会员分析
    public function index()
    {
//        $start_time = I('request.start_time');
//        $end_time = I('request.end_time');
//
//        if($start_time=='' || $end_time=='')
//        {
//            $start_time=time()-24*3600*6;
//            $end_time=time();
//            $this->assign("start_time",date('Y-m-d',$start_time));
//            $this->assign("end_time",date('Y-m-d',$end_time));
//        }else
//        {
//            $this->assign("start_time",$start_time);
//            $this->assign("end_time",$end_time);
//            $start_time=strtotime($start_time);
//            $end_time=strtotime($end_time);
//        }
//
        $poor_day=round(($this->end_time-$this->start_time)/(24*3600));
        $redata=array();
        for($i=0;$i<$poor_day+1;$i++)
        {   
            $redata[$i]=date('Y-m-d',$this->start_time+$i*3600*24);
            $redata_time[$i]=date('m-d',$this->start_time+$i*3600*24);
            //新增订单收入
            unset($business);
            $business=$this->shoporders->where("status!=-1 AND shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < pay_time  and pay_time <'.(strtotime($redata[$i])+24*3600-1))->sum('pay_amount');
            $business_res[$i]=!empty($business) ? $business : 0;
            $business_res_count+=$business_res[$i];
            //新增售卡收入
            unset($sell_card);
            $sell_card=$this->shopfinancial->where("transaction_money > 0 and shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_type!=4")->sum('transaction_money');
            $sell_card_res[$i]=!empty($sell_card) ? $sell_card : 0;

            $financials = $this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i])." < createtime  and createtime <".(strtotime($redata[$i])+24*3600-1)." and type=1 and transaction_type=4")->select();

            //排序只保留相同card_id的一条数据
            $list = array();
            if(!empty($financials)){
                foreach ($financials as $k=>$vo) {
                    $id=intval($vo['card_id']);
                    $list[$id]=isset($list[$id])?
                            (strtotime($vo['createtime'])>strtotime($list[$id]['createtime']))? $vo:$list[$id] : $vo;
                }
            }
            $list=array_values($list);
            $arr1 = array_map(create_function('$n', 'return $n["createtime"];'), $list);
            array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$list);
            if(!empty($list)){
                foreach ($list as $key => $value) {
                    $card_cash[$key] = !empty($value['transaction_money'])?$value['transaction_money']:0;
                    $card_cash_count[$i]+=$card_cash[$key];
                }
                $card_cash_count_res[$i]=!empty($card_cash_count[$i]) ? $card_cash_count[$i] : 0;
            }
            $sell_card_res[$i]+=$card_cash_count_res[$i];
            $sell_card_res_count+=$sell_card_res[$i];
            
        }

        //会员消费统计
        $where=" shop_id=".$this->shopid." AND  identity=1 AND type=2 AND createtime>=".$this->start_time." AND createtime<=".$this->end_time;
        $memberfee=$this->shopfinancial->where($where)->sum('transaction_money');
        $income['memberfee']=!empty($memberfee) ? $memberfee*-1:0;
        //新增会员数
        $where="shop_id=".$this->shopid." AND createtime>=".$this->start_time." AND createtime<=".$this->end_time;
        $addmembernum=$this->shopmember->where($where)->count();
        $income['addmembernum']=!empty($addmembernum) ? $addmembernum:0;
        //上客人数
        $where=" types!=3 AND is_del=0 AND  shop_id=".$this->shopid." AND createtime>=".$this->start_time." AND createtime<=".$this->end_time;
        $ordermembernum=$this->OrdersProject->where($where)->count();
        $income['ordermembernum']=!empty($ordermembernum) ? $ordermembernum:0;
        //项目统计
        $where=" shop_id=".$this->shopid."  AND is_del=0 AND createtime>=".$this->start_time." AND createtime<=".$this->end_time;
        $projectmoney=$this->OrdersProject->where($where)->sum('pay_money');
        $income['projectmoney']=!empty($projectmoney) ? $projectmoney:0;
        //商品统计
        $productmoney=$this->OrdersProduct->where($where)->sum('pay_money');
        $income['productmoney']=!empty($productmoney) ? $productmoney:0;
        //加钟人数
        $where=" types=3  AND is_del=0 AND  shop_id=".$this->shopid." AND createtime>=".$this->start_time." AND createtime<=".$this->end_time;
        $addpointnum=$this->OrdersProject->where($where)->count();
        $income['addpointnum']=!empty($addpointnum) ? $addpointnum:0;
        //加钟率
        $addpointrate=number_format($addpointnum/$ordermembernum,2)*100;
        $income['addpointrate']=!empty($addpointrate) ? $addpointrate:0;
        //点钟人数
        $where=" types=2  AND is_del=0 AND  shop_id=".$this->shopid." AND createtime>=".$this->start_time." AND createtime<=".$this->end_time;
        $pointnum=$this->OrdersProject->where($where)->count();
        $income['pointnum']=!empty($pointnum) ? $pointnum:0;
        //点钟率
        $pointrate=number_format($pointnum/$ordermembernum,2)*100;
        $income['pointrate']=!empty($pointrate) ? $pointrate:0;


        unset($info);
        $info['redata_time']=$redata_time;
        $info['business_res']=$business_res;
        $info['sell_card_res']=$sell_card_res;
       // unset($income);
        $income['business_res_count'] = $business_res_count;
        $income['sell_card_res_count'] = $sell_card_res_count;
        unset($data);
        $data['code'] = 0;
        $data['msg'] = 'success';
        $data['data'] = $info;
        $data['income'] = $income;
        outJson($data);

    }
    public function detailist()
    {

        $act = I('request.act');
        $limit   =I('get.limit');
        $pagecur=I('get.page');
        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;

        if($act=='order')
        {
            //订单明细
            $where=" status!=-1 AND shop_id=".$this->shopid." AND pay_time>=".$this->start_time." AND pay_time<=".$this->end_time;
            $list = $this->shoporders
                    ->where($where)
                    ->page($pagecur.','.$pagenum)
                    ->field("id,
                    order_sn,
                    createtime,
                    status,
                    room_id,
                    total_amount,
                    pay_time,
                    pay_amount,
                    invoice_money")
                    ->select();
            $arr=array("-1"=>"取消订单","1"=>"待支付","2"=>"已完成");
            $totalamount=0;
            $payamount=0;
            $invoicemoney=0;
            foreach($list as $k=>$v)
            {
                $list[$k]['id']=$v['id'];
                $list[$k]['order_sn']=$v['order_sn'];
                $list[$k]['createtime']=date("y-m-d H:i:s",$v['createtime']);
                $list[$k]['status']=$arr[$v['status']];
                $list[$k]['room_id']=M("ShopRoom")->where("id=".$v['room_id'])->getField('room_name');
                $list[$k]['total_amount']=$v['total_amount'];
                $list[$k]['pay_time']=date("y-m-d H:i:s",$v['pay_time']);
                $list[$k]['pay_amount']=$v['pay_amount'];
                $list[$k]['invoice_money']= $v['invoice_money'];
                $totalamount+=$v['total_amount'];
                $invoicemoney+=$v['invoice_money'];
                $payamount+=$v['pay_amount'];
            }
            $countlist=array("id"=>'',"order_sn"=>'',"createtime"=>'',"status"=>'',"room_id"=>'合计',"total_amount"=>$totalamount,"invoice_money"=>$invoicemoney,"pay_time"=>"","pay_amount"=>$payamount);
            array_push($list,$countlist);
            $count   = $this->shoporders->where($where)->count();
            $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
            $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        }
        else if($act=='card')
        {
            //售卡明细

            $where=" transaction_type!=1 AND type=1 AND  shop_id=".$this->shopid." AND createtime>=".$this->start_time." AND createtime<=".$this->end_time;
            $list = $this->shopfinancial->where($where)->page($pagecur.','.$pagenum)
                    ->field("id,
                    card_id,
                    createtime,
                    transaction_type,
                    transaction_money
                   ")
                    ->select();
            $arr=array("2"=>"次卡","3"=>"期限卡","4"=>"疗程卡");
            $transactionmoney=0;
            foreach($list as $k=>$v)
            {
                $list[$k]['id']=$v['id'];
                $list[$k]['card_id']=$v['card_id'];
                $list[$k]['transaction_type']=$arr[$v['transaction_type']];
                $list[$k]['transaction_money']=$v['transaction_money'];
                $list[$k]['createtime']=date("y-m-d H:i:s",$v['createtime']);
                $transactionmoney+=$v['transaction_money'];
            }
            $countlist=array("id"=>'',"card_id"=>'',"transaction_type"=>'合计',"transaction_money"=>$transactionmoney,"createtime"=>'');
            array_push($list,$countlist);
            $count   = $this->shopfinancial->where($where)->count();
            $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
            $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);

        }
        else if($act=='memberfee') {
            $where = " shop_id=" . $this->shopid . " AND  identity=1 AND type=2 AND createtime>=" . $this->start_time . " AND createtime<=" . $this->end_time;
            $list = $this->shopfinancial->where($where)->page($pagecur.','.$pagenum)
                    ->field("id,
                    project_id,
                    createtime,
                    transaction_type,
                    transaction_money
                   ")->select();
            $arr = array("2" => "次卡", "3" => "期限卡", "4" => "疗程卡");
            $transactionmoney = 0;
            foreach ($list as $k => $v) {
                $list[$k]['id']=$v['id'];
                $list[$k]['project_id'] = $this->ShopItem->where("id=" . $v['project_id'])->getField("item_name");
                $list[$k]['transaction_type'] = $arr[$v['transaction_type']];
                $list[$k]['transaction_money'] = !empty($v['transaction_money']) ? $v['transaction_money'] * -1 : 0;
                $list[$k]['createtime'] = date("y-m-d H:i:s", $v['createtime']);
                $transactionmoney += $list[$k]['transaction_money'];
            }
            $countlist = array("id" => '', "project_id" => '', "transaction_type" => '合计', "transaction_money" => $transactionmoney, "createtime" => '');
            array_push($list, $countlist);
            $count   = $this->shopfinancial->where($where)->count();
            $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
            $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);

        }
        else if($act=='addmember')
        {
            //新增会员明细
            $where="shop_id=".$this->shopid." AND createtime>=".$this->start_time." AND createtime<=".$this->end_time;
            $list=$this->shopmember->where($where)->page($pagecur.','.$pagenum)
                ->field("id,
                    member_no,
                    member_name,
                    sex,
                    member_tel,
                    createtime")->select();
            foreach($list as $k=>$v)
            {
                $list[$k]['id']=$v['id'];
                $list[$k]['member_no']=$v['member_no'];
                $list[$k]['member_name']=$v['member_name'];
                $list[$k]['sex']=$v['sex'];
                $list[$k]['member_tel']=$v['member_tel'];
                $list[$k]['createtime']=date("y-m-d H:i:s",$v['createtime']);
            }
            $count   = $this->shopmember->where($where)->count();
            $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
            $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        }
        else if($act=='member')
        {
            //上客人数明细
            $where=" types!=3   AND is_del=0 AND  shop_id=".$this->shopid." AND createtime>=".$this->start_time." AND createtime<=".$this->end_time;
            $list=$this->OrdersProject->where($where)->page($pagecur.','.$pagenum)
                    ->field("id,
                    title,
                    types,
                    total_price,
                    pay_money,
                    createtime")->select();
            $arr=array("1"=>"轮钟","2"=>"点钟 ","3"=>"加钟");

            $payamount=0;
            foreach($list as $k=>$v)
            {
                $list[$k]['id']=$v['id'];
                $list[$k]['title']=$v['title'];
                $list[$k]['types']=$arr[$v['types']];
                $list[$k]['total_price']=$v['total_price'];
                $list[$k]['pay_money']=$v['pay_money'];
                $list[$k]['createtime']=date("y-m-d H:i:s",$v['createtime']);
                $payamount+=$v['pay_money'];
            }
            $countlist=array("id"=>'',"title"=>'',"types"=>'合计',"pay_money"=>$payamount,"createtime"=>'');
            array_push($list,$countlist);
            $count   = $this->OrdersProject->where($where)->count();
            $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
            $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        }
        else if($act=='project')
        {
            //项目明细
            $where=" shop_id=".$this->shopid."  AND is_del=0  AND createtime>=".$this->start_time." AND createtime<=".$this->end_time;
            $list=$this->OrdersProject->where($where)->page($pagecur.','.$pagenum)
                    ->field("id,
                    title,
                    types,
                    total_price,
                    createtime")->select();
            $arr=array("1"=>"轮钟","2"=>"点钟 ","3"=>"加钟");
            $payamount=0;
            foreach($list as $k=>$v)
            {
                $list[$k]['id']=$v['id'];
                $list[$k]['title']=$v['title'];
                $list[$k]['total_price']=$v['total_price'];
                $list[$k]['types']=$arr[$v['types']];
                $list[$k]['createtime']=date("y-m-d H:i:s",$v['createtime']);
                $payamount+=$v['total_price'];
            }
            $countlist=array("id"=>'',"title"=>'合计',"total_price"=>$payamount,"types"=>'',"createtime"=>'');
            array_push($list,$countlist);
            $count   = $this->OrdersProject->where($where)->count();
            $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
            $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        }
        else if($act=='product')
        {
            //商品明细
            $where=" shop_id=".$this->shopid." AND createtime>=".$this->start_time." AND createtime<=".$this->end_time;
            $list=$this->OrdersProduct->where($where)->page($pagecur.','.$pagenum)
                    ->field("id,
                    title,
                    unit_price,
                    number,
                    total_price,
                    createtime")->select();
            $arr=array("1"=>"轮钟","2"=>"点钟 ","3"=>"加钟");
            $payamount=0;
            $number=0;
            foreach($list as $k=>$v)
            {
                $list[$k]['id']=$v['id'];
                $list[$k]['title']=$v['title'];
                $list[$k]['unit_price']=$v['unit_price'];
                $list[$k]['number']=$v['number'];
                $list[$k]['total_price']=$v['total_price'];
                $list[$k]['createtime']=date("y-m-d H:i:s",$v['createtime']);
                $payamount+=$v['total_price'];
                $number+=$v['number'];
            }
            $countlist=array("id"=>'',"title"=>'',"unit_price"=>'合计',"number"=>$number,"total_price"=>$payamount,"createtime"=>'');
            array_push($list,$countlist);
            $count   = $this->OrdersProduct->where($where)->count();
            $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
            $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);

        }
        echo json_encode($data);
    }

}
?>