<?php
// +----------------------------------------------------------------------
// | 请假
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: ALina
// +----------------------------------------------------------------------
namespace Masseur\Controller;
use Common\Controller\MasseurbaseController;
class LeaveController extends MasseurbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopLeave = M("ShopLeave");//请假表
        $this->Setting = M("Setting");//

        $this->where="shop_id=".$this->shop_id;
        $this->arr=array("1"=>"待审核","2"=>"已通过","3"=>"未通过");
    }
    public function leavetype()
    {
        $leavetype=$this->Setting->where("parentid=11")->order("sort asc")->select();
        $data['code'] = 0;
        $data['msg'] = 'success';
        $data['data'] = $leavetype;
        outJson($data);
    }
    //请假列表
    public function index()
    {
        $info=$this->ShopLeave->where("masseur_id=".$this->masseur_id)->order("createtime desc")->select();
        $count   = $this->ShopLeave->where("masseur_id=".$this->masseur_id)->count();
        foreach($info as $k=>$val)
        {
            $info[$k]['status']=$this->arr[$val['status']];
            $info[$k]['start_time']=date('Y-m-d H:i',$val['start_time']);
            $info[$k]['end_time']=date('Y-m-d H:i',$val['end_time']);
            $info[$k]['approval_time']=date('Y-m-d H:i',$val['approval_time']);
            $info[$k]['type']=$this->Setting->where("id=".$val['type'])->getField('title');
        }
        if(count($info)>0)
        {
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['count']=$count;
            $data['data'] = $info;
        }
        else
        {
            unset($data);
            $data['code'] = 1;
            $data['msg'] = 'error';
            $data['count']='';
            $data['data'] = '';
        }
        outJson($data);
    }
    public function edit()
    {
        if($_POST['act']=='update')
        {
            $id=I('post.id');
            $data_arr=array(
                'chain_id'=>$this->chain_id,
                'shop_id'=>$this->shop_id,
                'type'=>I('post.type',0,'intval'),
                'masseur_id'=> $this->masseur_id,
                'start_time'=>strtotime(I('post.start_time')),
                'end_time'=>(strtotime(I('post.end_time'))+24*3600-1),
                'remark'=>I('post.remark'),

            );
			
			

            $this->where.=" AND id!=".$id;


                $res = $this->ShopLeave->where('id=' . $id)->save($data_arr);
                if ($res !== false)
                {
                    $data['code'] = 0;
                    $data['msg'] = 'success';
                    $data['data'] = '';
                }
                else
                {
                    $data['code'] = 1;
                    $data['msg'] = 'error';
                    $data['data'] = '';
                }
            outJson($data);
        }
        else
        {
            $id=I('post.id');
            $info= $this->ShopLeave->where('id='. $id)->find();
            $info['status']=$this->arr[$info['status']];
            $info['start_time']=date('Y-m-d H:i',$info['start_time']);
            $info['end_time']=date('Y-m-d H:i',$info['end_time']);
            $info['approval_time']=date('Y-m-d H:i',$info['approval_time']);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = $info;
            outJson($data);
        }
        $this->display('edit');
    }
    public function applyleave()
    {
        $data=array(
            'chain_id'=>$this->chain_id,
            'shop_id'=>$this->shop_id,
            'type'=>I('post.type',0,'intval'),
            'masseur_id'=> $this->masseur_id,
            'start_time'=>strtotime(I('post.start_time')),
            'end_time'=>(strtotime(I('post.end_time'))+24*3600-1),
            'createtime'=>time(),
            'remark'=>I('post.remark'),
            'status'=>1
        );
        if(I('post.type',0,'intval')==false)
        {
            unset($data);
            $data['code'] = 1;
            $data['msg'] = '请假类型不能为空！';
            $data['data'] = '';
        }
        else if($this->ShopLeave->add($data))
        {
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = '';
        }
        else
        {
            unset($data);
            $data['code'] = 1;
            $data['msg'] = 'error';
            $data['data'] = '';
        }
        outJson($data);
    }
    
}
?>