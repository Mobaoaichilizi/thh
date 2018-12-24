<?php
/**
 * Created by PhpStorm.
 * User: Alina
 * Date: 2018/9/29
 * Time: 14:34
 */
namespace Api\Controller;
use Common\Controller\ApibaseController;
class ItemanalysisController extends ApibaseController
{

    public function _initialize()
    {
        parent::_initialize();
        $this->ShopItem=M("ShopItem");
        $this->OrdersProject=M("OrdersProject");
        $this->shopid=$this->shop_id;
    }
    //项目汇总统计列表
    public function index()
    {
        $pagesize    = I('post.pagesize','','intval');
        $pagesize    = !empty($pagesize) ? $pagesize :10; //每页显示个数
        $pagecur     = I('post.pagecur','','intval');
        $pagecur     = !empty($pagecur)? $pagecur:1;//当前第几个页
        $pagestart   =($pagecur-1)*$pagesize;
        $where=" shop_id=".$this->shopid;
        $sqlwhere=" is_del=0 AND shop_id=".$this->shopid;
        $startdate=I("startdate");
        $enddate=I("enddate");
        if(!empty($startdate))
        {
            $sqlwhere.=" and createtime>='".strtotime($startdate)."'";
        }
        if(!empty($enddate))
        {
            $sqlwhere.=" and createtime<='".strtotime($enddate)."'";
        }
        $datalist=$this->ShopItem->where($where)->page($pagecur.','.$pagesize)->select();
        $count=$this->ShopItem->where($where)->count();
        $ordernum=0;
        $total_price=0;
        $pay_money=0;
        $fee=0;
        $ordersnum=0;
        $loop_rewardnum=0;
        $point_rewardnum=0;
        $add_rewardnum=0;
        foreach ($datalist as $k=>$v)
        {
            $data[$k]['id']=$v['id'];
            $data[$k]['category_name']      =M('ItemCategory')->where("id=".$v['category_id'])->getField('category_name');
            $data[$k]['item_name']          =$v['item_name'];
            $data[$k]['ordernum']           =M('OrdersProject')->where("  project_id=".$v['id']." AND ".$sqlwhere)->count();
            $ordernum+=$data[$k]['ordernum'];
            $data[$k]['total_price']        =M('OrdersProject')->where("  project_id=".$v['id']." AND ".$sqlwhere)->sum('total_price');
            $total_price+=$data[$k]['total_price'];
            $data[$k]['pay_money']          =M('OrdersProject')->where("  project_id=".$v['id']." AND ".$sqlwhere)->sum('pay_money');
            $pay_money+=$data[$k]['pay_money'];
            $data[$k]['fee']                 ='';
            $fee+=$data[$k]['fee'];
            $data[$k]['ordersnum']           =M('OrdersProject')->where("  project_id=".$v['id']." AND ".$sqlwhere)->count();
            $ordersnum+=$data[$k]['ordersnum'];
            $data[$k]['loop_rewardnum']     =M('OrdersProject')->where(" types=1 AND project_id=".$v['id']." AND ".$sqlwhere)->count();
            $data[$k]['point_rewardnum']    =M('OrdersProject')->where(" types=2 AND project_id=".$v['id']." AND ".$sqlwhere)->count();
            $data[$k]['add_rewardnum']      =M('OrdersProject')->where(" types=3 AND project_id=".$v['id']." AND ".$sqlwhere)->count();
            $loop_rewardnum+=$data[$k]['loop_rewardnum'];
            $point_rewardnum+=$data[$k]['point_rewardnum'];
            $add_rewardnum+=$data[$k]['add_rewardnum'];

        }
        $total=array('title'=>'合计','ordernum'=>$ordernum,'total_price'=>$total_price,'pay_money'=>$pay_money,
            'fee'=>$fee,'ordersnum'=>$ordersnum,'loop_rewardnum'=>$loop_rewardnum,'point_rewardnum'=>$point_rewardnum,'add_rewardnum'=>$add_rewardnum
        );

        $datapages=intval($count/$pagesize);
        if($count%$pagesize>0)
        {
            $datapages=$datapages+1;
        }
        if($count>0)
        {
            $result=array("code"=>0,"datapages"=>$datapages,"count"=>$count,"msg"=>"","data"=>$data,"total"=>$total);
        }
        else
        {
            $result=array("code"=>0,"datapages"=>0,"count"=>0,"msg"=>"暂无数据");
        }
        outJson($result);

    }

    //项目明细统计列表
    public function detail()
    {
        $pagesize    = I('post.pagesize','','intval');
        $pagesize    = !empty($pagesize) ? $pagesize :10; //每页显示个数
        $pagecur     = I('post.pagecur','','intval');
        $pagecur     = !empty($pagecur)? $pagecur:1;//当前第几个页
        $pagestart   =($pagecur-1)*$pagesize;

        $where=" is_del=0 AND shop_id=".$this->shopid;
        $startdate=I("startdate");
        $enddate=I("enddate");
        if(!empty($startdate))
        {
            $where.=" and createtime>='".strtotime($startdate)."'";
        }
        if(!empty($enddate))
        {
            $where.=" and createtime<='".strtotime($enddate)."'";
        }
        $datalist=$this->OrdersProject->where($where)->page($pagecur.','.$pagenum)->select();
        $count=$this->OrdersProject->where($where)->count();

        $total_price=0;
        $pay_money=0;
        $fee=0;
        $project_reward=0;
        foreach ($datalist as $k => $v) {
            $data[$k]['id'] =$v['id'];
            $data[$k]['createtime']=date("Y-m-d H:i:s",$v['createtime']);
            $data[$k]['pay_time']=date("Y-m-d H:i:s",$v['pay_time']);
            $data[$k]['item_name'] = M('ShopItem')->where(" id=" . $v['project_id'])->getField('item_name');
            $data[$k]['total_price'] =$v['total_price'];
            $total_price+=$data[$k]['total_price'];
            $data[$k]['pay_money'] =$v['pay_money'];
            $pay_money+=$data[$k]['pay_money'];
            $data[$k]['fee'] ='';
            $fee+=$data[$k]['fee'];
            $data[$k]['masseur_name'] = M('ShopMasseur')->where(" id=" . $v['masseur_id'])->getField('masseur_name');
            $data[$k]['project_reward'] =$v['project_reward'];
            $project_reward+= $data[$k]['project_reward'];
            $data[$k]['payuser'] = M('ShopUser')->where(" id=" . $v['payuser_id'])->getField('username');
        }
        $total=array(
            'title'=>'合计',
            'total_price'=>$total_price,
            'pay_money'=>$pay_money,
            'fee'=>$fee,
            'project_reward'=>$project_reward);

        $datapages=intval($count/$pagesize);
        if($count%$pagesize>0)
        {
            $datapages=$datapages+1;
        }
        if($count>0)
        {
            $result=array("code"=>0,"datapages"=>$datapages,"count"=>$count,"msg"=>"","data"=>$data,"total"=>$total);
        }
        else
        {
            $result=array("code"=>0,"datapages"=>0,"count"=>0,"msg"=>"暂无数据");
        }
        outJson($result);
    }





}