<?php
// +----------------------------------------------------------------------
// | 技师订单上下钟
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Th2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Masseur\Controller;
use Common\Controller\MasseurbaseController;
class MasseurController extends MasseurbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        //$this->ordersproject = M("OrdersProject");//订单项目表
        $this->orders = M("Orders");//订单表
        $this->shopmasseur = M("ShopMasseur");//技师表
        $this->shopitem = M("ShopItem");//项目表
        $this->shopproduct = M("ShopProduct");//产品表
        $this->shopscheduling=M("ShopScheduling"); //排班表
        $this->shoplockcard = D("shopLockcard"); //锁牌
        $this->ordersproject=M("OrdersProject"); //订单项目表
        $this->ordersproduct=M("OrdersProduct"); //产品项目表
        $this->ordersreward=M("OrdersReward"); //订单提成表
        $this->shoproom=M("ShopRoom"); //房间表
        $this->shopbed=M("ShopBed"); //床位表
        $this->roomcategory=M("RoomCategory"); //房间分类
        $this->shopmember=M("ShopMember"); //会员表
        $this->shoplockcard=M("ShopLockcard"); //锁牌表
        $this->itemcategory=M("ItemCategory"); //项目分类
        $this->productcategory=M("ProductCategory"); //产品分类
        $this->masseurcategory=M("MasseurCategory"); //技师等级
        $this->shoprole=M("ShopRole"); //商家权限
        $this->shopuser=M("ShopUser"); //商家管理员
		$this->feedback=M("Feedback"); //意见反馈
        $this->where="shop_id=".$this->shop_id;

    }
    //首页订单
    public function index()
    {
        // $this->where.=" and pay_time =0 ";
        $this->where.=" and ((up_time=0) or (down_time=0) or (pay_time=0))";
        $order = $this->ordersproject->where($this->where.' and masseur_id='.$this->masseur_id.' and status=1  and is_del=0')->order('createtime desc,id desc')->find();

        $count = $this->ordersproject->where($this->where.' and masseur_id='.$this->masseur_id.' and status=1 and is_del=0')->order('createtime desc,id desc')->count();
        if($order){
            $shopuser_id = $this->orders->where('id='.$order['order_id'])->getfield('shopuser_id');
            $role_id = $this->shopuser->where('id='.$shopuser_id)->getfield('role_id');
            if($role_id == '0'){
                $info['from'] = '管理员';
            }else{
                $info['from'] = $this->shoprole->where('id='.$role_id)->getfield('name');
            }
            $info['id'] = $order['id'];
            $info['order_id'] = $order['order_id'];
            $info['item_name'] = $order['title'];
            $info['item_price'] = $this->shopitem->where('id='.$order['project_id'])->getfield('item_price');
            $info['item_duration'] = $this->shopitem->where('id='.$order['project_id'])->getfield('item_duration');
            $room_id = $this->orders->where('id='.$order['order_id'])->getfield('room_id');
            $info['room_name'] = $this->shoproom->where('id='.$room_id)->getfield('room_name');
            $info['createtime'] = $this->orders->where('id='.$order['order_id'])->getfield('createtime');
            $info['createtime'] = date('Y-m-d H:i',$info['createtime']);
            $info['accept_time'] = $order['accept_time'];
            if($info['accept_time']>0){
                $info['difference_time'] = time()-$info['accept_time'];
            }
            if(empty($order['up_time'])){
                $info['status'] = 1; //待上钟
            }else if(!empty($order['up_time']) && empty($order['down_time'])){
                $info['down_time'] = $order['up_time']+($order['duration']*60);
                $info['countdown'] = $info['down_time']-time();
                $info['status'] = 2;//待下钟
            }else{
                $info['status'] = 3;//已下钟
            }
			$info['is_confirm'] = $order['is_confirm'];
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['count'] = $count;
            $data['data'] = $info;
            //$data['data'] = $this->ordersproject->getLastSql();
            outJson($data);
            
        }else{
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = '';
            outJson($data);
        }
       
        
    }
    //订单列表
    public function lists()
    {
        $is_history = I('post.is_history','0',"intval");
        if(!empty($is_history)){//历史订单
            $this->where.=" and (is_del=1 or (up_time!=0 and down_time!=0 and pay_time!=0))";
        }else{//当前订单
            $this->where.=" and ((up_time=0) or (down_time=0) or (pay_time=0)) and is_del=0";
        }
        $pagecur=!empty($_POST['pagecur']) ? $_POST['pagecur'] : 1;//当前第几个页
        $pagesize=!empty($_POST['pagesize']) ? $_POST['pagesize'] : 10;//每页显示个数
        $pagecur=($pagecur-1)*$pagesize;
        $order = $this->ordersproject->where($this->where.' and masseur_id='.$this->masseur_id.' and status=1')->order('createtime desc,id desc')->limit($pagecur.",".$pagesize)->group('order_id')->select();
        $order_counts = $this->ordersproject->where($this->where.' and masseur_id='.$this->masseur_id.' and status=1')->order('createtime desc,id desc')->group('order_id')->select();
        $count = count($order_counts);

        if($order){
            foreach ($order as $key => $value) {
                $order_info = $this->orders->where('id='.$value['order_id'])->find();
                $role_id = $this->shopuser->where('id='.$order_info['shopuser_id'])->getfield('role_id');
                if($role_id == '0'){
                    $info[$key]['from'] = '管理员';
                }else{
                    $info[$key]['from'] = $this->shoprole->where('id='.$role_id)->getfield('name');
                }

                $info[$key]['member_info']='';
                if($order_info['member_id']!=0)
                {
                    $info[$key]['member_info']=$this->shopmember->where("shop_id=".$this->shop_id." and id=".$order_info['member_id'])->find();
                }


                $info[$key]['id'] = $value['id'];
				$info[$key]['masseur_id'] = $value['masseur_id'];
                $info[$key]['order_id'] = $value['order_id'];
                $info[$key]['item_name'] = $value['title'];
				$info[$key]['status'] =$order_info['status'];
                 //订单是否全部上钟
                $info[$key]['up_count']=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$value['order_id']." and up_time=0  and masseur_id=".$this->masseur_id)->count();
                //全部下钟
                $info[$key]['down_count']=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$value['order_id']." and down_time=0 and masseur_id=".$this->masseur_id)->count();
                $info[$key]['is_one_uptime']=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$value['order_id']." and up_time > 0 and masseur_id=".$this->masseur_id)->count();
                $info[$key]['is_evaluation']=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$value['order_id']." and is_evaluation=1 and masseur_id=".$this->masseur_id)->count();
				$info[$key]['item_price'] = $this->shopitem->where('id='.$value['project_id'])->getfield('item_price');
                $info[$key]['cover'] = $this->shopitem->where('id='.$value['project_id'])->getfield('cover');
                $info[$key]['cover'] = $this->host_url.$info[$key]['cover'];
                $info[$key]['item_duration'] = $this->shopitem->where('id='.$value['project_id'])->getfield('item_duration');
                $room_id = $this->orders->where('id='.$value['order_id'])->getfield('room_id');
                $info[$key]['room_name'] = $this->shoproom->where('id='.$room_id)->getfield('room_name');
                $info[$key]['createtime'] = $this->orders->where('id='.$value['order_id'])->getfield('createtime');
                $info[$key]['createtime'] = date('Y-m-d H:i',$info[$key]['createtime']);
                $info[$key]['is_confirm'] = $value['is_confirm'];
                $info[$key]['accept_time'] = $value['accept_time'];
                if($value['accept_time']>0){
                    $info[$key]['difference_time'] = time()-$value['accept_time'];
                }
                if(empty($value['up_time'])){
                    $info[$key]['state'] = 1; //待上钟
                }else if(!empty($value['up_time']) && empty($value['down_time'])){
                    $info[$key]['down_time'] = $value['up_time']+($value['duration']*60);
                    $info[$key]['countdown'] = $info[$key]['down_time']-time();
                    $info[$key]['state'] = 2;//待下钟
                }else if(!empty($value['up_time']) && !empty($value['down_time']) && empty($value['pay_time'])){
                    $info[$key]['state'] = 3;//已下钟
                }else{
                    $info[$key]['state'] = 4;//已完成
                }
                if($value['is_del'] == 1){
                    $info[$key]['state'] = 5;//已取消
                }
            }
            
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['count'] = $count;
            $data['data'] = $info;
            outJson($data);
            
        }else{
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['count'] = $count;
            $data['data'] = array();
            outJson($data);
        }
       
        
    }
    //上钟
    public function up_clock(){
        $id = I('post.id');
        $result = $this->ordersproject->where($this->where.' and id='.$id)->save(array('up_time'=>time()));
        $info = $this->ordersproject->where($this->where.' and id='.$id)->find();
        $down_time = time()+($info['duration']*60);
        $shopuser_id = $this->orders->where('id='.$info['order_id'])->getField('shopuser_id');
        $masseur_name = $this->shopmasseur->where('id='.$this->masseur_id)->getField('nick_name');
        if($result){
            $shopuser_info = $this->shopuser->where('id='.$shopuser_id)->find();
            if($shopuser_info['device']!=''){
                unset($title);
                unset($content);
                $title=$masseur_name."健康师已经上钟！";
                $content = $masseur_name."健康师已经上钟！";
                $device[] = $shopuser_info['device'];
                $extra = array("push_type" => 2, "order_id" => $info['order_id']);
                $audience='{"alias":'.json_encode($device).'}';
                $extras=json_encode($extra);
                $os=$shopuser_info['os'];
                $res=jpush_shop_send($title,$content,$audience,$os,$extras);
            }
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['info'] = $down_time;
            outJson($data);
        }else{
            unset($data);
            $data['code'] = 1;
            $data['msg'] = 'error';
            outJson($data);
        }
    }
    //下钟
    public function down_clock()
    {
        $id = I('post.id');
        $info = $this->ordersproject->where($this->where.' and id='.$id)->find();
        $down_time = $info['up_time'] + $info['duration']*60;
        if($down_time-time()>300){
            unset($data);
            $data['code'] = 1;
            $data['msg'] = '不能提前下钟！';
            outJson($data);
        }
        $result = $this->ordersproject->where($this->where.' and id='.$id)->save(array('down_time'=>time()));
        if($result){
            //如果此技师再无订单，技师状态取消锁定
            $count = $this->ordersproject->where($this->where.' and masseur_id='.$this->masseur_id.' and ((up_time=0) or (down_time=0)) and is_del=0 and status=1')->count();
            if($count == 0){
                $this->shopscheduling->where('id='.$info['scheduling_id'].' and masseur_id='.$this->masseur_id)->save(array('is_lock'=>1));
            }
            //如果此订单已完成，取消锁牌的锁定和包间的状态
            $complete = $this->ordersproject->where($this->where.' and order_id='.$info['order_id'].' and (up_time=0 or down_time=0 or pay_time=0) and status=1 and is_del=0')->count();
            if($complete == 0){
                $orders = $this->orders->where('id='.$info['order_id'])->find();
                $this->shoproom->where('id='.$orders['room_id'])->save(array('state'=>3));
                $this->shoplockcard->where('id='.$orders['lockcard_id'])->save(array('is_lock'=>1));
				
            }
            $shopuser_id = $this->orders->where('id='.$info['order_id'])->getField('shopuser_id');
            $masseur_name = $this->shopmasseur->where('id='.$this->masseur_id)->getField('nick_name');
            $shopuser_info = $this->shopuser->where('id='.$shopuser_id)->find();
            if($shopuser_info['device']!=''){
                unset($title);
                unset($content);
                $title=$masseur_name."健康师已经下钟！";
                $content = $masseur_name."健康师已经下钟！";
                $device[] = $shopuser_info['device'];
                $extra = array("push_type" => 3, "order_id" => $info['order_id']);
                $audience='{"alias":'.json_encode($device).'}';
                $extras=json_encode($extra);
                $os=$shopuser_info['os'];
                $res=jpush_shop_send($title,$content,$audience,$os,$extras);
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

    //订单详情
    public function show_order()
    {
        $order_id=I('post.order_id');
        $order_info=$this->orders->where("shop_id=".$this->shop_id." and id=".$order_id)->find();
        if(!$order_info)
        {
            unset($data);
            $data['code']=1;
            $data['msg']='订单不存在！';
            outJson($data);
        }
        
        $order_info['pay_time_data']=date("Y-m-d H:i:s",$order_info['pay_time']);
        $order_info['createtime_data']=date("Y-m-d H:i:s",$order_info['createtime']);
        $order_info['room_name'] = $this->shoproom->where('id='.$order_info['room_id'])->getfield('room_name');
        $role_id = $this->shopuser->where('id='.$order_info['shopuser_id'])->getfield('role_id');
        if($role_id == '0'){
            $order_info['from'] = '管理员';
        }else{
            $order_info['from'] = $this->shoprole->where('id='.$role_id)->getfield('name');
        }

        //$this->assign('order_info',$order_info);
        
        //会员信息
        $member_info='';
        if($order_info['member_id']!=0)
        {
            $member_info=$this->shopmember->where("shop_id=".$this->shop_id." and id=".$order_info['member_id'])->find();
            //$this->assign('member_info',$member_info);
        }
        //获取订单项目
        $finish_time=0;
        /*
        $result_project=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id'])->order("down_time asc")->select();
        foreach($result_project as &$val)
        {
            $val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('masseur_sn');
            $val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('nick_name');
            $finish_time+=$val['duration'];
        }
        */
        $project_total_money=0;
        $result_project=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id'].' and masseur_id='.$this->masseur_id)->order("down_time asc")->group("masseur_id")->select();
        $order_info['is_confirm'] = $this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id'].' and masseur_id='.$this->masseur_id." and is_confirm=1")->count();
        foreach($result_project as &$val)
        {
            $val['project_list']=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id'])->order("down_time asc")->select();
            
            $val['project_count']=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id'])->count();
            $val['project_up_count']=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id']." and up_time=0")->count();
            
            
            foreach($val['project_list'] as &$rowtt)
            {
                $rowtt['up_time_data']=date("Y-m-d H:i:s",$rowtt['up_time']);
                $rowtt['down_time_data']=date("Y-m-d H:i:s",$rowtt['down_time']);
                $rowtt['createtime_data']=date("Y-m-d H:i:s",$rowtt['createtime']);
                $project_total_money+=$rowtt['total_price'];
                $finish_time+=$rowtt['duration'];
            }
            $finish_time+=$val['duration'];
            $val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('masseur_sn');
            $val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('nick_name');
            $val['cover']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('cover');
            if($val['cover']){
               $val['cover']=$this->host_url.$val['cover']; 
            }
            
            $val['up_time_data']=date("Y-m-d H:i:s",$val['up_time']);
            $val['down_time_data']=date("Y-m-d H:i:s",$val['down_time']);
            $val['createtime_data']=date("Y-m-d H:i:s",$val['createtime']);
            $val['total_price']=$project_total_money;
            
        }
        
        
        
        //$this->assign('result_project',$result_project);
        //预计结束时间
        //$this->assign('finish_time',$finish_time);
        //订单是否全部上钟
        $up_count=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and up_time=0  and masseur_id=".$this->masseur_id)->count();
        //$this->assign('up_count',$up_count);
        //全部下钟
        $down_count=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and down_time=0 and masseur_id=".$this->masseur_id)->count();
        //$this->assign('down_count',$down_count);
        
        //是否有一个上钟
        $is_one_uptime=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and up_time > 0 and masseur_id=".$this->masseur_id)->count();
        //$this->assign('is_one_uptime',$is_one_uptime);
		
		$is_evaluation=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and is_evaluation=1 and masseur_id=".$this->masseur_id)->count();
		
        //获取订单产品
        $result_product=$this->ordersproduct->where("shop_id=".$this->shop_id." and order_id=".$order_info['id'].' and masseur_id='.$this->masseur_id)->select();
        foreach($result_product as &$val)
        {
            if($val['masseur_id']!=0)
            {
                $val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('masseur_sn');
                $val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('nick_name');
            }else
            {
                $val['masseur_name']='-';
                $val['nick_name']='-';
            }
        }
        //$this->assign('result_product',$result_product);
        //获取订单提成
        $result_reward=$this->ordersreward->where("shop_id=".$this->shop_id." and order_id=".$order_info['id'])->select();
        foreach($result_reward as &$val)
        {
            
            $val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('masseur_sn');
            $val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('nick_name');
            
        }
        //计算支付明细
        $hykzk_sum=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=1")->sum("total_price");
        
        $hykzk_pay_sum=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=1")->sum("pay_money");
        $ckzk_sum=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=2")->sum("total_price");
        $qxkzk_sum=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=3")->sum("total_price");
        $lckzk_sum=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=4")->sum("total_price");
        
        $product_hykzk_sum=$this->ordersproduct->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=1")->sum("total_price");
        $product_hykzk_pay_sum=$this->ordersproduct->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=1")->sum("pay_money");
        
        
        
        unset($data);
        $data['code']=0;
        $data['order_info']=$order_info; //订单信息
        $data['member_info']=$member_info; //会员信息
        $data['result_project']=$result_project; //项目列表
        $data['up_count']=$up_count; //是否全部上钟
        $data['down_count']=$down_count; //是否全部下钟
        $data['is_one_uptime']=$is_one_uptime; //是否有一个上钟
		$data['is_evaluation']=$is_evaluation; //是否评价
        $data['result_product']=$result_product; //产品列表
        $data['result_reward']=$result_reward; //推荐提成列表
        //计算支付明细
        $data['hykzk_sum']=$hykzk_sum; //会员卡原价
        $data['hykzk_pay_sum']=$hykzk_pay_sum; //会员卡支付总数
        $data['ckzk_sum']=$ckzk_sum; //次卡抵扣价格
        $data['qxkzk_sum']=$qxkzk_sum; //期限卡抵扣价格
        $data['lckzk_sum']=$result_reward; //疗程卡抵扣价格
        $data['product_hykzk_sum']=$product_hykzk_sum; //产品总价
        $data['product_hykzk_pay_sum']=$result_reward; //产品支付价格
        outJson($data);
        
        //$this->display();
    }


    //删除项目
    public function del_project()
    {
        $id = I('post.id',0,'intval');
        $project_info=$this->ordersproject->where("shop_id=".$this->shop_id." and id=".$id)->find();
        $count=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$project_info['order_id'].' and masseur_id='.$this->masseur_id)->count();
        if($count < 2)
        {
            unset($data);
            $data['code']=1;
            $data['msg']='最少要选择一个项目！';
            outJson($data);
        }
        if ($this->ordersproject->delete($id)!==false) {
            $is_download=$this->ordersproject->where("order_id=".$project_info['order_id']." and masseur_id=".$project_info['masseur_id'])->count();
            if($is_download == 0)
            {
                $this->shopscheduling->where("shop_id=".$this->shop_id." and masseur_id=".$project_info['masseur_id']." and id=".$project_info['scheduling_id'])->save(array('is_lock' => 1));
            }
            
            $this->orders->where("id=".$project_info['order_id'])->setDec('total_amount',$project_info['total_price']);
            unset($data);
            $data['code']=0;
            $data['msg']='删除成功！';
            outJson($data);
        } else {
            unset($data);
            $data['code']=1;
            $data['msg']='删除失败！';
            outJson($data);
        }   
    }

    //删除产品
    public function del_product()
    {
        $id = I('post.id',0,'intval');
        $order_product_info=$this->ordersproduct->where("id=".$id)->find();
        $this->orders->where("id=".$order_product_info['order_id'])->setDec('total_amount',$order_product_info['total_price']);
        if ($this->ordersproduct->delete($id)!==false) {
            unset($data);
            $data['code']=0;
            $data['msg']='删除成功！';
            outJson($data);
        } else {
            unset($data);
            $data['code']=1;
            $data['msg']='删除失败！';
            outJson($data);
        }   
    }

    //更换包间
    public function replace_room()
    {

        $room_id=I('post.room_id');
        $bed_id=I('post.bed_id');
        $order_id=I('post.order_id');
        if($order_id=='')
        {
            unset($data);
            $data['code']=1;
            $data['msg']='订单不存在！';
            outJson($data);
        }
        if($bed_id=='')
        {
            unset($data);
            $data['code']=1;
            $data['msg']='请选择床位';
            outJson($data);
        }
        unset($data);
        $data=array(
            'room_id' => $room_id,
            'bed_id' => $bed_id,
        );
        $order_info=$this->orders->where("id=".$order_id." and shop_id=".$this->shop_id)->find();
        $this->shoproom->where("id=".$order_info['room_id']." and shop_id=".$this->shop_id)->save(array('state' => 3));
        $this->shopbed->where("id=".$order_info['bed_id']." and shop_id=".$this->shop_id)->save(array('is_lock' => 1));
        $result=$this->orders->where("id=".$order_id." and shop_id=".$this->shop_id)->save($data);
        if($result)
        {
            $this->shoproom->where("id=".$room_id." and shop_id=".$this->shop_id)->save(array('state' => 0));
            $this->shopbed->where("id=".$bed_id." and shop_id=".$this->shop_id)->save(array('is_lock' => 0));
            unset($data);
            $data['code']=0;
            $data['msg']='更新成功！';
            outJson($data);
        }else
        {
            unset($data);
            $data['code']=1;
            $data['msg']='更新失败！';
            outJson($data);
        }
    }

    public function use_room()
    {
        
        //空闲锁牌
        $free_lockcard_list=$this->shoplockcard->where("is_lock=1 and shop_id=".$this->shop_id." and status=1")->order("sort asc")->select(); //未锁定
        //$unfree_lockcard_list=$this->shoplockcard->where("is_lock=0 and shop_id=".$this->shop_id)->order("sort asc")->select(); //锁定
        //$this->assign('free_lockcard_list',$free_lockcard_list);
        //订单列表
        $order_list=$this->orders->where("shop_id=".$this->shop_id." and (status=1 or status=2)")->order("createtime desc")->select();
        //$this->assign('order_list',$order_list);
        //空闲房间
        $free_room_list=$this->shoproom->where("shop_id=".$this->shop_id." and state=1")->order("id asc")->select();
        foreach($free_room_list as &$val)
        {
            //总房间数
            $val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shop_id." and state=1 and room_id=".$val['id'])->count();
            //剩余房间数
            $val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shop_id." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
        }
        //$this->assign('free_room_list',$free_room_list);
        //$this->assign('unfree_lockcard_list',$unfree_lockcard_list);
        //轮钟排序
        //$result=$this->shopscheduling->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")->where("a.start_time < ".time()." and a.end_time > ".time()." and a.shop_id=".$this->shop_id." and a.is_lock=1 ".$where)->field("a.masseur_id as masseur_id,a.status as status,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.category_id as category_id")->order("a.id asc")->select();
        //echo $this->shopscheduling->getLastSql();
        //$result=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shop_id." and is_lock=1")->order("id asc")->select();
        $round_clock=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shop_id." and is_lock=1")->order("id asc")->select();
        foreach($round_clock as &$val)
        {
            unset($order_project_info);
            $order_project_info=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and shop_id=".$this->shop_id." and is_del=0 and types=1")->order("down_time desc")->find();
            if($order_project_info)
            {
                $val['down_time']=$order_project_info['down_time'];
            }else
            {
                $val['down_time']=0;
            }
            $val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('masseur_sn');
            $val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('masseur_name');
            $val['masseur_level']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('category_id');
            $val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('nick_name');
        }
        $arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $round_clock);
        
        array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$round_clock);
        //$this->assign("round_clock",$round_clock);
        
        
        $free_room_count=$this->shoproom->where("shop_id=".$this->shop_id." and state=1")->count();
        $use_room_count=$this->shoproom->where("shop_id=".$this->shop_id." and state!=1")->count();
        $free_masseur_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shop_id." and is_lock=1")->count();
        $use_masseur_count=$this->shopscheduling->where("start_time < ".time()." and end_time > ".time()." and shop_id=".$this->shop_id." and is_lock=0")->count();
        
        
        
        unset($data);
        $data['code']=0;
        $data['msg']='信息获取成功！';
        $data['free_room_count']=$free_room_count;
        $data['use_room_count']=$use_room_count;
        $data['free_masseur_count']=$free_masseur_count;
        $data['use_masseur_count']=$use_masseur_count;
        outJson($data);
        
        
        //$this->display('index');
    }

    //获取床位列表
    public function bed_list()
    {
        $room_id=I('post.room_id');
        if(empty($room_id))
        {
            unset($data);
            $data['code']=1;
            $data['msg']="房间不存在！";
            outJson($data);
        }
        $bed_list=$this->shopbed->field("id,bed_no")->where("room_id=".$room_id." and shop_id=".$this->shop_id." and state=1 and is_lock=1")->order("sort asc")->select();
        if($bed_list)
        {
            unset($data);
            $data['code']=0;
            $data['msg']='信息获取成功！';
            $data['data']=$bed_list;
            outJson($data);
        }else
        {
            unset($data);
            $data['code']=0;
            $data['msg']='没有床位了！';
            $data['data']=array();
            outJson($data);
        }
        
    }

    //项目列表
    public function get_project()
    {
        $where="shop_id=".$this->shop_id;
        $where.=" and state=1";
        $project_ids = $this->shopmasseur->where($where.' and id='.$this->masseur_id)->getfield('master_item',true);
        $map='';
        if(!empty($project_ids)){
            $project_ids = implode(',', $project_ids);
            $map.=" and id in(".$project_ids.")";
        }

        $cate_list=$this->itemcategory->field("id,category_name")->where("shop_id=".$this->shop_id)->order("sort asc")->select();
        foreach($cate_list as &$val)
        {
            $val['project_list']=$this->shopitem->field("id,item_sn,item_name,item_price,item_duration,cover")->where("shop_id=".$this->shop_id." and category_id=".$val['id'].$map)->order("id desc")->select();
            foreach($val['project_list'] as &$rowcc)
            {
                $rowcc['cover']=$this->host_url.$rowcc['cover'];
            }
        }
        if($cate_list)
        {
            unset($data);
            $data['code']=0;
            $data['msg']='获取成功！';
            $data['data']=$cate_list;
            outJson($data);
        }else
        {
            unset($data);
            $data['code']=0;
            $data['msg']='获取成功！';
            $data['data']=array();
            outJson($data);
        }
    }
    //产品列表
    public function get_product()
    {
       $cate_list=$this->productcategory->field("id,category_name")->where("shop_id=".$this->shop_id)->order("sort asc")->select();
        foreach($cate_list as &$val)
        {
            $val['product_list']=$this->shopproduct->field("id,product_sn,product_name,product_price,cover")->where("shop_id=".$this->shop_id." and category_id=".$val['id'])->order("id desc")->select();
            foreach($val['product_list'] as &$rowcc)
            {
                $rowcc['cover']=$this->host_url.$rowcc['cover'];
            }
        }
        if($cate_list)
        {
            unset($data);
            $data['code']=0;
            $data['msg']='获取成功！';
            $data['data']=$cate_list;
            outJson($data);
        }else
        {
            unset($data);
            $data['code']=0;
            $data['msg']='获取成功！';
            $data['data']=array();
            outJson($data);
        }
    }



