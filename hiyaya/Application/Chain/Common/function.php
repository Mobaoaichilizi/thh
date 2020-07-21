<?php
// +----------------------------------------------------------------------
// | 公共函数
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
function myCalender($year = '', $month = '', $timezone = 'Asia/Shanghai')
{

 date_default_timezone_set ( $timezone );
 $year = abs ( intval ( $year ) );
 $month = abs ( intval ( $month ) );

 $nowDate = new DateTime();

 if ($year <= 0)
 {
  $year = $nowDate->format( 'Y' );
 }

 if ($month <= 0 or $month > 12)
 {
  $month = $nowDate->format('m');
 }

 //上一年
 $pretYear = $year - 1;
 //上一月
 $mpYear = $year;
 $preMonth = $month - 1;
 if ($preMonth <= 0)
 {
  $preMonth = 1;
  $mpYear = $pretYear;
 }

 //下一年
 $nextYear = $year + 1;
 //下一月
 $mnYear = $year;
 $nextMonth = $month + 1;
 if ($nextMonth > 12)
 {
  $nextMonth = 1;
  $mnYear = $nextYear;
 }

 //日历头
 $html = '';

 $currentDay = $nowDate->format('Y-m-j' );

 //当月最后一天
 $creatDate = new DateTime("$year-$nextMonth-0");
 $lastday = $creatDate->format('j');
 $creatDate = NULL;

 //循环输出天数
 $day = 1;
 $line = '';
 while ( $day <= $lastday )
 {
  $cday = $year . '-' . $month . '-' . $day;

  //当前星期几
  $creatDate = new DateTime("$year-$month-$day");
  $nowWeek = $creatDate->format('N');
  $creatDate = NULL;

  if ($day == 1)
  {
   $line = '<tr align="center">';
   $line .= str_repeat ( '<td> </td>', $nowWeek - 1 );
  }
  if(strtotime($cday) < strtotime($currentDay))
  {
	  $line .= "<td class='day before'><span class='date'>$day</span></td>";
  }else{
  unset($resultday);
  $referredlist=M('Referred')->order("sort asc")->select();
  foreach($referredlist as $row)
  {
	$ticketprice=M('ticketprice')->where("typeid=".$row['id']." and todaytime=".strtotime($cday))->find();
	if($ticketprice)
	{
	$resultday.='<p class="price">'.$row['name'].'：<span>￥'.$ticketprice['present_price'].'</span></p>';
	}
  }
  if ($cday == $currentDay)
  {
	$line .= "<td class='day today'><span class='date_today'>今天</span>".$resultday."</td>";
  } else
  {
	
	$line .= '<td class="day after"><a href="javascript:void(0)" onclick="show_date_price('.strtotime($cday).');"><span class="date">'.$day.'</span>'.$resultday.'</a></td>';
  }
  }

  

  //一周结束
  if ($nowWeek == 7)
  {
   $line .= '</tr>';
   $html .= $line;
   $line = '<tr align="center">';
  }
  //全月结束
  if ($day == $lastday)
  {
   if ($nowWeek != 7)
   {
    $line .= str_repeat ( '<td> </td>', 7 - $nowWeek );
   }
   $line .= '</tr>';
   $html .= $line;

   break;
  }
  $day ++;
 }

 $html .= '';
 return $html;
}

/**
 * 记录管理员的操作内容
 *
 * @access  public
 * @param   string      $action     操作的类型
 * @param   string      $content    操作的内容
 * @return  void
 */
function admin_log($action,$type,$content)
{
    $log_info = array(
		"add" => "新增",
		"edit" => "编辑",
		"del" => "删除",
		"cancel" => "取消"
	);
	$log_type = array(
		"user" => "用户",
		"role" => "用户组",
		"menu" => "后台菜单",
		"ticket" => "票务",
		"ticketorder" => "票务订单",
		"goods" => "商品",
		"goodsorder" => "商品订单",
		"goodscate" => "商品分类",
		"article" => "文章",
		"articlecate" => "文章分类"
	);
	$datalog=array(
		'user_id' => session('admin_id'),
		'type' => $action,
		'createtime' => time(),
		'remark'=> $log_info[$action].$log_type[$type].':'.$content,
		'ip_address' => get_client_ip(),
	);
	M('log')->add($datalog);
}
/**

 * 格式化字节大小

 * @param  number $size      字节数

 * @param  string $delimiter 数字和单位分隔符

 * @return string            格式化后的带单位的大小


 */

