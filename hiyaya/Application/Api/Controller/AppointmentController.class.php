<?php
// +----------------------------------------------------------------------
// | 预约列表页
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Th2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class AppointmentController extends ApibaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->shopappointment = M("ShopAppointment");//预约表
        $this->shoproom = M("ShopRoom");//房间表
        $this->shopbed = M("ShopBed");//床位表
        $this->shopmasseur = M("ShopMasseur");//技师表
		$this->masseurcategory=M("MasseurCategory"); //技师等级
        $this->shopitem = M("ShopItem");//项目表
        $this->roomcategory = M("RoomCategory");//包间分类表
        $this->shopscheduling=M("ShopScheduling"); //排班表
        $this->ordersproject=M("OrdersProject"); //订单项目表
        $this->where="shop_id=".$this->shop_id;

    }
    //预约列表
    public function index()
    {
        $pagecur=!empty($_POST['pagecur']) ? $_POST['pagecur'] : 1;//当前第几个页
        $pagesize=!empty($_POST['pagesize']) ? $_POST['pagesize'] : 10;//每页显示个数
        $pagecur=($pagecur-1)*$pagesize;
        $keywords = trim(I('post.keywords','','htmlspecialchars'));
        if(!empty($keywords))
        {
            $this->where.=" and name like '%".$keywords."%'";
        }
        $result = $this->shopappointment->field('id,phone,name,male_num,female_num,reservation_time,room_id,masseur_id,project_id,is_lock,order_money')->where($this->where.' and status=1')->order('createtime asc,id desc')->limit($pagecur.",".$pagesize)->select();
        
        if($result){
            foreach ($result as $key => $value) {
                $info[$key]['id'] = $value['id'];
                $info[$key]['phone'] = $value['phone'];
                $info[$key]['name'] = $value['name'];
                $info[$key]['male_num'] = $value['male_num'];
                $info[$key]['female_num'] = $value['female_num'];
                $info[$key]['reservation_time'] = date('m-d H:i',strtotime($value['reservation_time']));
                $info[$key]['is_lock'] = $value['is_lock'];
                $info[$key]['order_money'] = $value['order_money'];
                $info[$key]['from'] = '管家';
                if(!empty($value['room_id'])){
                    $info[$key]['room_name'] = $this->shoproom->where('id='.$value['room_id'])->getfield('room_name');

                }
                if(!empty($value['masseur_id'])){
                    $info[$key]['masseur_sn'] = $this->shopmasseur->where('id='.$value['masseur_id'])->getfield('masseur_sn');
                }
                if(!empty($value['project_id'])){
                    $info[$key]['project_name'] = $this->shopitem->where('id='.$value['project_id'])->getfield('item_name');
                }

            }
        
        }
        if(empty($info)){
            $info = array();
        }
        $count = $this->shopappointment->where($this->where.' and status=1')->count();
        unset($data);
        $data['code'] = 0;
        $data['msg'] = 'success';
        $data['count'] = $count;
        $data['data'] = $info;
        outJson($data);
       
        
    }
    //取消预约
    public function cancel(){
        $id = I('post.id');
        $res = array(
            'id' => $id,
            'status' => 3
        );
        $result = $this->shopappointment->save($res);
        if($result !== false){
            $info = $this->shopappointment->where('id='.$id)->find();
            if($info['is_lock'] == '1'){
                if(!empty($info['masseur_id'])){
                    $time = strtotime($info['reservation_time']);
                    $this->shopscheduling->where("shop_id=".$this->shopid." and masseur_id=".$info['masseur_id']." and start_time < ".$time." and end_time > ".$time)->save(array('is_lock' => 1));
                }
            }
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            outJson($data);
        }else{
            unset($data);
            $data['code'] = 1;
            $data['msg'] = 'error';
            outJson($data);
        }
    }
   //新增预约
    public function add(){
        $chain_id = $this->chain_id;
        $shop_id = $this->shop_id;
        $phone = I('post.phone');
        $name = I('post.name');
        $male_num = I('post.male_num',0,'intval');
        $female_num = I('post.female_num',0,'intval');
        $reservation_time = I('post.reservation_time');
        $room_id = I('post.room_id',0,'intval');
        $masseur_id = I('post.masseur_id',0,'intval');
        $project_id = I('post.project_id',0,'intval');
        $is_lock = I('post.is_lock',0,'intval');
        if(!empty($project_id)){
            $order_money = $this->shopitem->where('id='.$project_id)->getfield('item_price');
        }else{
            $order_money = 0;
        }
        if(empty($reservation_time)){
            unset($data);
            $data['code'] = 1;
            $data['msg'] = '服务时间不能为空';
            outJson($data);
        }
        $res = array(
            'chain_id' => $chain_id,
            'shop_id' => $shop_id,
            'phone' => $phone,
            'name' => $name,
            'male_num' => $male_num,
            'female_num' => $female_num,
            'reservation_time' => $reservation_time,
            'room_id' => $room_id,
            'masseur_id' => $masseur_id,
            'project_id' => $project_id,
            'is_lock' => $is_lock,
            'order_money'=>$order_money,
            'createtime'=>time(),
            'shopuser_id'=>$this->user_id,
        );
        $result = $this->shopappointment->add($res);
        if($result){
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            outJson($data);
        }else{
            unset($data);
            $data['code'] = 1;
            $data['msg'] = 'error';
            outJson($data);
        }
    }
    //包间列表
    public function room_list(){
        $result = $this->shopbed->where($this->where.' and state=1 and is_lock=1')->getfield('room_id',true);
        $result = array_values(array_unique($result));
        $result = implode(',', $result);
        // $categories = $this->roomcategory->where($this->where)->order('sort asc')->select();
        $info = $this->shoproom->field('id,room_name,room_capacity,category_id')->where($this->where.' and id in ('.$result.') and state=1')->select();
        foreach ($info as $k => $v) {
            $info[$k]['category_name'] = $this->roomcategory->where($this->where.' and id='.$v['category_id'])->getfield('category_name');
        }
        
        if($info){
           
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = $info;
            outJson($data);
        }else{
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = array();
            outJson($data);
        }
    }
    //可用技师列表
    public function masseur_list(){
        $reservation_time = I('post.reservation_time');
        if(empty($reservation_time)){
            unset($data);
            $data['code'] = 1;
            $data['msg'] = '预约时间不能为空！';
            outJson($data);
        }
        $time = strtotime($reservation_time);
        $result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".$time." and a.end_time > ".$time." and a.shop_id=".$this->shop_id." and a.is_lock=1 ")->field("a.masseur_id as masseur_id,a.status as status,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.category_id as category_id")->order("a.id asc")->select();
      
        $count=$this->shopscheduling->where("start_time < ".$time." and end_time > ".$time." and shop_id=".$this->shop_id." and is_lock=1")->count();
      
        foreach($result as &$val)
        {
            unset($order_project_info);
            $order_project_info=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and shop_id=".$this->shop_id." and is_del=0 and types=1")->order("down_time desc")->find();
            if($order_project_info)
            {
                $val['down_time']=$order_project_info['down_time'];
                $val['down_time'] = date('Y-m-d H:i:s',$val['down_time']);
            }else
            {
                $val['down_time']=0;
            }
			$val['category_name']=$this->masseurcategory->where("id=".$val['category_id'])->getField("category_name");
        }
        $arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $result);
        if($result){
            unset($data);
            $data['code']='0';
            $data['msg']='success';
            $data['data']=$result;
            outJson($data);
        }else{
            unset($data);
            $data['code']='0';
            $data['msg']='success';
            $data['data']=array();
            outJson($data);
        }

    }

}
?>