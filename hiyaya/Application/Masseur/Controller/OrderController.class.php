<?php
// +----------------------------------------------------------------------
// | 排钟列表
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: ALina
// +----------------------------------------------------------------------
namespace Masseur\Controller;
use Common\Controller\MasseurbaseController;
class OrderController extends MasseurbaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->ShopLeave = M("ShopLeave");//请假表
        $this->shopscheduling=M("ShopScheduling"); //排班表
        $this->ordersproject=M("OrdersProject"); //订单项目表
        $this->ordersproduct=M("OrdersProduct"); //订单产品表
        $this->shopmasseur=M("ShopMasseur"); //技师表

        $this->shopmemberlabel = D("ShopMemberlabel");//会员标签表
        $this->shopmember = M("ShopMember");//会员表
        $this->shopnumcard = M('ShopNumcard');//次卡表
        $this->shopdeadlinecard = M('ShopDeadlinecard');//期限卡表
        $this->shopcoursecard = M('ShopCoursecard');//疗程卡表
        $this->shopcourseproject = M('ShopCourseproject');//疗程卡项目表
        $this->financial = M('ShopFinancial');//明细表
        $this->setting = D("Setting");//参数表
        $this->shop = M("Shop");//店铺表
        $this->shopitem = M('ShopItem');//项目表
        $this->shoprole=M("ShopRole"); //商家权限
        $this->shopuser=M("ShopUser"); //商家管理员
        $this->charge = D("ShopCharge");//会员充值表
		$this->evaluation=M('Evaluation');
		$this->evalabel=M('Evalabel');

        $this->orders=M("Orders");
        $this->ShopRoom=M("ShopRoom");
        $this->where="shop_id=".$this->shop_id;
        $this->arr=array("1"=>"早班","2"=>"中班","3"=>"晚班","4"=>"休假","5"=>"请假");
        $this->arr_lock=array("0"=>"锁","1"=>"空");
    }
    //排钟列表
    public function index()
    {
        $round_clock=$this->shopscheduling
            ->table("dade_shop_scheduling as a")->join("left join dade_shop_masseur as b on a.masseur_id=b.id")
            ->where("a.start_time < ".time()." and a.end_time > ".time()." and b.state=1 and a.shop_id=".$this->shop_id)
            ->field("a.masseur_id as masseur_id,a.status as status,a.is_lock as is_lock,b.masseur_sn as masseur_sn,b.masseur_name as masseur_name,b.nick_name as nick_name,b.category_id as category_id,b.sex as masseur_sex")
            ->order("a.id asc")->select();
      
        foreach($round_clock as &$val)
        {
            unset($order_project_info);
            $order_project_info=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and shop_id=".$this->shop_id." and is_del=0 and (types=1 or types=4)")->order("down_time desc")->find();
            
            if($order_project_info)
            {
                $val['down_time']=$order_project_info['down_time']; 
            }
            else
            {
                $val['down_time']=0;
            }
            if($val['is_lock']==0)
			{
				unset($order_project);
				unset($order_info);
				$order_project=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and is_del=0")->order("id desc")->find();
				$order_info=$this->orders->where("id=".$order_project['order_id'])->find();
				$val['room_name']=$this->ShopRoom->where("id=".$order_info['room_id'])->getField("room_name");
			}else{
            	$val['room_name']="";
			}
            $val['status'] = $this->arr[$val['status']];
            $val['is_lock'] = $this->arr_lock[$val['is_lock']];
            $val['wheel_count']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and (types=1 or types=4) and is_del=0")->count();
            $val['point_count']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and types=2 and is_del=0")->count();
            $val['project_title']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and is_del=0")->order("id desc")->getField('title');

        }
   //         foreach($round_clock as &$val)
			// {
			// 	unset($order_project_info);
			// 	$order_project_info=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and shop_id=".$this->shop_id." and is_del=0 and types=1")->order("down_time desc")->find();
			// 	if($order_project_info)
			// 	{
			// 		$val['down_time']=$order_project_info['down_time'];
			// 	}else
			// 	{
			// 		$val['down_time']=0;
			// 	}
			// 	$val['wheel_count']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and types=1 and is_del=0")->count();
			// 	$val['point_count']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and types=2 and is_del=0")->count();
			// 	$val['project_title']=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and is_del=0")->order("id desc")->getField('title');
			// 	if($val['is_lock']==0)
			// 	{
			// 		unset($order_project);
			// 		unset($order_info);
			// 		$order_project=$this->ordersproject->where("masseur_id=".$val['masseur_id']." and is_del=0")->order("id desc")->find();
			// 		$order_info=$this->orders->where("id=".$order_project['order_id'])->find();
			// 		$val['room_name']=$this->ShopRoom->where("id=".$order_info['room_id'])->getField("room_name");
			// 	}else{
   //              	$val['room_name']="";
			// 	}
				
			// }
        $arr1 = array_map(create_function('$n', 'return $n["down_time"];'), $round_clock);

        array_multisort($arr1,SORT_ASC,SORT_NUMERIC,$round_clock);
        if(count($round_clock)>0)
        {
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = $round_clock;
        }
        else
        {
            $data['code'] = 1;
            $data['msg'] = 'error';
            $data['data'] = '';
        }
        outJson($data);
    }
    //会员详情
    public function member_detail(){
        $member_id = I('post.member_id');
        //基本信息
        $info = $this->shopmember->field('id,member_no,member_name,sex,member_tel,birthday,member_card,remark,is_msg,discount,balance,createtime')->where($this->where.' and id='.$member_id.' and state=1')->find();
         //次卡信息
        $card['member_card'] =  $this->shopmember->field('id,shop_id,member_card,balance,is_msg,discount')->where($this->where.' and id='.$member_id.' and state=1')->select();
        foreach ($card['member_card'] as $k1 => &$v1) {
            $v1['shop_name'] = $this->shop->where('id='.$v1['shop_id'])->getfield('shop_name');
            $v1['address'] = $this->shop->where('id='.$v1['shop_id'])->getfield('address');
        }
       
        $card['numcard'] = $this->shopnumcard->field('id,project_id,shop_id,card_num')->where($this->where.' and member_id='.$member_id)->select();
        if(!empty($card['numcard'])){
            foreach ($card['numcard'] as $k2 => &$v2) {
                $v2['project_name'] = $this->shopitem->where('id='.$v2['project_id'])->getfield('item_name');
                $v2['shop_name'] = $this->shop->where('id='.$v2['shop_id'])->getfield('shop_name');
                $v2['address'] = $this->shop->where('id='.$v2['shop_id'])->getfield('address');
            }
        }
        //期限卡信息
        $card['deadline'] = $this->shopdeadlinecard->field('id,project_id,shop_id,end_time,day_ceiling')->where($this->where.' and member_id='.$member_id)->select();
        if(!empty($card['deadline'])){
            foreach ($card['deadline'] as $k3 => &$v3) {
                $v3['project_name'] = $this->shopitem->where('id='.$v3['project_id'])->getfield('item_name');
                if(time()>=$v3['end_time']){
                    $v3['due_time'] = 1;
                }else{
                    $v3['due_time'] = 0;
                }
                $v3['end_time'] = date('Y-m-d',$v3['end_time']);
                $v3['shop_name'] = $this->shop->where('id='.$v3['shop_id'])->getfield('shop_name');
                $v3['address'] = $this->shop->where('id='.$v3['shop_id'])->getfield('address');
                $consumption_ceilings = $this->financial->where("transaction_type=3 and card_id=".$v3['id']." and createtime > ".strtotime(date("Y-m-d"))." and createtime < ".(strtotime(date("Y-m-d"))+3600*24))->select();
                $used = 0;
                if(!empty($consumption_ceilings)){
                    foreach ($consumption_ceilings as $k => $v) {
                        if($v['transaction_num'] < 0){
                            $used += ((-1)*$v['transaction_num']);
                        }
                    }
                   
                    $v3['day_ceiling'] = $v3['day_ceiling'] - $used;
                    
                }
            }
        }
        //疗程卡信息
        $card['course'] = $this->shopcoursecard->field('id,shop_id')->where('member_id='.$member_id)->select();
        if(!empty($card['course'])){
            
            foreach ($card['course'] as $k4 => &$v4) {
                $v4['projects'] = $this->shopcourseproject->field('project_id,card_num')->where($this->where.' and card_id='.$v4['id'])->select();
                $v4['count'] = $this->shopcourseproject->field('project_id,card_num')->where('card_id='.$v4['id'])->count();
                $v4['shop_name'] = $this->shop->where('id='.$v4['shop_id'])->getfield('shop_name');
                $v4['address'] = $this->shop->where('id='.$v4['shop_id'])->getfield('address');
                foreach ($v4['projects'] as $k => &$v) {
                    $v['project_name'] = $this->shopitem->where('id='.$v['project_id'])->getfield('item_name');
                    $v['item_price'] = $this->shopitem->where('id='.$v['project_id'])->getfield('item_price');
                }
               
            }
        }
        //交易明细
        $result = $this->financial->field('card_id,project_id,createtime,transaction_type,transaction_money,transaction_days,transaction_num,now_money,now_num,now_days,type')->where('member_id='.$member_id)->order('createtime desc,id desc')->select();
        if($result){
            foreach ($result as $key => $value) {
                $result[$key]['createtime'] = date('Y-m-d',$value['createtime']);
                if($value['transaction_type'] == '1'){
                    $result[$key]['send_money'] = $this->charge->where('id='.$value['card_id'])->getfield('send_money');
                }
                if(!empty($value['project_id'])){
                    $result[$key]['item_name'] = $this->shopitem->where('id='.$value['project_id'])->getfield('item_name');
                }
            }
        }else{
            $result = array();
        }
        $labels = $this->shopmemberlabel->field('id,label')->where($this->where.' and member_id='.$member_id)->order('sort asc')->select();
        // $label['default'] = $this->setting->field('id,title')->where('parentid=6 and status=1')->order('sort asc')->select();
        if($info){
            $info['createtime'] = date('Y-m-d H:i',$info['createtime']);
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = $info;
            $data['card'] = $card;
            $data['financial'] = $result;
            $data['tags'] = $labels;
            outJson($data);
        }else{
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['data'] = array();
            outJson($data);
        }
    }
    //健康档案
    public function get_archives(){
        $member_id = I('post.member_id');
        $tags['own_archives'] = $this->shopmemberlabel->field('id,label')->where($this->where.' and member_id='.$member_id)->order('sort asc')->select();
        $tags['hot_archives'] = $this->setting->field('id,title')->where('parentid=6 and status=1')->order('sort asc')->select();
        unset($data);
        $data['code'] = 0;
        $data['msg'] = 'success';
        $data['data'] = $tags;
        outJson($data);
    }
    //会员修改备注信息
    public function post_remark(){
        $id = I('post.member_id');
        $remark = I('post.remark');
        $result = $this->shopmember->where('id='.$id)->save(array('remark'=>$remark));
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
    //添加会员标签
    public function add_label(){
        $member_id = I('post.member_id');
        $labels = I('post.labels');
        $labels = explode(',', $labels);
        $this->shopmemberlabel->where('member_id='.$member_id)->delete();
        foreach ($labels as $key => $value) {
            unset($res);
            $res = array(
                'member_id'=>$member_id,
                'label'=>$value,
                'chain_id' => $this->chain_id,
                'shop_id' => $this->shop_id,
                'create_time'=>time(),
            );
           
            $result = $this->shopmemberlabel->add($res);
          

        }
        unset($data);
        $data['code'] = 0;
        $data['msg'] = 'success';
        outJson($data);
       
    }
    //删除会员标签
    public function del_label(){
        $member_id = I('post.member_id');
        $label_id = I('post.label_id');
        $result = $this->shopmemberlabel->where('member_id='.$member_id.' and id='.$label_id)->delete();
        if($result !== false){
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
    //订单派单推送
    public function push_order(){
        $order_id = I('post.order_id');
        $orderproject = $this->ordersproject->where($this->where." and order_id=".$order_id." and masseur_id=".$this->masseur_id)->order('createtime desc,id desc')->find();
        if(empty($orderproject)){
            unset($data);
            $data['code'] = 1;
            $data['msg'] = '无此订单信息！';
            outJson($data);
        }else{
            $shopuser_id = $this->orders->where('id='.$order_id)->getfield('shopuser_id');
            $role_id = $this->shopuser->where('id='.$shopuser_id)->getfield('role_id');
            if($role_id == '0'){
                $orderproject['from'] = '管理员';
            }else{
                $orderproject['from'] = $this->shoprole->where('id='.$role_id)->getfield('name');
            }
            $orderproject['createtime'] = date('Y-m-d H:i:s',$orderproject['createtime']);
            $room_id = $this->orders->where('id='.$orderproject['order_id'])->getfield('room_id');
            $orderproject['room_name'] = $this->ShopRoom->where('id='.$room_id)->getfield('room_name');
            unset($data);
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['info'] = $orderproject;
            outJson($data);
        }
        

    }
    //个人信息
    public function person_info(){
        $info['nick_name'] = $this->shopmasseur->where($this->where.' and id='.$this->masseur_id)->getfield('nick_name');
        $info['cover'] = $this->shopmasseur->where($this->where.' and id='.$this->masseur_id)->getfield('cover');
        if($info['cover']){
            $info['cover'] = $this->host_url.$info['cover'];
        }
        $info['stores'] = $this->shop->where('id='.$this->shop_id)->getfield('shop_name');
        $info['business_time'] = $this->shop->where('id='.$this->shop_id)->getfield('business_time');
        $info['success_order'] = $this->ordersproject->where($this->where.' and masseur_id='.$this->masseur_id.' and is_del=0 and is_confirm=1')->count();
        $info['today_order'] = $this->ordersproject->where($this->where.' and masseur_id='.$this->masseur_id.' and is_del=0 and is_confirm=1 and accept_time>'.strtotime(date('Y-m-d')).' and accept_time<'.time())->count();
        $info['today_evaluation'] = $this->evaluation->where($this->where.' and masseur_id='.$this->masseur_id.' and createtime>'.strtotime(date('Y-m-d')).' and createtime<'.time())->count();
        $info['mobile'] = $this->shop->where('id='.$this->shop_id)->getfield('mobile');
        //评价总分和评价率
        $evaluation_score = $this->evaluation->where($this->where.' and masseur_id='.$this->masseur_id.' and types=1')->sum('score');
        
        $all_evaluation = $this->evaluation->where($this->where.' and masseur_id='.$this->masseur_id)->count();
        $custom_evaluation = $this->evaluation->where($this->where.' and masseur_id='.$this->masseur_id.' and types=1')->count();
        $info['evaluation_score'] = round($evaluation_score/$custom_evaluation,2);
        $info['evaluation_rate'] = (round($custom_evaluation/$all_evaluation,2)*100).'%';
        unset($data);
        $data['code'] = 0;
        $data['msg'] = "success";
        $data['info'] = $info;
        outJson($data);
    }
	//评价列表
	public function evaluation_list()
	{
		$pagecur=!empty($_POST['pagecur']) ? $_POST['pagecur'] : 1;//当前第几个页
        $pagesize=!empty($_POST['pagesize']) ? $_POST['pagesize'] : 10;//每页显示个数
        $pagecur=($pagecur-1)*$pagesize;
        $result = $this->evaluation->field("id,order_id,score,lable_list,content,createtime")->where($this->where.' and masseur_id='.$this->masseur_id)->order('createtime desc')->limit($pagecur.",".$pagesize)->select();
        if($result)
		{
			foreach($result as &$val)
			{
				if($val['lable_list']!='')
				{
					$val['evalabel_list']=$this->evalabel->field("label_name")->where("id in (".$val['lable_list'].")")->select();
				}else
				{
					$val['evalabel_list']=array();
				}
				$val['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
				$val['nickname']='匿名';
				$val['nick_img']='';
			}
			unset($data);
			$data['code'] = 0;
			$data['msg'] = "success";
			$data['info'] = $result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code'] = 0;
			$data['msg'] = "success";
			$data['info'] = array();
			outJson($data);
		}
  
	}
    //提交头像
    public function post_cover(){
        $picname=$_FILES['pic']['name'];
        $picsize=$_FILES['pic']['size'];
        if($picname!="")
        {
            include_once(VENDOR_PATH."vendor/autoload.php");
            if($picsize>40960000)
            {
                unset($data);
                $data['code']=1;
                $data['message']="图片大小不能超过4M！";
                outJson($data); 
            }
            else
            {
                $type=strtolower(strstr($picname,'.'));
                if ($type!=".png"&&$type!=".jpg"&&$type!=".jpeg")
                {
                    unset($data);
                    $data['code']=1;
                    $data['message']="只支持png和jpg两种格式！";
                    outJson($data); 
                }
                $rand=rand(1000,9999);
                $filename=time().$rand;
                $dir="./Uploads/cover/".date("Y-m-d")."/";                
                if(!is_dir($dir))
                {
                    mkdir($dir,0777);
                }
                $pics=$filename.$type;
                //上传路径
                $pic_path=$dir.$pics;
                if(move_uploaded_file($_FILES['pic']['tmp_name'],$pic_path))
                {
                    $pic_path=$pic_path;

                }
                else
                {
                    unset($data);
                    $data['code']=1;
                    $data['message']="上传失败！";
                    outJson($data); 
                }
                $this->shopmasseur->where('id='.$this->masseur_id)->save(array('cover'=>$pic_path));
                if($pic_path){
                    $pic_path = $this->host_url."/Uploads/cover/".date("Y-m-d")."/".$pics;
                }
                unset($data);
                $data['code']=0;
                $data['message']="获取成功！";
                $data['pic']=$pic_path;
                $data['filename']=$filename;
                outJson($data); 
                    
                
            }
        }
        else
        {
            unset($data);
            $data['code']=1;
            $data['message']="请选择上传文件！";
            outJson($data); 
        }
    }

}
?>