<?php
/**
 * Created by PhpStorm.
 * User: Alina
 * Date: 2018/9/29
 * Time: 14:34
 */
namespace Api\Controller;
use Common\Controller\ApibaseController;
class ProductanalysisController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopProduct=M("ShopProduct");
        $this->OrdersProduct=M("OrdersProduct");
        $this->shopid=$this->shop_id;

    }
    //产品统计
    //商品汇总统计列表
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
        $datalist=$this->ShopProduct->where($where)->page($pagecur.','.$pagenum)->select();
        $count=$this->ShopProduct->where($where)->count();
        $ordernum=0;
        $total_price=0;
        $pay_money=0;
        $fee=0;
        foreach ($datalist as $k=>$v)
        {
            $data[$k]['id']=$v['id'];
            $data[$k]['category_name']=M('ProductCategory')->where("id=".$v['category_id'])->getField('category_name');
            $data[$k]['product_name']   =$v['product_name'];
            $data[$k]['ordernum']=M('OrdersProduct')->where(" product_id=".$v['id']." AND ".$sqlwhere)->count();
            $ordernum+=$data[$k]['ordernum'];
            $data[$k]['total_price']=M('OrdersProduct')->where(" product_id=".$v['id']." AND ".$sqlwhere)->sum('total_price');
            $total_price+=$data[$k]['total_price'];
            $data[$k]['pay_money']=M('OrdersProduct')->where(" product_id=".$v['id']." AND ".$sqlwhere)->sum('pay_money');
            $pay_money+= $data[$k]['pay_money'];
            $data[$k]['fee']='';
            $fee+=$data[$k]['fee'];

        }
        $total=array("title"=>'合计',"ordernum"=>$ordernum,"total_price"=>$total_price,
            "pay_money"=>$pay_money,"fee"=>$fee);

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

    //商品明细统计列表
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
        $datalist=$this->OrdersProduct->where($where)->page($pagecur.','.$pagenum)->select();
        $count=$this->OrdersProduct->where($where)->count();
        $total_price=0;
        $pay_money=0;
        $fee=0;
        $product_reward=0;
        foreach ($datalist as $k=>$v)
        {
            $data[$k]['id']=$v['id'];
            $data[$k]['createtime']=date("Y-m-d H:i:s",$v['createtime']);
            $data[$k]['pay_time']=date("Y-m-d H:i:s",$v['pay_time']);
            $data[$k]['product_name']=M('ShopProduct')->where(" id=".$v['product_id'])->getField('product_name');
            $data[$k]['total_price']=$v['total_price'];
            $total_price+=$v['total_price'];
            $data[$k]['pay_money']=$v['pay_money'];
            $pay_money+=$v['pay_money'];
            $data[$k]['fee']='';
            $fee+=$data[$k]['fee'];
            $data[$k]['masseur_name']=M('ShopMasseur')->where(" id=".$v['masseur_id'])->getField('masseur_name');
            $data[$k]['product_reward']=$v['product_reward'];
            $product_reward+=$v['product_reward'];
            $data[$k]['payuser']=M('ShopUser')->where(" id=".$v['payuser_id'])->getField('username');
        }
        $total=array( 'title'=>'合计', 'total_price'=>$total_price,'pay_money'=>$pay_money,
            'fee'=>$fee,'product_reward'=>$product_reward);


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