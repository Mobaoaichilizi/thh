<?php

namespace Home\Controller;
use Common\Controller\HomebaseController;
class IndexController extends HomebaseController {
	public function _initialize() {
		
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		
		//$this->product = M('Goods');
		//$this->ordergoods = D("OrderGoods");
		//$this->order = D("Order");

		//购物车

	}

    public function index0(){
			//$res=sendMail('415199201@qq.com','肥肥','肥肥','内容');
			//print_r($res);
			//$result=D("Product")->where("id=18")->find();
			//$result['process']=html_entity_decode($result['process']);
			//$this->assign('result',$result);
			//echo date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
			/*
			$msg = array('first' => array('value' => "您的订单已提交成功！", "color" => "#4a5077"), 'keyword1' => array('title' => '店铺', 'value' => '肥肥之家', "color" => "#4a5077"), 'keyword2' => array('title' => '下单时间', 'value' => date('Y-m-d H:i:s'), "color" => "#4a5077"), 'keyword3' => array('title' => '商品', 'value' => '小碗熊', "color" => "#4a5077"), 'keyword4' => array('title' => '金额', 'value' => '50元', "color" => "#4a5077"), 'remark' => array('value' => "\r\n您的订单我们已经收到，支付后我们将尽快配送~~", "color" => "#4a5077"));
			if (is_array($msg)) {
			foreach ($msg as $key => $value) {
				if (!empty($value['title'])) {
					$content .= $value['title'] . ":" . $value['value'] . "\n";
				} else {
					$content .= $value['value'] . "\n";
					if ($key == 0) {
						$content .= "\n";
					}
				}
			}
			$content .= "<a href='http://www.xadade.com'>点击查看详情</a>";
			}
			$datas=array("touser" => $openid, "msgtype" => "text", "text" => array('content' => urlencode($content)));
			//print_r($datas);
			$map    = array('status' => 1);
			$data   = M('Config')->where($map)->field('type,name,value')->select();
			$config = array();
			if($data && is_array($data)){	
				foreach ($data as $value) {
						$config[$value['name']] = $value['value'];
				}
			}
			
			//echo rand(1000,9999).rand(1000,9999).rand(1000,9999);
			C($config); //添加配置
			//echo get_client_ip();
			/*
			$db = M();
			$list=$db->query('SHOW TABLE STATUS');
			$list  = array_map('array_change_key_case', $list);
			print_r($list);
			
			$db = M();
			$table='lgq_about';
			$start=0;
			$result = $db->query("SELECT * FROM `{$table}` LIMIT {$start}, 1000");
			
            foreach ($result as $row) {
                $row = array_map('mysql_escape_string', $row);
				$cc=implode("', '", $row);
			
                $sql = "INSERT INTO `{$table}` VALUES ('" . implode("', '", $row) . "');\n";
                echo $sql;
            }
			
			
			$msg="您有一个提问需要回答\r\n<a href='http://www.xadade.com'>点击查看详情</a>";
			//$datas=json_encode(array("touser" => "otJCajp-4vPHV9mjhW2n-RxDTRUE", "msgtype" => "text", "text" => array("content" => $msg)));
			unset($data);
			//$data=array();
			$data['touser'] = "otJCajp-4vPHV9mjhW2n-RxDTRUE";
			$data['msgtype'] = 'text';
			$data['text']['content'] = urlencode($msg);
			$dat = json_encode($data);
			$dat = urldecode($dat);
			

			
			
			$access_token=get_access_token('wxd2248229f5923554','ce350bb123e0c688d7536d0762924313');
			$url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token->access_token;
			//$result = curlPost($url,$dat);
			echo $link='http://'.$_SERVER['HTTP_HOST'].U("Mylable/problem");
			print_r($result);
			*/
			$rand=get_rand_number(100000000,999999999,1);
			print_r($rand[0]);
			$this->display();
	}
	public function index(){
		$result = M('Information')->where('setting_id=218')->find();
		if($result){
			$result['content'] = html_entity_decode($result['content']);
			$this->assign('result', $result); 

		}
		$this->display();
	}
	//列表页面
	public function products() {



		$count = $this->product->count();

		$this->assign('count', $count); 

		//排序方式

        	

		$products = $this->product->order("sort ASC")->limit('0, 5')->select();

		$this->assign('products', $products);


		$this->display();

	}
	
		//下拉刷新

