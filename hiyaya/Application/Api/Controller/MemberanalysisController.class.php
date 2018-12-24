<?php
// +----------------------------------------------------------------------
// | 会员分析
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Th2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class MemberanalysisController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->shopmember=M("ShopMember"); //会员表
        $this->shopnumcard=M("ShopNumcard"); //次卡
        $this->shopdeadlinecard=M("ShopDeadlinecard"); //期限卡
        $this->shopcoursecard=M("ShopCoursecard"); //疗程卡
        $this->shopfinancial=M("ShopFinancial"); //消费记录
        $this->shoporders=M("Orders"); //订单记录
        $this->shopid=$this->shop_id;

    }
    //会员分析
    public function index()
    {
        
        //会员数量
        $member_count=$this->shopmember->where("shop_id=".$this->shopid)->count();
        $info['member_count']=!empty($member_count) ? $member_count : 0;
        //会员卡余额
        $member_balance=$this->shopmember->where("shop_id=".$this->shopid)->sum("balance");
        $info['member_balance']=!empty($member_balance) ? $member_balance : 0;
        
        //次卡充值余额
        $numcard_balance=$this->shopnumcard->where("shop_id=".$this->shopid)->sum("numcard_money");
        $info['numcard_balance']=!empty($numcard_balance) ? $numcard_balance : 0;
        
        //疗程卡充值余额
        $coursecard_balance=$this->shopcoursecard->where("shop_id=".$this->shopid)->sum("card_money");
        $info['coursecard_balance']=!empty($coursecard_balance) ? $coursecard_balance : 0;
        
        //折线图新增会员和充值金额
        $start_time = I('request.start_time');
        $end_time = I('request.end_time');
        
        
        if($start_time=='' || $end_time=='')
        {
            $start_time=time()-24*3600*6;
            $end_time=time();
        }else
        {
            $start_time=strtotime($start_time);
            $end_time=strtotime($end_time."+1 day -1 second");
        }
        
        //$datetime7=time()-24*3600*6;
        $poor_day=round(($end_time-$start_time)/(24*3600));
        $redata=array();
        $numberdata=array();
        for($i=0;$i<$poor_day+1;$i++)
        {   
            $redata[$i]=date('Y-m-d',$start_time+$i*3600*24);
            $redata_time[$i]=date('m-d',$start_time+$i*3600*24);
            //新增会员数量
            unset($member_res);
            $member_res=$this->shopmember->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1))->count();
            $member_data[$i]=!empty($member_res) ? $member_res : 0;
            $member_data_count+=$member_data[$i];
            //充值金额
            // unset($topup_res);
            // $topup_res=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and (transaction_type=1 or transaction_type=2 or transaction_type=3 or transaction_type=4)")->sum("transaction_money");
            // $topup_money[$i]=!empty($topup_res) ? $topup_res : 0;
            //会员卡充值
            $membercard_array_prepaid[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and transaction_type=1")->sum("transaction_money");
            $membercard_array_prepaid[$i]=!empty($membercard_array_prepaid[$i]) ? $membercard_array_prepaid[$i] : 0;
            $membercard_prepaid+=$membercard_array_prepaid[$i]; 
            //次卡充值
            $numcard_array_prepaid[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and transaction_type=2")->sum("transaction_money");
            $numcard_array_prepaid[$i]=!empty($numcard_array_prepaid[$i]) ? $numcard_array_prepaid[$i] : 0;
            $numcard_prepaid+=$numcard_array_prepaid[$i];
            //期限卡充值 
            $deadlinecard_array_prepaid[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and transaction_type=3")->sum("transaction_money");
            $deadlinecard_array_prepaid[$i]=!empty($deadlinecard_array_prepaid[$i]) ? $deadlinecard_array_prepaid[$i] : 0;
            $deadlinecard_prepaid+=$deadlinecard_array_prepaid[$i];
          
            //疗程卡充值 
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
                
            }
            $coursecard_array_prepaid[$i]=!empty($card_cash_count[$i]) ? $card_cash_count[$i] : 0;
            $coursecard_prepaid+=$coursecard_array_prepaid[$i];

            // $coursecard_array_prepaid[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money > 0 and transaction_type=4")->sum("transaction_money");
            // $coursecard_array_prepaid[$i]=!empty($coursecard_array_prepaid[$i]) ? $coursecard_array_prepaid[$i] : 0;
            // $coursecard_prepaid+=$coursecard_array_prepaid[$i];

            //充值金额
            unset($topup_res);
            $topup_res = $membercard_array_prepaid[$i]+$numcard_array_prepaid[$i]+$deadlinecard_array_prepaid[$i]+$coursecard_array_prepaid[$i];
            $topup_money[$i]=!empty($topup_res) ? $topup_res : 0;
            //会员卡消费
            $membercard_array_consumption[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money < 0 and transaction_type=1")->sum("transaction_money");
            $membercard_array_consumption[$i]=!empty($membercard_array_consumption[$i]) ? $membercard_array_consumption[$i] : 0;
            $membercard_consumption+=(-1)*$membercard_array_consumption[$i];
            //次卡消费
            $numcard_array_consumption[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money < 0 and transaction_type=2")->sum("transaction_money");
            $numcard_array_consumption[$i]=!empty($numcard_array_consumption[$i]) ? $numcard_array_consumption[$i] : 0;
            $numcard_consumption+=(-1)*$numcard_array_consumption[$i];
            //疗程卡消费 
            $coursecard_array_consumption[$i]=$this->shopfinancial->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1)." and transaction_money < 0 and transaction_type=4")->sum("transaction_money");
            $coursecard_array_consumption[$i]=!empty($coursecard_array_consumption[$i]) ? $coursecard_array_consumption[$i] : 0;
            $coursecard_consumption+=(-1)*$coursecard_array_consumption[$i];
        }
        $redata_array=$redata;
        $member_data_array=$member_data;
        $topup_money_array=$topup_money;
        unset($redata);
        $redata['redata_time']=$redata_time;
        $redata['member_data']=$member_data;
        $redata['topup_money']=$topup_money;
        
        $prepaid['membercard'] = $membercard_prepaid;
        $prepaid['numcard'] = $numcard_prepaid;
        $prepaid['deadlinecard'] = $deadlinecard_prepaid;
        $prepaid['coursecard'] = $coursecard_prepaid;
        
        $consumption['membercard'] = $membercard_consumption;
        $consumption['numcard'] = $numcard_consumption;
        $consumption['coursecard'] = $coursecard_consumption;

        $tabledata['member_num'] = $member_data;
        $tabledata['membercard_prepaid'] = $membercard_array_prepaid;
        $tabledata['numcard_prepaid'] = $numcard_array_prepaid;
        $tabledata['deadlinecard_prepaid'] = $deadlinecard_array_prepaid;
        $tabledata['coursecard_prepaid'] = $coursecard_array_prepaid;
        $tabledata['membercard_consumption'] = $membercard_array_consumption;
        $tabledata['numcard_consumption'] = $numcard_array_consumption;
        $tabledata['coursecard_consumption'] = $coursecard_array_consumption;
        unset($data);
        $data['code'] = 0;
        $data['msg'] = 'success';
        $data['data'] = $info;
        $data['redata'] = $redata;
        $data['prepaid'] = $prepaid;
        $data['consumption'] = $consumption;
        $data['tabledata'] = $tabledata;
        $data['start_time'] = date("Y-m-d",$start_time);
        $data['end_time'] = date("Y-m-d",$end_time);
        outJson($data);

        
    
    }

}
?>