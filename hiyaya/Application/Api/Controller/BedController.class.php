<?php
// +----------------------------------------------------------------------
// | 门店列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class BedController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopBed = M("ShopBed");
        $this->where=" chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
        $category=M('RoomCategory')->where($this->where)->select();
        $this->data=array('chain_id'=>$this->chain_id,'shop_id'=>$this->shop_id,'category'=>$this->category);
    }

    public function index()
    {

        $pagesize    = I('post.pagesize','','intval');
        $pagesize    = !empty($pagesize) ? $pagesize :10; //每页显示个数
        $pagecur     = I('post.pagecur','','intval');
        $pagecur     = !empty($pagecur)? $pagecur:1;//当前第几个页
        $pagestart   =($pagecur-1)*$pagesize;
        $keywords    = trim(I('post.keywords','','htmlspecialchars'));
        $room_id = I('post.room_id','','intval');
        if(!empty($keywords))
        {
            $this->where.=" and  room_no like '%".$keywords."%'";
        }
        if(!empty($room_id))
        {
            $this->where.=" AND room_id=".$room_id;
        }
        $list    = $this->ShopBed->where($this->where)->order('id')->page($pagestart.','.$pagesize)->select();
        foreach ($list as $key=> $val)
        {
            $list[$key]['chain_id']      =M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id']       =M('shop')->where('id='.$val['shop_id'])->getField('shop_name');
            $list[$key]['room_id']   =M('ShopRoom')->where('id='.$val['room_id'])->getField('room_name');
            if($val['state']==1)
            {
                $list[$key]['state']='启用';
            }
            else
            {
                $list[$key]['state']='锁定';
            }
        }
        $count   = $this->ShopBed->where($this->where)->count();
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