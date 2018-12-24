<?php
// +----------------------------------------------------------------------
// | 门店列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class ProductController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopProduct = D("ShopProduct");
        $this->where=" chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
        $category=M('ProductCategory')->where($this->where)->select();
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
        if(!empty($keywords))
        {
            $this->where.=" AND product_name like '%".$keywords."%'";
        }
        $list    = $this->ShopProduct->where($this->where)->order('id')->page($pagestart.','.$pagesize)->select();
        foreach ($list as $key=> $val)
        {
            $list[$key]['index']        = Getzimu($val['product_name']);
            $list[$key]['chain_id']     = M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id']      = M('shop')->where('id='.$val['shop_id'])->getField('shop_name');
            $list[$key]['category_id']  = M('ProductCategory')->where('id='.$val['category_id'])->getField('category_name');
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
			$list[$key]['cover']=$this->host_url.$list[$key]['cover'];
        }
        $count   = $this->ShopProduct->where($this->where)->count();
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
    //删除产品
    public  function del()
    {
        $id = I('post.id',0,'intval');
        $res=$this->ShopProduct->where("id=".$id)->find();
        if($res['state']==1)
        {
            $result=array("code"=>1,"msg"=>"启用中的产品禁止删除！");
        }
        if ($this->ShopProduct->delete($id)!==false)
        {
            $result=array("code"=>0,"msg"=>"删除成功！");
        }
        else
        {
            $result=array("code"=>1,"msg"=>"删除失败！");
        }
        outJson($result);
    }
    //下架产品
    public  function close()
    {
        $id = I('post.id',0,'intval');
        $data=array("state"=>0);
        $res=$this->ShopProduct->where('id='.$id)->save($data);
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
    //上架产品
    public  function open()
    {
        $id = I('post.id',0,'intval');
        $data=array("state"=>1);
        $res=$this->ShopProduct->where('id='.$id)->save($data);
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