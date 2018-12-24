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
        $this->shopid=$this->shop_id;

    }
    //会员分析
    public function index()
    {
        $start_time = I('request.start_time');
        $end_time = I('request.end_time');
        
        if($start_time=='' || $end_time=='')
        {
            $start_time=time()-24*3600*6;
            $end_time=time();
            $this->assign("start_time",date('Y-m-d',$start_time));
            $this->assign("end_time",date('Y-m-d',$end_time));
        }else
        {
            $this->assign("start_time",$start_time);
            $this->assign("end_time",$end_time);
            $start_time=strtotime($start_time);
            $end_time=strtotime($end_time);
        }
       
        $poor_day=round(($end_time-$start_time)/(24*3600));
        $redata=array();
        for($i=0;$i<$poor_day+1;$i++)
        {   
            $redata[$i]=date('Y-m-d',$start_time+$i*3600*24);
            $redata_time[$i]=date('m-d',$start_time+$i*3600*24);
            //新增订单收入
            unset($business);
            $business=$this->shoporders->where("shop_id=".$this->shopid." and ".strtotime($redata[$i]).' < pay_time  and pay_time <'.(strtotime($redata[$i])+24*3600-1))->sum('pay_amount');
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
        unset($info);
        $info['redata_time']=$redata_time;
        $info['business_res']=$business_res;
        $info['sell_card_res']=$sell_card_res;
        unset($income);
        $income['business_res_count'] = $business_res_count;
        $income['sell_card_res_count'] = $sell_card_res_count;
        unset($data);
        $data['code'] = 0;
        $data['msg'] = 'success';
        $data['data'] = $info;
        $data['income'] = $income;
        outJson($data);

        
    
    }

}
?>