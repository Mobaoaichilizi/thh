<?php
// +----------------------------------------------------------------------
// | 健康师分析
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Th2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class MasseuranalysisController extends ApibaseController
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
        $this->OrdersProject=M("OrdersProject"); //项目消费统计
        $this->OrdersProduct=M("OrdersProduct"); //产品消费统计
        $this->ShopItem=M("ShopItem"); //项目
        $this->ShopProduct=M("ShopProduct"); //产品
        $this->shopmasseur = M('ShopMasseur');//技师表
        $this->ordersreward = M('OrdersReward');//推荐提成表
        $this->orderspaytype = M('OrdersPaytype');//订单付款方式表
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
        //健康师列表
        $masseur_list = $this->shopmasseur->where("shop_id=".$this->shopid.' and state=1')->select();
        $masseur_name = array();
        foreach ($masseur_list as $key => $value) {
            $loop[$key]['nick_name'] = $value['nick_name'];
            $loop[$key]['loop_num'] = $this->OrdersProject->where('masseur_id='.$value['id'].' and is_del=0 and pay_time>'.$start_time.' and pay_time<'.$end_time)->count();
            $loop_num[$key] = $loop[$key]['loop_num'];
            //健康师昵称
            
            $masseur_name[$key] = !empty($value['masseur_name'])?$value['masseur_name']:'';
            //项目原价
            unset($project_price);
            $project_price = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('total_price');
            $project_price_data[$key]=!empty($project_price) ? $project_price : 0;
            $project_price_data_count+=$project_price_data[$key];
            //产品原价
            unset($product_price);
            $product_price = $this->OrdersProduct->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('total_price');
            $product_price_data[$key]=!empty($product_price) ? $product_price : 0;
            $product_price_data_count+=$product_price_data[$key];
            //实收金额
            unset($real_price);
            $orderproject_price = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('pay_money');
            $orderproduct_price = $this->OrdersProduct->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('pay_money');
            $real_price = $orderproject_price+$orderproduct_price;
            $real_price_data[$key]=!empty($real_price) ? $real_price : 0;
            $real_price_data_count+=$real_price_data[$key];
            //会员卡售卡/充值金额
            // unset($card);
            // $card = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=1 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('transaction_money');
            // $card_data[$key]=!empty($card) ? $card : 0;
            // $card_data_count+=$card_data[$key];
            // //次卡售卡/充值金额
            // unset($numcard);
            // $numcard = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=2 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('transaction_money');
            // $numcard_data[$key]=!empty($numcard) ? $numcard : 0;
            // $numcard_data_count+=$numcard_data[$key];
            // //期限卡售卡/充值金额
            // unset($deadlinecard);
            // $deadlinecard = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=3 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('transaction_money');
            // $deadlinecard_data[$key]=!empty($deadlinecard) ? $deadlinecard : 0;
            // $deadlinecard_data_count+=$deadlinecard_data[$key];
            // //疗程卡售卡/充值金额
            // $financials = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time." < createtime  and createtime <".($end_time+24*3600-1)." and type=1 and transaction_type=4 and status=1 and sellcard_masseur=".$value['id'])->select();
            // //排序只保留相同card_id的一条数据
            // $list = array();
            // if(!empty($financials)){
            //     foreach ($financials as $k=>$vo) {
            //         $id=intval($vo['card_id']);
            //         $list[$id]=isset($list[$id])?
            //                 (strtotime($vo['createtime'])>strtotime($list[$id]['createtime']))? $vo:$list[$id] : $vo;
            //     }
            // }
            // $list=array_values($list);
            // $arr1 = array_map(create_function('$n', 'return $n["createtime"];'), $list);
            // array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$list);
            // if(!empty($list)){
            //     foreach ($list as $k1 => $v1) {
            //         $card_cash[$k1] = !empty($v1['transaction_money'])?$v1['transaction_money']:0;
            //         $card_cash_count[$key]+=$card_cash[$k1];
            //     }
                
            // }
            // $coursecard_data[$key]=!empty($card_cash_count[$key]) ? $card_cash_count[$key] : 0;
            // $coursecard_data_count+=$coursecard_data[$key];
            // //项目提成
            // unset($project_reward);
            // $project_reward = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('project_reward');
            // $project_reward_data[$key]=!empty($project_reward) ? $project_reward : 0;
            // $project_reward_data_count+=$project_reward_data[$key];
            // //商品提成
            // unset($product_reward);
            // $product_reward =$this->OrdersProduct->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and is_del=0')->sum('product_reward');
            // $product_reward_data[$key]=!empty($product_reward) ? $product_reward : 0;
            // $product_reward_data_count+=$product_reward_data[$key];
            // //会员卡提成
            // unset($card_reward);
            // $card_reward = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=1 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('sellcard_reward');
            // $card_reward_data[$key]=!empty($card_reward) ? $card_reward : 0;
            // $card_reward_data_count+=$card_reward_data[$key];
            // //次卡提成
            // unset($numcard_reward);
            // $numcard_reward = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=2 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('sellcard_reward');
            // $numcard_reward_data[$key]=!empty($numcard_reward) ? $numcard_reward : 0;
            // $numcard_reward_data_count+=$numcard_reward_data[$key];
            // //期限卡提成
            // unset($deadlinecard_reward);
            // $deadlinecard_reward = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=3 and type=1 and status=1 and sellcard_masseur=".$value['id'])->sum('sellcard_reward');
            // $deadlinecard_reward_data[$key]=!empty($deadlinecard_reward) ? $deadlinecard_reward : 0;
            // $deadlinecard_reward_data_count+=$deadlinecard_reward_data[$key];

            // //疗程卡提成
            // $financials = $this->shopfinancial->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and transaction_type=4 and type=1 and status=1 and sellcard_masseur=".$value['id'])->select();
      
            // //排序只保留相同card_id的一条数据
            // $list = array();
            // if(!empty($financials)){
            //     foreach ($financials as $k=>$vo) {
            //         $id=intval($vo['card_id']);
            //         $list[$id]=isset($list[$id])?
            //                 (strtotime($vo['createtime'])>strtotime($list[$id]['createtime']))? $vo:$list[$id] : $vo;
            //     }
            // }
            // $list=array_values($list);
            // $arr1 = array_map(create_function('$n', 'return $n["createtime"];'), $list);
            // array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$list);
            // if(!empty($list)){
            //     foreach ($list as $k1 => $v1) {
            //         $coursecard_cash[$k1] = !empty($v1['sellcard_reward'])?$v1['sellcard_reward']:0;
            //         $coursecard_cash_count[$key]+=$coursecard_cash[$k1];
            //     }
                
            // }
            // $coursecard_reward_data[$key]=!empty($coursecard_cash_count[$key]) ? $coursecard_cash_count[$key] : 0;
            // $coursecard_reward_data_count+=$coursecard_reward_data[$key];


            // //推荐提成
            // unset($recommend_reward);
            // $recommend_reward = $this->ordersreward->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and is_del=0 and masseur_id=".$value['id'])->sum('total_reward');
            // $recommend_reward_data[$key]=!empty($recommend_reward) ? $recommend_reward : 0;
            // $recommend_reward_data_count+=$recommend_reward_data[$key];

            // //奖励提成
            // unset($prize_reward);
            // $loop_reward = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and is_del=0 and masseur_id=".$value['id'].' and types=1')->sum('loop_reward');
            // $point_reward = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and is_del=0 and masseur_id=".$value['id'].' and types=2')->sum('point_reward');
            // $add_reward = $this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1)." and is_del=0 and masseur_id=".$value['id'].' and types=3')->sum('add_reward');
            // $prize_reward_data[$key]=$loop_reward+$point_reward+$add_reward;
            // $prize_reward_data_count+=$prize_reward_data[$key];
            // //总提成
            // unset($all_reward_data1);
            // $all_reward_data1 = $project_reward_data[$key]+$product_reward_data[$key]+$card_reward_data[$key]+$numcard_reward_data[$key]+$deadlinecard_reward_data[$key]+$coursecard_reward_data[$key]+$recommend_reward_data[$key]+$prize_reward_data[$key];
            // $all_reward_data[$key]=!empty($all_reward_data1) ? $all_reward_data1 : 0;
            // $all_reward_data_count+=$all_reward_data[$key];

            //轮钟数
            unset($wheel);
            $wheel=$this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and types=1 and is_del=0')->count();
            $wheel_data[$key]=!empty($wheel) ? $wheel : 0;
            $wheel_data_count+=$wheel_data[$key];
            //点钟数
            unset($point);
            $point=$this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and types=2 and is_del=0')->count();
            $point_data[$key]=!empty($point) ? $point : 0;
            $point_data_count+=$point_data[$key];
            //加钟数
            unset($add);
            $add=$this->OrdersProject->where("shop_id=".$this->shopid." and ".$start_time.' < createtime  and createtime <'.($end_time+24*3600-1).' and masseur_id='.$value['id'].' and types=3 and is_del=0')->count();
            $add_data[$key]=!empty($add) ? $add : 0;
            $add_data_count+=$add_data[$key];
            //总钟数
            unset($all_data1);
            $all_data1 = $wheel_data[$key]+$point_data[$key]+$add_data[$key];
            $all_data[$key]=!empty($all_data1) ? $all_data1 : 0;
            $all_data_count+=$all_data[$key];

        }
        array_multisort($loop_num,SORT_DESC,$loop);
        $info['masseur_name']=$masseur_name;
        $info['project_price_data']=$project_price_data;
        $info['product_price_data']=$product_price_data;
        $info['real_price_data']=$real_price_data;
        // $info['card_data']=$card_data;
        // $info['numcard_data']=$numcard_data;
        // $info['deadlinecard_data']=$deadlinecard_data;
        // $info['coursecard_data']=$coursecard_data;
        // $info['project_reward_data']=$project_reward_data;
        // $info['product_reward_data']=$product_reward_data;
        // $info['card_reward_data']=$card_reward_data;
        // $info['numcard_reward_data']=$numcard_reward_data;
        // $info['deadlinecard_reward_data']=$deadlinecard_reward_data;
        // $info['coursecard_reward_data']=$coursecard_reward_data;
        // $info['recommend_reward_data']=$recommend_reward_data;
        // $info['prize_reward_data']=$prize_reward_data;
        // $info['all_reward_data']=$all_reward_data;
        $info['wheel_data']=$wheel_data;
        $info['point_data']=$point_data;
        $info['add_data']=$add_data;
        $info['all_data']=$all_data;
        unset($data);
        $data['code'] = 0;
        $data['msg'] = 'success';
        $data['loop'] = $loop;
        $data['data'] = $info;
        outJson($data);

        
    
    }

}
?>