<?php
// +----------------------------------------------------------------------
// | 门店列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class ItemController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopItem = D("ShopItem");
        $this->where=" chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
        $category=M('ItemCategory')->where($this->where)->select();
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
        $category_id = I('post.category_id','','intval');
        if(!empty($keywords))
        {
            $this->where.=" and  item_name like '%".$keywords."%'";
        }
        if(!empty($category_id))
        {
            $this->where.=" AND category_id=".$category_id;
        }
        $list    = $this->ShopItem->where($this->where)->order('id')->page($pagestart.','.$pagesize)->select();
        foreach ($list as $key=> $val)
        {
            $list[$key]['index']         = Getzimu($val['item_name']);
            $list[$key]['chain_id']      = M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id']       = M('shop')->where('id='.$val['shop_id'])->getField('shop_name');
            $list[$key]['category_id']   = M('ItemCategory')->where('id='.$val['category_id'])->getField('category_name');
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
                $list[$key]['is_discount']='是';
            }
            else
            {
                $list[$key]['is_discount']='否';
            }
        }
        $count   = $this->ShopItem->where($this->where)->count();
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

    //删除项目
    public  function del()
    {
        $id = I('post.id',0,'intval');
        $res=$this->ShopItem->where("id=".$id)->find();
        if($res['state']==1)
        {
            $result=array("code"=>1,"msg"=>"启用中的项目禁止删除！");
        }
        if ($this->ShopItem->delete($id)!==false)
        {
            $result=array("code"=>0,"msg"=>"删除成功！");
        }
        else
        {
            $result=array("code"=>1,"msg"=>"删除失败！");
        }
        outJson($result);
    }
    //下架项目
    public  function close()
    {
        $id = I('post.id',0,'intval');
        $data=array("state"=>0);
        $res=$this->ShopItem->where('id='.$id)->save($data);
        if ($res !== false)
        {
            $result=array("code"=>0,"msg"=>"操作成功");
        }
        else
        {
            $result=array("code"=>1,"msg"=>"操作失败");
        }
        outJson($result);
    }
    //上架项目
    public  function open()
    {
        $id = I('post.id',0,'intval');
        $data=array("state"=>1);
        $res=$this->ShopItem->where('id='.$id)->save($data);
        if ($res !== false)
        {
            $result=array("code"=>0,"msg"=>"操作成功");
        }
        else
        {
            $result=array("code"=>1,"msg"=>"操作失败");
        }
        outJson($result);
    }
}
?>