	public function ajaxProducts(){


		$page = isset($_POST['page']) && intval($_POST['page'])>1 ? intval($_POST['page']) : 2;

		$pageSize = isset($_POST['pagesize']) && intval($_POST['pagesize']) > 1 ? intval($_POST['pagesize']) : 5;

		$start=($page-1) * $pageSize;

		$products = $this->product->order("sort ASC")->limit($start . ',' . $pageSize)->select();

		exit(json_encode(array('products' => $products)));

		//$this->show($str);

	}
	//产品详情页	

	public function product() {

		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

		$where = array('id' => $id);

		$product = $this->product->where($where)->find();
		
		$product['process']=html_entity_decode($product['process']);
		
		$this->assign('product', $product);

		$this->display();

	}
	
	/**

	 * 添加购物车

	 */

	public function addProductToCart() {

		$count = isset($_GET['count']) ? intval($_GET['count']) : 1;

		$id = intval($_GET['id']);
		$product=$this->product->field('id,title,img_thumb,price,original_price')->where("id='$id'")->find();
		$product['count']=$count;
		if(isset($_SESSION['shopcar']))
		$shopcar=unserialize($_SESSION['shopcar']); 
		else
		$shopcar=new \Think\Shopcar(); 
		$shopcar->add($product);
		$_SESSION['shopcar']=serialize($shopcar);

	}
	
	public function cart(){
        
		$shopcar=unserialize($_SESSION['shopcar']); 
		
        $list=$shopcar->productList; 
		

        
		$this->assign('products', $list);

		$this->assign('totalFee', $totalFee);

		$this->assign('totalCount', $totalCount);

		$this->assign('metaTitle','购物车');

		$this->display();

	}
	
	 //删除购物车里面的商品
	

	public function deleteCart(){

        $id = intval($_GET['id']);
		
		$shopcar=unserialize($_SESSION['shopcar']); 
		$product=$this->product->where("id='$id'")->find();
		$shopcar->delete($product);
		$_SESSION['shopcar']=serialize($shopcar);
		header('Location:'.U('Index/cart'));

	}

	
//更新购物车的数量
   public function ajaxUpdateCart()
   {
	    $id = intval($_GET['id']);
		$shuliang=intval($_GET['count']);
		$shopcar=unserialize($_SESSION['shopcar']); 
		$product=$this->product->where("id='$id'")->find();
		$shopcar->upd($product,$shuliang);
		$_SESSION['shopcar']=serialize($shopcar);
		header('Location:'.U('Index/cart'));
   }
   
   
   public function orderCart() {

		
        $shopcar=unserialize($_SESSION['shopcar']); 
        $list=$shopcar->productList;
		
		foreach ($list as $key => $row) 
		{
			$total+=$row['count']*$row['price'];
			$totalcount+=$row['count'];
			$totalprice+=$row['count']*$row['price'];
		}
		
		$member=D('member');
		

		$this->assign('total',$total);
		$this->assign('totalcount',$totalcount);

		$this->assign('totalprice',$totalprice);
		
		$this->assign('products', $list);

		$this->assign('metaTitle', '购物车结算');

		$this->display();

	}
	
	public function ordersave() {
		$member_id = 8; //用户ID
		$order_price = I("post.order_price"); //价格
		$name = I("post.name"); //收货人姓名
		$phone = I("post.phone"); //收货人电话
		$address = I("post.address"); //收货地址
		
		$addorder=array(
			'order_sn' => date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8),
			'member_id' => $member_id,
			'order_price' => $order_price,
			'name' => $name,
			'phone' => $phone,
			'address' => $address,
			'createtime' => time(),
		);
		$result=$this->order->add($addorder);
		if($result!==false)
		{
			$shopcar=unserialize($_SESSION['shopcar']); 
			$carts=$shopcar->productList;
			if ($carts){
				$order_id=$this->order->getLastInsID();
				foreach ($carts as $key => $rowset) {
					$data['order_id']=$order_id;
					$data['member_id']=$member_id;
					$data['goods_id']=$rowset['id'];
					$data['goods_name']=$rowset['title'];
					$data['number']=$rowset['count'];
					$data['price']=$rowset['price'];
					$data['goods_img']=$rowset['img_thumb'];
					$result=$this->ordergoods->add($data);
					if($result===false)
					{
						$this->error('生成订单失败！');
					}
					$temp = $this->product->where('id='.$rowset['id'])->find();
					$datt['stock']=$temp['stock']-$rowset['count'];
					$datt['sales']=$temp['sales']+$rowset['count'];
					$res=$this->product->where('id='.$rowset['id'])->save($datt);
					if($res==false)
					{
						$this->error('减库存失败！');
					}
				}
				$this->success('正在提交中...', U('Index/products'));
			}else
			{
				$this->error('购物车不能为空！');
			}
		}
		
		
	}
   


}