<?php
// +----------------------------------------------------------------------
// | 门店列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class LiaochengController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopLiaocheng = D("ShopLiaocheng");
        $this->where=" chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
        $item=M('ShopItem')->where($this->where)->select();
        $this->data=array('chain_id'=>$this->chain_id,'shop_id'=>$this->shop_id,'item'=>$this->item);
    }
    public function index()
    {
        $pagesize    = I('post.pagesize','','intval');
        $pagesize    = !empty($pagesize) ? $pagesize :10; //每页显示个数
        $pagecur     = I('post.pagecur','','intval');
        $pagecur     = !empty($pagecur)? $pagecur:1;//当前第几个页
        $pagestart   =($pagecur-1)*$pagesize;
        $keywords    = trim(I('post.keywords','','htmlspecialchars'));
        $category_id = I('post.category_id','','intval');
        if(!empty($keywords))
        {
            $this->where.=" and  package_name like '%".$keywords."%'";
        }
        if(!empty($category_id))
        {
            $this->where.=" AND category_id=".$category_id;
        }
        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $list    = $this->ShopLiaocheng->where($this->where)->order('id')->page($pagestart.','.$pagesize)->select();
        foreach ($list as $key=> $val)
        {
            $list[$key]['index']         = Getzimu($val['package_name']);
            $list[$key]['chain_id'] =M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id'] =M('shop')->where('id='.$val['shop_id'])->getField('shop_name');
            $list[$key]['address'] =M('shop')->where('id='.$val['shop_id'])->getField('address');
            $item=unserialize($val['item']);
            $itemlist=array();
            foreach ($item as $k=>$v)
            {
                $itemlist[$k]['item_name']       = M('ShopItem')->where('id='.$v['item_detail'])->getField('item_name');
                $itemlist[$k]['item_price']      = M('ShopItem')->where('id='.$v['item_detail'])->getField('item_price');
                $itemlist[$k]['item_duration']   = M('ShopItem')->where('id='.$v['item_detail'])->getField('item_duration');
                $itemlist[$k]['number']=$v['number'];
            }
            $list[$key]['itemlist']=$itemlist;
            $list[$key]['createtime']=date("y-m-d H:i:s",$val['createtime']);
            if($val['state']==1)
            {
                $list[$key]['state']='启用';
            }
            else
            {
                $list[$key]['state']='锁定';
            }
            if($val['is_discount']==1)
            {
                $list[$key]['is_discount']='开';
            }
            else
            {
                $list[$key]['is_discount']='关';
            }
        }
        $count   = $this->ShopLiaocheng->where($this->where)->count();
        $datapages=intval($count/$pagesize);
        if($count%$pagesize>0)
        {
            $datapages=$datapages+1;
        }
        if($count>0)
        {
            $result=array("code"=>0,"datapages"=>$datapages,"count"=>$count,"msg"=>"","data"=>$list);
        }
        else
        {
            $result=array("code"=>0,"datapages"=>0,"count"=>0,"msg"=>"暂无数据","data"=>'');
        }
        outJson($result);
    }

}
?>