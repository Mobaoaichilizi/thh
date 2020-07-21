<?php
// +----------------------------------------------------------------------
// | 预约
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: HT2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class AppointmentController extends ManagerbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->shopmember=M("ShopMember"); //会员表 
        $this->shopmasseur=M("ShopMasseur"); //技师表
        $this->shoproom=M("ShopRoom"); //房间表
        $this->shopitem=M("ShopItem"); //项目列表
        $this->shopappointment=D("ShopAppointment"); //预约表
        $this->shoplockcard=D("shopLockcard"); //锁牌表
        $this->shopbed=D("shopBed"); //床位表
        $this->orders=M("Orders"); //订单表
        $this->shopscheduling=M("ShopScheduling"); //排班表
        $this->ordersproject=M("OrdersProject"); //订单项目表
        $this->shopmember=M("shopMember"); //会员表
        $this->shopguest=M("shopGuest"); //散客表
        $this->shopid=session('shop_id');
        $this->chainid=session('chain_id');
        $this->userid=session('user_id');
    }
    public function index()
    {
        
        $this->display('index');
    }
    public function info(){
        $limit   =I('get.limit');
        $pagecur=I('get.page');
        $where=" chain_id=".$this->chainid." and shop_id=".$this->shopid;
        if(I('get.keywords'))
        {
            $keywords= I('get.keywords');
            $where.="  and name like '%".$keywords."%'";
        }
        if(I('get.member_tel'))
        {
            $member_tel= I('get.member_tel');
            $where.=" and  phone like '%".$member_tel."%'";
        }
         if(I('get.status'))
        {
            $status= I('get.status');
            $where.=" and  status like '%".$status."%'";
        }

        $pagenum =!empty($limit) ? $limit :3;
        $pagecur = !empty($pagecur)? $pagecur:1;
        $list    = $this->shopappointment->where($where)->order('id asc')->page($pagecur.','.$pagenum)->select();
        if(!empty($list)){
            foreach ($list as $key=>$val)
            {
                $info[$key]['id'] = $val['id'];
                $info[$key]['reservation_time'] = $val['reservation_time'];
                $info[$key]['member'] = $val['name'].$val['phone'];
               
                if(empty($val['male_num']) && empty($val['female_num'])){
                    $info[$key]['member_num'] = '-';
                }else{
                    $info[$key]['member_num'] = $val['male_num'].'男'.$val['female_num'].'女';
                }
                
                if(empty($val['room_id'])){
                    $info[$key]['room'] = '-';
                }else{
                    $info[$key]['room'] = $this->shoproom->where('id='.$val['room_id'])->getfield('room_name');
                }
                $masseur = $this->shopmasseur->field('masseur_sn,masseur_name')->where('id='.$val['masseur_id'])->find();
                $reservation_info = $this->shopitem->where('id='.$val['project_id'])->find();
                
                if(empty($val['project_id'])){
                    $info[$key]['reservation_info'] = $masseur['masseur_sn'].'-';
                }else{
                    $info[$key]['reservation_info'] = $masseur['masseur_sn'].' '.$reservation_info['item_name'].' '.$reservation_info['item_duration'].'分钟';
                }
                $info[$key]['order_money'] = $val['order_money'];
                if($val['is_lock'] == 1){
                    $info[$key]['is_lock'] = '锁定';
                }else{
                    $info[$key]['is_lock'] = '未锁定';
                }
                if($val['status'] == 1){
                    $info[$key]['status'] = '正常(已接受)';
                }else if($val['status'] == 2){
                    $info[$key]['status'] = '已开单';
                }else if($val['status'] == 3){
                    $info[$key]['status'] = '已取消';
                }
                $info[$key]['createtime']=date("Y-m-d H:i:s",$val['createtime']);
                
            }
        }
        
        $count   = $this->shopappointment->where($where)->count();
        $page    = new \Think\Page($count,$pagenum);// 实例化分页类 传入总记录数和每页显示的记录数
        $data=array("code"=>0,"msg"=>"","count"=>$count,"data"=>$info);
        echo json_encode($data);
    }
    //添加预约
    public function add(){
        
        if(IS_POST){
            $project_id = I('post.project_id',0,'intval');
            $is_lock = I('post.is_lock',0,'intval');
            $time = strtotime($_POST['reservation_time']);
            if(!empty($project_id)){
                $order_money = $this->shopitem->where('id='.$project_id)->getfield('item_price');
            }
            if($this->shopappointment->create()!==false){
                $result = $this->shopappointment->add();
               if($result)
               {
                unset($res);
                $res = array('order_money' => $order_money,'createtime' => time(),'shopuser_id'=>$this->userid);
                $this->shopappointment->where('id='.$result)->save($res);
                if($is_lock == '1'){
                    $this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$_POST['masseur_id']." and start_time < ".$time." and end_time > ".$time)->save(array('is_lock' => 0));
                }
                $this->success("预约成功！", U("Appointment/index"));
               }
            }else{
                $error = $this->shopappointment->getError();
                $this->error($error);
            }

        }else{
            $member_id = I('get.member_id',0,'intval');
            if(!empty($member_id)){
                $member = $this->shopmember->field('member_name,member_tel')->where('id='.$member_id)->find();
            }
            $where = 'shop_id='.$this->shopid;
            $room_list = $this->shoproom->where($where.' and state=1')->select();
            $masseur_list = $this->shopmasseur->where($where.' and state=1')->select();
            $project_list = $this->shopitem->where($where.' and state=1')->select();
            $this->assign('member',$member);
            $this->assign('room_list',$room_list);
            $this->assign('masseur_list',$masseur_list);
            $this->assign('project_list',$project_list);
            $this->display();
        }
    }
    //选择会员
    public function select_member(){
        $this->display();
    }
    //接收选择的会员
    public function post_select_member(){
        $member_id=I('post.member_id');
        if(empty($member_id))
        {
            $this->error("请选择会员！");
        }
        $result = $this->shopmember->where('id='.$member_id)->find();
        unset($data);
        $data['state'] = 'success';
        $data['info'] = '添加成功！';
        $data['data'] = $result;
        outJson($data);
    }
    //编辑预约信息
    public function edit(){
        
        if(IS_POST){
            $id = I('post.id',0,'intval');
            $is_lock=!empty($_POST['is_lock']) ? 1:'0';
            $time = strtotime($_POST['reservation_time']);
            if($this->shopappointment->create()!==false){
                $res=$this->shopappointment->where('id='.$id)->save($data);
                if(!empty($_POST['project_id'])){
                    $order_money = $this->shopitem->where('id='.$_POST['project_id'])->getfield('item_price');
                }
                if($res !== false)
                {
                    unset($rest);
                    $rest = array('order_money'=>$order_money,'is_lock'=>$is_lock);
                    $this->shopappointment->where('id='.$id)->save($rest);
                    if($is_lock == '1'){
                        $this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$_POST['masseur_id']." and start_time < ".$time." and end_time > ".$time)->save(array('is_lock' => 0));
                    }else{
                        $this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$_POST['masseur_id']." and start_time < ".$time." and end_time > ".$time)->save(array('is_lock' => 1));
                    }
                    $this->success("编辑成功！", U("Appointment/index"));
                }
                else
                {
                    $this->error('编辑失败!');
                }
            }else{
                $this->error($this->shopappointment->getError());exit;
            }
        }else{
            $id = I('get.id',0,'intval');
            $info= $this->shopappointment->where('id='.$id)->find();
            $where = 'shop_id='.$this->shopid;
            $room_list = $this->shoproom->where($where.' and state=1')->select();
            $masseur_list = $this->shopmasseur->where($where.' and state=1')->select();
            $project_list = $this->shopitem->where($where.' and state=1')->select();
            $this->assign('room_list',$room_list);
            $this->assign('masseur_list',$masseur_list);
            $this->assign('project_list',$project_list);
            $this->assign('info',$info);
            $this->display();
        }
    }
    //取消预定
    public function close(){
        $id = I('post.id',0,'intval');
        $res = array('status' => '3');
        $result = $this->shopappointment->where('id='.$id)->save($res);
        
        if($result !== false){
            $info = $this->shopappointment->where('id='.$id)->find();
            if($info['is_lock'] == '1'){
                if(!empty($info['masseur_id'])){
                    $time = strtotime($info['reservation_time']);
                    $this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$info['masseur_id']." and start_time < ".$time." and end_time > ".$time)->save(array('is_lock' => 1));
                }
            }
            echo 'success';
        }else{
            echo 'error';
        }
    }
    //开单
    public function order(){
        if(IS_POST){
            $appointment_id = I('post.appointment_id',0,'intval');
            $appointment_info = $this->shopappointment->where('id='.$appointment_id)->find();
            if($appointment_info['status'] == '3'){
                $this->error('此预约已经取消！');
            }else if($appointment_info['status'] == '2'){
                $this->error('已开单，请勿重复开单！');
            }
            $project_id=I('post.project_id');
            $masseur_id=I('post.masseur_id');
            $chain_id=I('post.chain_id');
            $shop_id=I('post.shop_id');
            $lockcard_id=I('post.lockcard_id');
            $room_id=I('post.room_id');
            $bed_id=I('post.bed_id');    
            $types=I('post.types','1','intval');    
            if(empty($chain_id) || empty($shop_id) || empty($room_id) || empty($bed_id))
            {
                $this->error("信息不能为空！");
            }
            if(empty($project_id)){
                $this->error('请选择项目！');
            }
            if(empty($masseur_id)){
                $this->error('请选择技师！');
            }
           
            $masseur_info = $this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$masseur_id." and start_time < ".time()." and end_time > ".time())->find();
           
            $where = 'shop_id='.$this->shopid.' and member_name="'.$appointment_info['name'].'" and member_tel='.$appointment_info['phone'];
            $member_count = $this->shopmember->where($where)->count();
            if(empty($member_count)){
              
                // unset($res);
                // $res = array(
                //     'chain_id'=>$this->chainid,
                //     'shop_id'=>$this->shopid,
                //     'member_name'=>$appointment_info['name'],
                //     'member_tel'=>$appointment_info['phone'],
                //     'identity'=>2,
                //     'createtime'=>time(),
                // );
                // $result = $this->shopmember->add($res);
                // if($result !== false){
                //     $member_id = $result;
                // }
               
            }else{
                $member_id = $this->shopmember->where($where)->getfield('id');
            }
            $member_id=$member_id?$member_id:'0';
            unset($data);
            $data=array(
                'chain_id' => $chain_id,
                'shop_id' => $shop_id,
                'order_sn' => 'YS-'.time().substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8),
                'shopuser_id' => $this->userid,
                'member_id'=>$member_id?$member_id:0,
                'room_id' => $room_id,
                'bed_id' => $bed_id,
                'lockcard_id' => $lockcard_id,
                'status' => 1,
                'createtime' => time(),
            );
            $this->shoplockcard->where("id=".$lockcard_id." and shop_id=".$this->shopid)->save(array("is_lock" => 0));
            $this->shopbed->where("id=".$bed_id." and shop_id=".$this->shopid)->save(array('is_lock' => 0));
            $this->shopappointment->where('id='.$appointment_id." and shop_id=".$this->shopid)->save(array('status' => 2));
            $result=$this->orders->add($data);
            if($result){
                $project_info=$this->shopitem->where("id=".$project_id." and shop_id=".$this->shopid)->find();

                unset($project_data);
                $project_data=array(
                    'chain_id' => $chain_id,
                    'shop_id' => $shop_id,
                    'order_id' => $result,
                    'shopuser_id' => $this->userid,
                    'masseur_id' => $masseur_id,
                    'scheduling_id' => $masseur_info['id'],
                    'project_id' => $project_id,
                    'title' => $project_info['item_name'],
                    'duration' => $project_info['item_duration'],
                    'unit_price' => $project_info['item_price'],
                    'number' => '1',
                    'total_price' => $project_info['item_price'],
                    'loop_reward' => $project_info['turn_reward'],
                    'point_reward' => $project_info['point_reward'],
                    'add_reward' => $project_info['add_reward'],
                    'project_reward' => $project_info['rec_reward'],
                    'is_discount' => $project_info['is_discount'],
                    'up_time' => 0,
                    'down_time' => 0,
                    'status' => 1,
                    'types' => $types,
                    'createtime' => time(),
                );
                $this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$masseur_id." and start_time < ".time()." and end_time > ".time())->save(array('is_lock' => 0));
                $this->ordersproject->add($project_data);
                $this->success('下单成功！');
            }else{
                $this->error('下单失败！');
            }
            
        }else{
            $id = I('get.id',0,'intval');
            $info = $this->shopappointment->where('id='.$id)->find();
            $where = 'shop_id='.$this->shopid;
            $lockcard_list = $this->shoplockcard->where($where.' and status=1 and is_lock=1')->select();
            $room_list = $this->shoproom->where($where.' and state=1')->select();
            $bed_list = $this->shopbed->where($where.' and state=1 and is_lock=1')->select();
            //轮班技师
            $result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shopid." and a.is_lock=1 ")->field("a.masseur_id as masseur_id,a.status as status,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name")->order("a.id asc")->select();
      
            $count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shopid." and is_lock=1")->count();
            $exist = 0;
            foreach($result as &$val)
            {
                unset($order_project_info);
                $order_project_info=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and shop_id=".$this->shopid." and is_del=0 and types=1")->order("down_time desc")->find();
                if($order_project_info)
                {
                    $val['down_time']=$order_project_info['down_time'];
                }else
                {
                    $val['down_time']=0;
                }
                if($val['masseur_id'] == $info['masseur_id']){
                    $exist = 1;
                }
            }
            $arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $result);
            
            array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$result);
            if(!empty($info['masseur_id'])){
                if($exist == 1){
                    if($result[0]['masseur_id'] == $info['masseur_id']){
                        $types = 1;
                    }else{
                        $types = 2;
                    }
                }
            }
            $project_list=$this->shopitem->where("shop_id=".$this->shopid.' and state=1')->select();
            $this->assign('lockcard_list',$lockcard_list);
            $this->assign('room_list',$room_list);
            $this->assign('bed_list',$bed_list);
            $this->assign('info',$info);
            $this->assign('masseur_list',$result);
            $this->assign('project_list',$project_list);
            $this->assign('id',$id);
            $this->assign('types',$types);
            $this->display();
        }
    }
    //根据预约时间获取值班技师
    public function select_masseur(){
        $value = I('post.value');
        $time = strtotime($value);
        $result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".$time." and a.end_time > ".$time." and a.shop_id=".$this->shopid." and a.is_lock=1 ")->field("a.masseur_id as masseur_id,a.status as status,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name")->order("a.id asc")->select();
      
        $count=$this->shopscheduling->where("start_time < ".$time." and end_time > ".$time." and shop_id=".$this->shopid." and is_lock=1")->count();
      
        foreach($result as &$val)
        {
            unset($order_project_info);
            $order_project_info=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and shop_id=".$this->shopid." and is_del=0 and types=1")->order("down_time desc")->find();
            if($order_project_info)
            {
                $val['down_time']=$order_project_info['down_time'];
            }else
            {
                $val['down_time']=0;
            }
        }
        $arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $result);
        if($result){
            unset($data);
            $data['state']='success';
            $data['info']='值班技师信息获取成功！';
            $data['data']=$result;
            outJson($data);
        }
    }
}