//追加信息提交订单
    public function post_additional_orders()
    {
        $chain_id = $this->chain_id;
        $shop_id = $this->shop_id;
        $masseur_id=$this->masseur_id;
        $order_id=I('post.order_id');
        $p_orders=$_POST['p_orders'];
        if(empty($chain_id) || empty($shop_id) || empty($order_id))
        {
            $this->error("参数错误！");
        }
        $p_orders=json_decode($p_orders,true);
        
        if(count($p_orders)==0)
        {
            unset($data);
            $data['code']=1;
            $data['msg']='项目、产品不能同时为空！';
            outJson($data);
        }
        $order_info=$this->orders->where("shop_id=".$this->shop_id." and id=".$order_id)->find();
        $orderproject_info = $this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_id." and masseur_id=".$this->masseur_id)->find();
        if($order_info)
        {
            foreach($p_orders as $key=>$val)
            {
                
                if($val['p_types']==1)
                {
                    $project_info=$this->shopitem->where("id=".$val['p_id']." and shop_id=".$this->shop_id)->find();
                    $masseur_info=$this->shopscheduling->where("shop_id=".$this->shop_id." and masseur_id=".$masseur_id." and start_time < ".time()." and end_time > ".time())->find();
                    if(!$masseur_info)
                    {
                        unset($data);
                        $data['code']=1;
                        $data['msg']='此技师当前不值班！';
                        outJson($data);
                    }
                    
                    $masseur_info_level=$this->shopmasseur->where("id=".$masseur_id)->getField("category_id");
                    $level_proportion=$this->masseurcategory->where("id=".$masseur_info_level)->getField("level_proportion");
                    
                    unset($project_data);
                    $project_data=array(
                        'chain_id' => $chain_id,
                        'shop_id' => $shop_id,
                        'order_id' => $order_info['id'],
                        'masseur_id' => $masseur_id,
                        'scheduling_id' => $masseur_info['id'],
                        'project_id' => $val['p_id'],
                        'title' => $project_info['item_name'],
                        'duration' => $project_info['item_duration'],
                        'unit_price' => $project_info['item_price']+$level_proportion,
                        'number' => $val['number'],
                        'total_price' => ($val['number']*$project_info['item_price'])+($val['number']*$level_proportion),
                        'loop_reward' => $project_info['turn_reward'],
                        'point_reward' => $project_info['point_reward'],
                        'add_reward' => $project_info['add_reward'],
                        'project_reward' => $project_info['rec_reward'],
                        'is_discount' => $project_info['is_discount'],
                        'up_time' => 0,
                        'down_time' => 0,
                        'status' => 1,
                        'types' => $val['types'],
                        'createtime' => time(),
                        'is_confirm' => $orderproject_info['is_confirm'],
                        'accept_time' => $orderproject_info['accept_time'],
                    );
                    $this->shopscheduling->where("shop_id=".$this->shop_id." and masseur_id=".$masseur_id." and start_time < ".time()." and end_time > ".time())->save(array('is_lock' => 0));
                    $this->ordersproject->add($project_data);
                    $pay_total_money+=($val['number']*$project_info['item_price'])+($val['number']*$level_proportion);
                }else if($val['p_types']==2)
                {
                   
                    $product_info=$this->shopproduct->where("id=".$val['p_id']." and shop_id=".$this->shop_id)->find();
                    unset($product_data);
                    $product_data=array(
                        'chain_id' => $chain_id,
                        'shop_id' => $shop_id,
                        'order_id' => $order_info['id'],
                        'masseur_id' => $masseur_id,
                        'product_id' => $val['p_id'],
                        'title' => $product_info['product_name'],
                        'unit_price' => $product_info['product_price'],
                        'number' => $val['number'],
                        'total_price' => $val['number']*$product_info['product_price'],
                        'product_reward' => $product_info['rec_reward'],
                        'is_discount' => $product_info['is_discount'],
                        'createtime' => time(),
                    );
                    $this->ordersproduct->add($product_data);
                    $pay_total_money+=$val['number']*$product_info['product_price'];
                }
            }
            $this->orders->where("id=".$order_id)->setInc('total_amount',$pay_total_money);
            unset($data);
            $data['code']=0;
            $data['msg']='追加成功！';
            outJson($data);
        }else
        {
            unset($data);
            $data['code']=1;
            $data['msg']='下单失败！';
            outJson($data);
        }
        
    }
    //获取房间和对应的床位
    public function room_bed()
    {
        $result=$this->shoproom->field("id,room_name,category_id,floor_id,state")->where("shop_id=".$this->shop_id." and state=1")->order("id asc")->select();
        foreach($result as &$val)
        {
            $val['category_name']=$this->roomcategory->where("id=".$val['category_id'])->getField('category_name');
            //总房间数
            $val['bed_total_count']=$this->shopbed->where("shop_id=".$this->shop_id." and state=1 and room_id=".$val['id'])->count();
            //剩余房间数
            $val['bed_yu_count']=$this->shopbed->where("shop_id=".$this->shop_id." and state=1 and is_lock=1 and room_id=".$val['id'])->count();
            $val['bed_list']=$this->shopbed->field("id,bed_no")->where("shop_id=".$this->shop_id." and state=1 and is_lock=1 and room_id=".$val['id'])->order("sort asc")->select();
        }
        if($result)
        {
            unset($data);
            $data['code']=0;
            $data['msg']='获取成功！';
            $data['data']=$result;
            outJson($data);
        }else
        {
            unset($data);
            $data['code']=0;
            $data['msg']='获取成功！';
            $data['data']=array();
            outJson($data);
        }
    }
    //确认接单接口
    public function confirm_order(){
        $order_id = I('post.id');//订单id
        // $order_id = $this->ordersproject->where('id='.$id)->getField('order_id');
        if(empty($order_id)){
            unset($data);
            $data['code']=1;
            $data['msg']='参数错误！';
            outJson($data);
        }
        $time = time();
        $result = $this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_id." and is_confirm=0 and masseur_id=".$this->masseur_id)->save(array('is_confirm'=>1,'accept_time'=>$time));
        $shopuser_id = $this->orders->where("shop_id=".$this->shop_id." and id=".$order_id)->getField('shopuser_id');
        $masseur_name = $this->shopmasseur->where('id='.$this->masseur_id)->getfield('nick_name');
        if($result!==false){
            $shopuser_info = $this->shopuser->where('id='.$shopuser_id)->find();
            if($shopuser_info['device']!=''){
                unset($title);
                unset($content);
                $title=$masseur_name."健康师已经接单！";
                $content = $masseur_name."健康师已经接单！";
                $device[] = $shopuser_info['device'];
                $extra = array("push_type" => 1, "order_id" => $order_id);
                $audience='{"alias":'.json_encode($device).'}';
                $extras=json_encode($extra);
                $os=$shopuser_info['os'];
                $res=jpush_shop_send($title,$content,$audience,$os,$extras);
            }
            unset($data);
            $data['code']=0;
            $data['msg']='接单成功！';
            $data['info']=$time;
            outJson($data);
        }
    }
    //备注
    public function note(){
        $id = I('post.id');//订单id
        $order = $this->orders->where('id='.$id)->getField('note');
        unset($data);
        $data['code']=0;
        $data['msg']='数据请求成功！';
        $data['info']=$order;
        outJson($data);

    }
    //提交备注
    public function post_note(){
        $id = I('post.id');
        $note = I('post.note');
        $result = $this->orders->where('id='.$id)->save(array('note'=>$note));
        if($result!==false){
            unset($data);
            $data['code']=0;
            $data['msg']='备注成功！';
            outJson($data);
        }else{
            unset($data);
            $data['code']=1;
            $data['msg']='备注失败!';
            outJson($data);
        }
    }


    //全部上钟
    public function all_up()
    {
        $id = I('post.order_id',0,'intval');
        $order_info=$this->orders->where("shop_id=".$this->shop_id." and id=".$id)->find();
        if($order_info)
        {
            $result=$this->ordersproject->where("order_id=".$order_info['id']." and shop_id=".$this->shop_id." and up_time=0 and masseur_id=".$this->masseur_id)->save(array('up_time' => time()));
            if($result)
            {
				$room_name = $this->shoproom->where('id='.$order_info['room_id'])->getField('room_name');
                $masseur_name = $this->shopmasseur->where('id='.$this->masseur_id)->getField('nick_name');
                $shopuser_info = $this->shopuser->where('id='.$order_info['shopuser_id'])->find();
                if($shopuser_info['device']!=''){
                    unset($title);
                    unset($content);
					$title="【".$room_name."】包间，【".$masseur_name."】健康师的项目已全部上钟！";
					$content = "【".$room_name."】包间，【".$masseur_name."】健康师的项目已全部上钟！";
                    $device[] = $shopuser_info['device'];
                    $extra = array("push_type" => 2, "order_id" => $id);
                    $audience='{"alias":'.json_encode($device).'}';
                    $extras=json_encode($extra);
                    $os=$shopuser_info['os'];
                    $res=jpush_shop_send($title,$content,$audience,$os,$extras);
                }
                unset($data);
                $data['code']=0;
                $data['msg']='全部上钟成功！';
                outJson($data);
            }else
            {
                unset($data);
                $data['code']=1;
                $data['msg']='上钟失败！';
                outJson($data);
            }
        }else
        {
            unset($data);
            $data['code']=1;
            $data['msg']='订单不存在！';
            outJson($data);
        }
    }
    //全部下钟
    public function all_down()
    {
        $id = I('post.order_id',0,'intval');
        $order_info=$this->orders->where("shop_id=".$this->shop_id." and id=".$id)->find();
        if($order_info)
        {
            $orders_project=$this->ordersproject->where("order_id=".$order_info['id']." and shop_id=".$this->shop_id." and down_time=0 and masseur_id=".$this->masseur_id)->select();
            foreach($orders_project as $val)
            {
                $this->shopscheduling->where("shop_id=".$this->shop_id." and masseur_id=".$val['masseur_id']." and id=".$val['scheduling_id'])->save(array('is_lock' => 1));
                $down_time = $val['up_time'] + $val['duration']*60;
                if($down_time-time()>300){
                    unset($data);
                    $data['code'] = 1;
                    $data['msg'] = '不能提前下钟！';
                    outJson($data);
                }
            }
            $result=$this->ordersproject->where("order_id=".$order_info['id']." and shop_id=".$this->shop_id." and down_time=0 and masseur_id=".$this->masseur_id)->save(array('down_time' => time()));

            $complete = $this->ordersproject->where($this->where.' and order_id='.$order_info['id'].' and (up_time=0 or down_time=0 or pay_time=0) and status=1 and is_del=0')->count();
            if($complete==0)
            {
                $this->shoplockcard->where("id=".$order_info['lockcard_id']." and shop_id=".$this->shop_id)->save(array("is_lock" => 1));
                $this->shopbed->where("id=".$order_info['bed_id']." and shop_id=".$this->shop_id)->save(array('is_lock' => 1));
                $this->shoproom->where('id='.$order_info['room_id'])->save(array('state'=>3));
            }

            if($result)
            {
				$room_name = $this->shoproom->where('id='.$order_info['room_id'])->getField('room_name');
                $masseur_name = $this->shopmasseur->where('id='.$this->masseur_id)->getField('nick_name');
                $shopuser_info = $this->shopuser->where('id='.$order_info['shopuser_id'])->find();
                if($shopuser_info['device']!=''){
                    unset($title);
                    unset($content);
					$title="【".$room_name."】包间，【".$masseur_name."】健康师的项目已全部下钟！";
					$content = "【".$room_name."】包间，【".$masseur_name."】健康师的项目已全部下钟！";
                    $device[] = $shopuser_info['device'];
                    $extra = array("push_type" => 3, "order_id" => $id);
                    $audience='{"alias":'.json_encode($device).'}';
                    $extras=json_encode($extra);
                    $os=$shopuser_info['os'];
                    $res=jpush_shop_send($title,$content,$audience,$os,$extras);
                }
                unset($data);
                $data['code']=0;
                $data['msg']='全部下钟成功！';
                outJson($data);
            }else
            {
                unset($data);
                $data['code']=1;
                $data['msg']='下钟失败！';
                outJson($data);
            }
        }else
        {
            unset($data);
            $data['code']=1;
            $data['msg']='订单不存在！';
            outJson($data);
        }
    }
    //首页订单接口（新）
    public function index_order(){
        $this->where.=" and ((up_time=0) or (down_time=0) or (pay_time=0) or (is_evaluation=0))";
        $orderprojects = $this->ordersproject->where($this->where.' and masseur_id='.$this->masseur_id.' and status=1  and is_del=0')->order('createtime desc,id desc')->select();
        foreach ($orderprojects as $key => $value) {
            $evaluation_count = $this->ordersproject->where('order_id='.$value['order_id'].' and is_evaluation=1')->count();
            if($evaluation_count == 0){
                $orderproject = $value;
                break;
            }
        }
        if(empty($orderproject)){
            unset($data);
            $data['code']=1;
            $data['msg']='暂无数据';
            $data['order_info']=array();
            outJson($data);
        }
        $order_info=$this->orders->where("shop_id=".$this->shop_id." and id=".$orderproject['order_id'])->find();
        if(!$order_info)
        {
            unset($data);
            $data['code']=1;
            $data['msg']='订单不存在！';
            outJson($data);
        }
        $role_id = $this->shopuser->where('id='.$order_info['shopuser_id'])->getfield('role_id');
        if($role_id == '0'){
            $order_info['from'] = '管理员';
        }else{
            $order_info['from'] = $this->shoprole->where('id='.$role_id)->getfield('name');
        }

        $order_info['pay_time_data']=date("Y-m-d H:i:s",$order_info['pay_time']);
        $order_info['createtime_data']=date("Y-m-d H:i:s",$order_info['createtime']);
        $order_info['room_name'] = $this->shoproom->where('id='.$order_info['room_id'])->getfield('room_name');
        
        //会员信息
        $member_info='';
        if($order_info['member_id']!=0)
        {
            $member_info=$this->shopmember->where("shop_id=".$this->shop_id." and id=".$order_info['member_id'])->find();
            //$this->assign('member_info',$member_info);
        }
        //获取订单项目
        $finish_time=0;
        $project_total_money=0;
        $result_project=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id'].' and masseur_id='.$this->masseur_id)->order("down_time asc")->group("masseur_id")->select();
        $order_info['is_confirm'] = $this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id'].' and masseur_id='.$this->masseur_id." and is_confirm=1")->count();
        foreach($result_project as &$val)
        {
            $val['project_list']=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id'])->order("down_time asc")->select();
            
            $val['project_count']=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id'])->count();
            $val['project_up_count']=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and masseur_id=".$val['masseur_id']." and up_time=0")->count();
            
            
            foreach($val['project_list'] as &$rowtt)
            {
                $rowtt['up_time_data']=date("Y-m-d H:i:s",$rowtt['up_time']);
                $rowtt['down_time_data']=date("Y-m-d H:i:s",$rowtt['down_time']);
                $rowtt['createtime_data']=date("Y-m-d H:i:s",$rowtt['createtime']);
                $project_total_money+=$rowtt['total_price'];
                $finish_time+=$rowtt['duration'];
            }
            $finish_time+=$val['duration'];
            $val['masseur_sn']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('masseur_sn');
            $val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('nick_name');
            $val['cover']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('cover');
            if($val['cover']){
                $val['cover'] = $this->host_url.$val['cover'];
            }
            $val['up_time_data']=date("Y-m-d H:i:s",$val['up_time']);
            $val['down_time_data']=date("Y-m-d H:i:s",$val['down_time']);
            $val['createtime_data']=date("Y-m-d H:i:s",$val['createtime']);
            $val['total_price']=$project_total_money;
            
        }
        //订单是否全部上钟
        $up_count=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and up_time=0  and masseur_id=".$this->masseur_id)->count();
        //$this->assign('up_count',$up_count);
        //全部下钟
        $down_count=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and down_time=0 and masseur_id=".$this->masseur_id)->count();
        //$this->assign('down_count',$down_count);
        
        //是否有一个上钟
        $is_one_uptime=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and up_time > 0 and masseur_id=".$this->masseur_id)->count();
        //$this->assign('is_one_uptime',$is_one_uptime);
		
		//是否评价
		$is_evaluation=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and is_evaluation=1 and masseur_id=".$this->masseur_id)->count();
		
        //获取订单产品
        $result_product=$this->ordersproduct->where("shop_id=".$this->shop_id." and order_id=".$order_info['id'].' and masseur_id='.$this->masseur_id)->select();
        foreach($result_product as &$val)
        {
            if($val['masseur_id']!=0)
            {
                $val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('masseur_sn');
                $val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('nick_name');
            }else
            {
                $val['masseur_name']='-';
                $val['nick_name']='-';
            }
        }
        //$this->assign('result_product',$result_product);
        //获取订单提成
        $result_reward=$this->ordersreward->where("shop_id=".$this->shop_id." and order_id=".$order_info['id'])->select();
        foreach($result_reward as &$val)
        {
            
            $val['masseur_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('masseur_sn');
            $val['nick_name']=$this->shopmasseur->where("id=".$val['masseur_id']." and shop_id=".$this->shop_id)->getField('nick_name');
            
        }
		
		
        //计算支付明细
        $hykzk_sum=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=1")->sum("total_price");
        
        $hykzk_pay_sum=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=1")->sum("pay_money");
        $ckzk_sum=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=2")->sum("total_price");
        $qxkzk_sum=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=3")->sum("total_price");
        $lckzk_sum=$this->ordersproject->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=4")->sum("total_price");
        
        $product_hykzk_sum=$this->ordersproduct->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=1")->sum("total_price");
        $product_hykzk_pay_sum=$this->ordersproduct->where("shop_id=".$this->shop_id." and order_id=".$order_info['id']." and card_type=1")->sum("pay_money");
        
        
        
        unset($data);
        $data['code']=0;
        $data['order_info']=$order_info; //订单信息
        $data['member_info']=$member_info; //会员信息
        $data['result_project']=$result_project; //项目列表
        $data['up_count']=$up_count; //是否全部上钟
        $data['down_count']=$down_count; //是否全部下钟
		$data['is_evaluation']=$is_evaluation; //是否评价
        $data['is_one_uptime']=$is_one_uptime; //是否有一个上钟
        // $data['result_product']=$result_product; //产品列表
        // $data['result_reward']=$result_reward; //推荐提成列表
        //计算支付明细
        // $data['hykzk_sum']=$hykzk_sum; //会员卡原价
        // $data['hykzk_pay_sum']=$hykzk_pay_sum; //会员卡支付总数
        // $data['ckzk_sum']=$ckzk_sum; //次卡抵扣价格
        // $data['qxkzk_sum']=$qxkzk_sum; //期限卡抵扣价格
        // $data['lckzk_sum']=$result_reward; //疗程卡抵扣价格
        $data['product_hykzk_sum']=$product_hykzk_sum; //产品总价
        $data['product_hykzk_pay_sum']=$result_reward; //产品支付价格
        outJson($data);

    }
	
	//二维码生成接口
	public function ewm_img()
	{
		$order_id=I('post.order_id');
		$masseur_id=I('post.masseur_id');
		if($order_id=='' || $masseur_id=='')
		{
			unset($data);
            $data['code']=1;
            $data['msg']='订单不存在！';
            outJson($data);
		}
		
		$is_downtime=$this->ordersproject->where("order_id=".$order_id." and masseur_id=".$masseur_id." and down_time=0")->find();
		if($is_downtime)
		{
			//$this->error("技师还没有下钟！");
			unset($data);
            $data['code']=1;
            $data['msg']='技师还没有下钟！';
            outJson($data);
		}
		
		$is_eval=$this->ordersproject->where("order_id=".$order_id." and masseur_id=".$masseur_id." and is_evaluation=1")->find();
		if($is_eval)
		{
			//$this->error("已经评价过了！");
			unset($data);
            $data['code']=1;
            $data['msg']='已经评价过了！';
            outJson($data);
		}
		
		require_once VENDOR_PATH."phpqrcode/phpqrcode.php";	
		$url="http://". $_SERVER['SERVER_NAME'] .'/index.php/Home/evaluate/index/order_id/'.$order_id.'/masseur_id/'.$masseur_id;
		//二维码
		$ewm_img ="./Uploads/ewm/".$order_id."_".$masseur_id.".png";
		\QRcode::png($url, $ewm_img);
		$QR = imagecreatefromstring(file_get_contents($ewm_img));
		//输出图片
		imagepng($QR, $ewm_img);
		unset($data);
		$data['code']=0;
		$data['msg']='生成成功！';
		$data['data']="http://". $_SERVER['SERVER_NAME'].substr($ewm_img, 1);
		outJson($data);
	}
	
	//意见反馈
	public function feedback()
	{
		$content=I('post.content');
		if(empty($content))
		{
			unset($data);
            $data['code']=1;
            $data['msg']='请输入内容！';
            outJson($data);
		}
		unset($data_array);
		$data_array=array(
			'chain_id' => $this->chain_id,
			'shop_id' => $this->shop_id,
			'masseur_id' => $this->masseur_id,
			'content' => $content,
			'type_id' => 1,
			'createtime' => time(),
		);
		$result=$this->feedback->add($data_array);
		if($result)
		{
			unset($data);
            $data['code']=0;
            $data['msg']='反馈成功！';
            outJson($data);
		}else
		{
			unset($data);
            $data['code']=1;
            $data['msg']='反馈失败！';
            outJson($data);
		}
	}
	
	//推送提醒
	public function call_admin()
	{
		$order_id=I('post.order_id');
		if(empty($order_id))
		{
			unset($data);
            $data['code']=1;
            $data['msg']='订单不存在！';
            outJson($data);
		}
		$order_info=$this->orders->where("id=".$order_id)->find();
		$room_name = $this->shoproom->where('id='.$order_info['room_id'])->getField('room_name');
		$shopuser_info = $this->shopmasseur->where('id='.$this->masseur_id)->find();
		$shop_user_info=$this->shopuser->where("id=".$order_info['shopuser_id'])->find();
		if($shop_user_info['device']!=''){
			unset($title);
			unset($content);
			$title="【".$room_name."】包间，【".$shopuser_info['nick_name']."】健康师呼叫您！";
			$content = "【".$room_name."】包间，【".$shopuser_info['nick_name']."】健康师呼叫您！";
			$device[] = $shop_user_info['device'];
			$extra = array("push_type" => 5, "order_id" => $order_id);
			$audience='{"alias":'.json_encode($device).'}';
			$extras=json_encode($extra);
			$os=$shop_user_info['os'];
			$res=jpush_shop_send($title,$content,$audience,$os,$extras);
		}
		unset($data);
		$data['code']=0;
		$data['msg']='呼叫成功！';
		outJson($data);
		
	}
	//修改密码
	public function edit_password()
	{
		$old_password=I('post.old_password');
		$password=I('post.password');
		$new_password=I('post.new_password');
		if(empty($old_password))
		{
			unset($data);
			$data['code']=1;
			$data['msg']='请输入旧密码';
			outJson($data);
		}
		if(empty($password))
		{
			unset($data);
			$data['code']=1;
			$data['msg']='请输入新密码';
			outJson($data);
		}
		if($password!=$new_password)
		{
			unset($data);
			$data['code']=1;
			$data['msg']='两次密码不同！';
			outJson($data);
		}
		$shopmasseur_info = $this->shopmasseur->where('id='.$this->masseur_id." and password='".sp_password($old_password)."'")->find();
		if($shopmasseur_info)
		{
			unset($data_array);
			$data_array=array(
				'password' => sp_password($new_password),
			);
			$res=$this->shopmasseur->where('id='.$this->masseur_id)->save($data_array);
			if($res)
			{
				unset($data);
				$data['code']=0;
				$data['msg']='修改成功！';
				outJson($data);
			}else
			{
				unset($data);
				$data['code']=1;
				$data['msg']='修改失败！';
				outJson($data);
			}
		}else
		{
			unset($data);
			$data['code']=1;
			$data['msg']='原始密码错误！';
			outJson($data);
		}
	}
    
}
?>