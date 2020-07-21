<?php
// +----------------------------------------------------------------------
// | 数据分析
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Th2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Masseur\Controller;
use Common\Controller\MasseurbaseController;
class AnalysisController extends MasseurbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->orders = M("Orders");//订单表
        $this->shopmasseur = M("ShopMasseur");//技师表
        $this->ordersproject=M("OrdersProject"); //订单项目表
        $this->ordersproduct=M("OrdersProduct"); //订单产品表
        $this->shopitem=M("ShopItem"); //项目表
        $this->evaluation=M("Evaluation"); //评价表
        $this->where="shop_id=".$this->shop_id;

    }
    //技师排名
    public function ranking()
    {
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
        $masseur_lists = $this->shopmasseur->where($this->where." and state=1")->order('createtime desc')->select();
        foreach ($masseur_lists as $key => $value) {
            //轮钟
            // $loop[$key]['nick_name'] = $value['nick_name'];
            // $loop[$key]['cover'] = $value['cover'];
            // if($value['cover']){
            //     $loop[$key]['cover'] = $this->host_url.$value['cover'];
            // }
            // $loop[$key]['loop_num'] = $this->ordersproject->where('masseur_id='.$value['id'].' and types=1 and is_del=0 and pay_time>'.$start_time.' and pay_time<'.$end_time)->count();
            // $loop_num[$key] = $loop[$key]['loop_num'];

            //评分
            $all_score = $this->evaluation->where($this->where.' and masseur_id='.$value['id'].' and types=1 and createtime>'.$start_time.' and createtime<'.$end_time)->sum('score');
            $count = $this->evaluation->where($this->where.' and masseur_id='.$value['id'].' and types=1 and createtime>'.$start_time.' and createtime<'.$end_time)->count();
            $score[$key]['nick_name'] = $value['nick_name'];
            $score[$key]['cover'] = $value['cover'];
            if($value['cover']){
                $score[$key]['cover'] = $this->host_url.$value['cover'];
            }
            $score[$key]['score'] = round($all_score/$count,2);
            $score_num[$key] = $score[$key]['score'];
            //业绩
            $project_amount = $this->ordersproject->where($this->where.' and is_del=0 and masseur_id='.$value['id'].'  and pay_time>'.$start_time.' and pay_time<'.$end_time)->sum('pay_money');
            $product_amount = $this->ordersproduct->where($this->where.' and is_del=0 and masseur_id='.$value['id'].'  and pay_time>'.$start_time.' and pay_time<'.$end_time)->sum('pay_money');
            $amount[$key]['nick_name'] = $value['nick_name'];
            $amount[$key]['cover'] = $value['cover'];
            if($value['cover']){
                $amount[$key]['cover'] = $this->host_url.$value['cover'];
            }
            $amount[$key]['pay_money'] = $project_amount+$product_amount;
            $amount_num[$key] = $amount[$key]['pay_money'];
            //点钟
            $point[$key]['nick_name'] = $value['nick_name'];
            $point[$key]['cover'] = $value['cover'];
            if($value['cover']){
                $point[$key]['cover'] = $this->host_url.$value['cover'];
            }
            $point[$key]['point_num'] = $this->ordersproject->where('masseur_id='.$value['id'].' and types=2 and is_del=0  and pay_time>'.$start_time.' and pay_time<'.$end_time)->count();
            $point_num[$key] = $point[$key]['point_num'];
            $add[$key]['nick_name'] = $value['nick_name'];
            $add[$key]['cover'] = $value['cover'];
            if($value['cover']){
                $add[$key]['cover'] = $this->host_url.$value['cover'];
            }
            $add[$key]['add_num'] = $this->ordersproject->where('masseur_id='.$value['id'].' and types=3 and is_del=0  and pay_time>'.$start_time.' and pay_time<'.$end_time)->count();
            $add_num[$key] = $add[$key]['add_num'];
        }
        array_multisort($score_num,SORT_DESC,$score);
        array_multisort($amount_num,SORT_DESC,$amount);
        array_multisort($point_num,SORT_DESC,$point);
        array_multisort($add_num,SORT_DESC,$add);
        unset($data);
        $data['code'] = 0;
        $data['msg'] = "success";
        $data['score'] = $score;
        $data['amount'] = $amount;
        $data['point'] = $point;
        $data['add'] = $add;
        $data['start_time'] = date("Y-m-d",$start_time);
        $data['end_time'] = date("Y-m-d",$end_time);
        outJson($data);
        
    }
    //我的业绩
    public function achievement(){
        $start_time = I('request.start_time');
        $end_time = I('request.end_time');
        if($start_time=='' || $end_time=='')
        {
            $start_time=time()-24*3600*6;
            $end_time=time();
        }else
        {
            $start_time = strtotime($start_time);

            $end_time=strtotime($end_time."+1 day -1 second");
        }
        $result = $this->ordersproject->where($this->where." and masseur_id=".$this->masseur_id." and is_del=0 and up_time>".$start_time." and up_time<".$end_time)->order('up_time desc')->select();
        if($result){
            foreach ($result as $key => $value) {
                $info[$key]['id'] = $value['id'];
                $info[$key]['order_id'] = $value['order_id'];
                $info[$key]['types'] = $value['types'];
                $info[$key]['title'] = $value['title'];
                $info[$key]['unit_price'] = $value['unit_price'];
                $info[$key]['duration'] = $value['duration'];
                $info[$key]['up_time'] = date('Y-m-d H:i',$value['up_time']);
            }
        }else{
            $info = array();
        }
        unset($data);
        $data['code'] = 0;
        $data['msg'] = "success";
        $data['info'] = $info;
        $data['start_time'] = date("Y-m-d",$start_time);
        $data['end_time'] = date("Y-m-d",$end_time);
        outJson($data);


    }
    
}
?>