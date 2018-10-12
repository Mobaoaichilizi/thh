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
		"articlecate" => "文章分类",
		"shopcate" => "景点分类",
    "setting" => "分类参数",
    "department" => "科室",
    "hospital" => "医院",
    "advice" => "建议",
    "information" => "信息"
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

/*
地区名称调用
*/
function city_name($id)
{
	$cityname=M('District')->where('id='.$id)->getField('name');
	return $cityname;
}




/**
     +----------------------------------------------------------
     * Export Excel | 2013.08.23
     * Author:HongPing <hongping626@qq.com>
     +----------------------------------------------------------
     * @param $expTitle     string File name
     +----------------------------------------------------------
     * @param $expCellName  array  Column name
     +----------------------------------------------------------
     * @param $expTableData array  Table data
     +----------------------------------------------------------
     */
 function exportExcel($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        //$fileName = $_SESSION['loginAccount'].date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $fileName = $xlsTitle.date('_YmdHis');
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        // vendor("PHPExcel.PHPExcel");
        require_once VENDOR_PATH."PHPExcel/Classes/PHPExcel.php";
        $objPHPExcel = new PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));  
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]); 
        } 
          // Miscellaneous glyphs, UTF-8   
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
          }             
        }  
        
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
        $objWriter->save('php://output'); 
        exit;   
    }
     
    /**
     +----------------------------------------------------------
     * Import Excel | 2013.08.23
     * Author:HongPing <hongping626@qq.com>
     +----------------------------------------------------------
     * @param  $file   upload file $_FILES
     +----------------------------------------------------------
     * @return array   array("error","message")
     +----------------------------------------------------------     
     */   
function importExecl($file){ 
        if(!file_exists($file)){ 
            return array("error"=>0,'message'=>'file not found!');
        } 
        Vendor("PHPExcel.PHPExcel.IOFactory"); 
        $objReader = PHPExcel_IOFactory::createReader('Excel5'); 
        try{
            $PHPReader = $objReader->load($file);
        }catch(Exception $e){}
        if(!isset($PHPReader)) return array("error"=>0,'message'=>'read error!');
        $allWorksheets = $PHPReader->getAllSheets();
        $i = 0;
        foreach($allWorksheets as $objWorksheet){
            $sheetname=$objWorksheet->getTitle();
            $allRow = $objWorksheet->getHighestRow();//how many rows
            $highestColumn = $objWorksheet->getHighestColumn();//how many columns
            $allColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);
            $array[$i]["Title"] = $sheetname; 
            $array[$i]["Cols"] = $allColumn; 
            $array[$i]["Rows"] = $allRow; 
            $arr = array();
            $isMergeCell = array();
            foreach ($objWorksheet->getMergeCells() as $cells) {//merge cells
                foreach (PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
                    $isMergeCell[$cellReference] = true;
                }
            }
            for($currentRow = 1 ;$currentRow<=$allRow;$currentRow++){ 
                $row = array(); 
                for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++){;                
                    $cell =$objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
                    $afCol = PHPExcel_Cell::stringFromColumnIndex($currentColumn+1);
                    $bfCol = PHPExcel_Cell::stringFromColumnIndex($currentColumn-1);
                    $col = PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                    $address = $col.$currentRow;
                    $value = $objWorksheet->getCell($address)->getValue();
                    if(substr($value,0,1)=='='){
                        return array("error"=>0,'message'=>'can not use the formula!');
                        exit;
                    }
                    if($cell->getDataType()==PHPExcel_Cell_DataType::TYPE_NUMERIC){
                        $cellstyleformat=$cell->getParent()->getStyle( $cell->getCoordinate() )->getNumberFormat();
                        $formatcode=$cellstyleformat->getFormatCode();
                        if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
                            $value=gmdate("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($value));
                        }else{
                            $value=PHPExcel_Style_NumberFormat::toFormattedString($value,$formatcode);
                        }                
                    }
                    if($isMergeCell[$col.$currentRow]&&$isMergeCell[$afCol.$currentRow]&&!empty($value)){
                        $temp = $value;
                    }elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$col.($currentRow-1)]&&empty($value)){
                        $value=$arr[$currentRow-1][$currentColumn];
                    }elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$bfCol.$currentRow]&&empty($value)){
                        $value=$temp;
                    }
                    $row[$currentColumn] = $value; 
                } 
                $arr[$currentRow] = $row; 
            } 
            $array[$i]["Content"] = $arr; 
            $i++;
        } 
        spl_autoload_register(array('Think','autoload'));//must, resolve ThinkPHP and PHPExcel conflicts
        unset($objWorksheet); 
        unset($PHPReader); 
        unset($PHPExcel); 
        unlink($file); 
        return array("error"=>1,"data"=>$array); 
    }

?>