function format_bytes($size, $delimiter = '') {

    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;

    return round($size, 2) . $delimiter . $units[$i];

}
//导入excel
function import_excel($file){
    // 判断文件是什么格式
    $type = pathinfo($file); 
    $type = strtolower($type["extension"]);
    if ($type=='xlsx') { 
        $type='Excel2007'; 
    }elseif($type=='xls') { 
        $type = 'Excel5'; 
    } 
    ini_set('max_execution_time', '0');
    Vendor('PHPExcel.PHPExcel');
    // 判断使用哪种格式
    $objReader = PHPExcel_IOFactory::createReader($type);
    $objPHPExcel = $objReader->load($file); 
    $sheet = $objPHPExcel->getSheet(0); 
    // 取得总行数 
    $highestRow = $sheet->getHighestRow();     
    // 取得总列数      
    $highestColumn = $sheet->getHighestColumn(); 
    //循环读取excel文件,读取一条,插入一条
    $data=array();
    //从第一行开始读取数据
    for($i=2;$i<=$highestRow;$i++)
	{   
		$data['name'] = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
		$data['phone'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
		$data['card']= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
		$data['main_meal']= $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
		$data['models']= $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
		$data['net_age']= $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
		$data['card_number']= $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
		$data['service_time']= strtotime($objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue());
		//$data['service_time']= strtotime($data['service_time']);
		$data['address']= $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
		$data['deputy_card']= $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
		$data['area']= $objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
		$data['note']= $objPHPExcel->getActiveSheet()->getCell("L".$i)->getValue();
		unset($json);
		$json=addresstolatlag('陕西省西安市'.$data['address']);
		$json=json_decode($json);
		$data['lng']=$json->result->location->lng;
		$data['lat']=$json->result->location->lat;
		
		$data['createtime']=time();            
		M('Repair')->add($data);
	} 
}



//导入已分单excel

function import_excel_complete($file){

    // 判断文件是什么格式

    $type = pathinfo($file); 

    $type = strtolower($type["extension"]);

    if ($type=='xlsx') { 

        $type='Excel2007'; 

    }elseif($type=='xls') { 

        $type = 'Excel5'; 

    } 

    ini_set('max_execution_time', '0');

    Vendor('PHPExcel.PHPExcel');

    // 判断使用哪种格式

    $objReader = PHPExcel_IOFactory::createReader($type);

    $objPHPExcel = $objReader->load($file); 

    $sheet = $objPHPExcel->getSheet(0); 

    // 取得总行数 

    $highestRow = $sheet->getHighestRow();     

    // 取得总列数      

    $highestColumn = $sheet->getHighestColumn(); 

    //循环读取excel文件,读取一条,插入一条

    $data=array();

    //从第一行开始读取数据

    for($i=2;$i<=$highestRow;$i++)

	{   

		$data['name'] = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();

		$data['phone'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();

		$data['card']= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();

		$data['main_meal']= $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();

		$data['models']= $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();

		$data['net_age']= $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();

		$data['card_number']= $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();

		$data['service_time']= strtotime($objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue());

		//$data['service_time']= strtotime($data['service_time']);

		$data['address']= $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();

		$data['deputy_card']= $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();

		$data['area']= $objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();

		$data['note']= $objPHPExcel->getActiveSheet()->getCell("L".$i)->getValue();
		
		$data['state']=2;

		unset($json);

		$json=addresstolatlag('陕西省西安市'.$data['address']);

		$json=json_decode($json);

		$data['lng']=$json->result->location->lng;

		$data['lat']=$json->result->location->lat;

		

		$data['createtime']=time();            
		unset($res_member);
		unset($res_id);
		$res_member=M('Member')->where("username='".$objPHPExcel->getActiveSheet()->getCell("M".$i)->getValue()."'")->find();
		$res_id=M('Repair')->add($data);
		unset($data_single);
		$data_single['member_id']=$res_member['id'];
		$data_single['repair_id']=$res_id;
		$data_single['single_time']=time();
		$data_single['shopid']=$res_member['shopid'];
		M('Single')->add($data_single);
	} 

}

//导出excel
function create_xls($data,$filename='simple.xls'){
    ini_set('max_execution_time', '0');
    Vendor('PHPExcel.PHPExcel');
    $filename=str_replace('.xls', '', $filename).'.xls';
    $phpexcel = new PHPExcel();
    $phpexcel->getProperties()
        ->setCreator("上门数据")
        ->setLastModifiedBy("上门数据")
        ->setTitle($filename)
        ->setSubject($filename);
    $phpexcel->getActiveSheet()->fromArray($data);
    $phpexcel->getActiveSheet()->setTitle('Sheet1');
    $phpexcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=$filename");
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0
    $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
    $objwriter->save('php://output');
    exit;
}
//传递数据以易于阅读的样式格式化后输出
function p($data){
    // 定义样式
    $str='<pre style="display: block;padding: 9.5px;margin: 44px 0 0 0;font-size: 13px;line-height: 1.42857;color: #333;word-break: break-all;word-wrap: break-word;background-color: #F5F5F5;border: 1px solid #CCC;border-radius: 4px;">';
    // 如果是boolean或者null直接显示文字；否则print
    if (is_bool($data)) {
        $show_data=$data ? 'true' : 'false';
    }elseif (is_null($data)) {
        $show_data='null';
    }else{
        $show_data=print_r($data,true);
    }
    $str.=$show_data;
    $str.='</pre>';
    echo $str;
}
//地址转换成经纬度
function addresstolatlag($address){  
  
$url='http://api.map.baidu.com/geocoder/v2/?address='.$address.'&output=json&ak=NDQQsSuHpSOGM1CfFC25vwUexPx4INil';  
if($result=file_get_contents($url))  
{  
	$res= explode(',"lat":', substr($result, 40,36));  
	return  $result;
}  
}

//判断点是否在一个任意多边形内部
function isPointInPolygon($polygon,$lnglat){
    $count = count($polygon);

    $px = $lnglat['lng'];
    $py = $lnglat['lat'];
    $flag = 0;
    for ($i = 0, $j = $count - 1; $i < $count; $j = $i, $i++) { 
        $sy = $polygon[$i]['lng'];
        $sx = $polygon[$i]['lat'];
        $ty = $polygon[$j]['lng'];
        $tx = $polygon[$j]['lat'];
		print_r($sy);
        if ($px == $sx && $py == $sy || $px == $tx && $py == $ty)
            return 1;
        if ($sy < $py && $ty >= $py || $sy >= $py && $ty < $py) {
            $x = $sx + ($py - $sy) * ($tx - $sx) / ($ty - $sy);
            if ($x == $px)
                return 1;
            if ($x > $px)
                $flag = 1;
        }
    }
    return $flag;
}
function is_point_in_polygon($point, $pts) {
    $N = count($pts);
    $boundOrVertex = true; //如果点位于多边形的顶点或边上，也算做点在多边形内，直接返回true
    $intersectCount = 0;//cross points count of x 
    $precision = 2e-10; //浮点类型计算时候与0比较时候的容差
    $p1 = 0;//neighbour bound vertices
    $p2 = 0;
    $p = $point; //测试点
 
    $p1 = $pts[0];//left vertex        
    for ($i = 1; $i <= $N; ++$i) {//check all rays
        // dump($p1);
        if ($p['lng'] == $p1['lng'] && $p['lat'] == $p1['lat']) {
            return $boundOrVertex;//p is an vertex
        }
         
        $p2 = $pts[$i % $N];//right vertex            
        if ($p['lat'] < min($p1['lat'], $p2['lat']) || $p['lat'] > max($p1['lat'], $p2['lat'])) {//ray is outside of our interests
            $p1 = $p2; 
            continue;//next ray left point
        }
         
        if ($p['lat'] > min($p1['lat'], $p2['lat']) && $p['lat'] < max($p1['lat'], $p2['lat'])) {//ray is crossing over by the algorithm (common part of)
            if($p['lng'] <= max($p1['lng'], $p2['lng'])){//x is before of ray
                if ($p1['lat'] == $p2['lat'] && $p['lng'] >= min($p1['lng'], $p2['lng'])) {//overlies on a horizontal ray
                    return $boundOrVertex;
                }
                 
                if ($p1['lng'] == $p2['lng']) {//ray is vertical                        
                    if ($p1['lng'] == $p['lng']) {//overlies on a vertical ray
                        return $boundOrVertex;
                    } else {//before ray
                        ++$intersectCount;
                    }
                } else {//cross point on the left side
                    $xinters = ($p['lat'] - $p1['lat']) * ($p2['lng'] - $p1['lng']) / ($p2['lat'] - $p1['lat']) + $p1['lng'];//cross point of lng
                    if (abs($p['lng'] - $xinters) < $precision) {//overlies on a ray
                        return $boundOrVertex;
                    }
                     
                    if ($p['lng'] < $xinters) {//before ray
                        ++$intersectCount;
                    } 
                }
            }
        } else {//special case when ray is crossing through the vertex
            if ($p['lat'] == $p2['lat'] && $p['lng'] <= $p2['lng']) {//p crossing over p2
                $p3 = $pts[($i+1) % $N]; //next vertex
                if ($p['lat'] >= min($p1['lat'], $p3['lat']) && $p['lat'] <= max($p1['lat'], $p3['lat'])) { //p.lat lies between p1.lat & p3.lat
                    ++$intersectCount;
                } else {
                    $intersectCount += 2;
                }
            }
        }
        $p1 = $p2;//next ray left point
    }
 
    if ($intersectCount % 2 == 0) {//偶数在多边形外
        return false;
    } else { //奇数在多边形内
        return true;
    }
}

function outJson($result) {

// 返回JSON数据格式到客户端 包含状态信息

    header('Content-Type:text/html; charset=utf-8');

	//exit(print_r($result));

    exit(json_encode($result));

}

?>