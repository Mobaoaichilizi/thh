<?php
// +----------------------------------------------------------------------
// | 门店列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class MasseurController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopMasseur = D("ShopMasseur");
        $this->where=" chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
        $item=M('ShopItem')->where($this->where)->select();
        $category=M('MasseurCategory')->where($this->where)->select();
        $this->data=array('chain_id'=>$this->chain_id,'shop_id'=>$this->shop_id,'category'=>$this->category,'item'=>$item);
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
        $tel         = trim(I('post.tel','','htmlspecialchars'));
        $sex         = trim(I('post.sex','','htmlspecialchars'));
        $master_item = trim(I('post.master_item','','intval'));
        $masseur_sn  = trim(I('post.masseur_sn','','htmlspecialchars'));
        if(!empty($keywords))
        {
            $this->where.=" and  masseur_name like '%".$keywords."%'";
        }
        if(!empty($tel))
        {
            $this->where.=" and  tel like '%".$tel."%'";
        }
        if(!empty($category_id))
        {
            $this->where.=" and  category_id=".$category_id;
        }
        if(!empty($masseur_sn))
        {
            $this->where.=" and  sex like '%".$masseur_sn."%'";
        }
        if(!empty($sex))
        {
            $this->where.=" and  sex ='".$sex."'";
        }
        if(!empty($master_item))
        {
            $this->where.=" and    find_in_set('".$master_item."',master_item)";
        }
        $list    = $this->ShopMasseur->field('id,shop_id,chain_id,masseur_sn,masseur_name,nick_name,category_id,sex,tel,master_item,remark,cover,balance,createtime,state')->where($this->where)->order('id')->page($pagestart.','.$pagesize)->select();
        foreach ($list as $key=> $val)
        {
            $list[$key]['chain_id']     = M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id']      = M('shop')->where('id='.$val['shop_id'])->getField('shop_name');
            $list[$key]['index']        = Getzimu($val['masseur_name']);
            $list[$key]['category_id']  = M('MasseurCategory')->where('id='.$val['category_id'])->getField('category_name');
            $list[$key]['createtime']   =date("y-m-d H:i:s",$val['createtime']);
            if($val['state']==1)
            {
                $list[$key]['state']='启用';
            }
            else
            {
                $list[$key]['state']='锁定';
            }
        }
        $count   = $this->ShopMasseur->where($this->where)->count();
        $datapages = intval($count/$pagesize);
        if($count%$pagesize>0)
        {
            $datapages = $datapages+1;
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