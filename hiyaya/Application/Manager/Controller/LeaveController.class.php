<?php
// +----------------------------------------------------------------------
// | 门店列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Alina <309824428@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class LeaveController extends ManagerbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopLeave = M("ShopLeave");
		$this->shopscheduling = M("ShopScheduling");
        $this->chain_id=session('chain_id');
        $this->shop_id=session('shop_id');
        $this->assign('chain_id',session('chain_id'));
        $this->assign('shop_id',session('shop_id'));
        $category      =M('ShopMasseur')->where('chain_id='.$this->chain_id.' and shop_id='.$this->shop_id.'')->select();
        $this->assign('category',$category);
        $typelist       =M('Setting')->where('parentid=11')->select();
        $this->assign('typelist',$typelist);
    }
    public function index()
    {
        $this->display('index');
    }
    public function json()
    {
        $limit   =I('get.limit');
        $pagecur=I('get.page');
        $where=" chain_id=".session('chain_id')." and shop_id=".session('shop_id');
        if(I('get.masseur_id'))
        {
            $masseur_id= I('get.masseur_id');
            $where.=" and masseur_id = ".$masseur_id;
        }
        if(I('get.startime'))
        {
            $startime= strtotime(I('get.startime'));
            $where.=" and start_time >= '".$startime."'";
        }
        if(I('get.endtime'))
        {
            $endtime= strtotime(I('get.endtime'));
            $where.=" and 	end_time <='".$endtime."'";
        }
        if(I('get.status'))
        {
            $status= I('get.status');
            $where.=" and status = ".$status;
        }
        if(I('get.type'))
        {
            $type= I('get.type');
            $where.=" and type = ".$type;
        }
        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $list    = $this->ShopLeave->where($where)->order('id')->page($pagecur.','.$pagenum)->select();
        foreach ($list as $key=> $val)
        {
            $list[$key]['chain_id']      =M('chain')->where('id='.$val['chain_id'])->getField('name');
            $list[$key]['shop_id']       =M('shop')->where('id='.$val['shop_id'])->getField('shop_name');
            $list[$key]['masseur_name']       =M('ShopMasseur')->where('id='.$val['masseur_id'])->getField('masseur_name');
            $list[$key]['title']       =M('Setting')->where('id='.$val['type'])->getField('title');
            $list[$key]['user_name']       =M('ShopUser')->where('id='.$val['shopuser_id'])->getField('username');
            $list[$key]['start_time']=date("y-m-d H:i:s",$val['start_time']);
            $list[$key]['end_time']=date("y-m-d H:i:s",$val['end_time']);
            $list[$key]['createtime']=date("y-m-d H:i:s",$val['createtime']);
            $list[$key]['approval_time']=date("y-m-d H:i:s",$val['approval_time']);


        }
        $count   = $this->ShopLeave->where($where)->count();
        $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$list);
        echo json_encode($data);
    }

	public function del()
    {
        $id = I('post.id',0,'intval');
        $res=$this->ShopLeave->where("id=".$id)->find();
        if($res['state']==1){
            $this->error("启用中的楼层禁止删除！");
        }
        if ($this->ShopLeave->delete($id)!==false)
        {
            echo 'success';
        }
        else
        {

            $this->error("删除失败！");
        }
    }


    public function edit()
    {
        if($_POST['act']=='update') {
            $id = I('post.id');
			$res_info=$this->ShopLeave->where("id=".$id)->find();
            $data = array(
                'status' => I('post.status'),
                'shopuser_id' =>session('user_id'),
                'approval_time' =>time()
            );
            $res = $this->ShopLeave->where('id=' . $id)->save($data);
            if ($res !== false) {
				if(I('post.status')==2)
				{
					unset($data_array);
					if($res_info['type']==12)
					{
						$data_array=array(
							'status' => 4,
						);
					}else if($res_info['type']==13)
					{
						$data_array=array(
							'status' => 5,
						);
					}else if($res_info['type']==14)
					{
						$data_array=array(
							'status' => 6,
						);
					}else if($res_info['type']==15)
					{
						$data_array=array(
							'status' => 7,
						);
					}else if($res_info['type']==16)
					{
						$data_array=array(
							'status' => 8,
						);
					}
					$this->shopscheduling->where("chain_id=".$res_info['chain_id']." and shop_id=".$res_info['shop_id']." and masseur_id=".$res_info['masseur_id']." and start_time >= ".$res_info['start_time']." and start_time <= ".$res_info['end_time'])->save($data_array);
				}
                $this->success("保存成功");
            } else {
                $this->error("保存失败！");
            }
            return;


        }
        if($_GET['id'])
        {

            $info= $this->ShopLeave->where('id='.$_GET['id'])->find();
            $this->assign('info',$info);
        }

        $this->display('edit');
    }
}
?>