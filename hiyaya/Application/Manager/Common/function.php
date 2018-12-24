<?php
// +----------------------------------------------------------------------
// | 函数文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
//返回josn格式
function outJson($result) {
// 返回JSON数据格式到客户端 包含状态信息
    header('Content-Type:text/html; charset=utf-8');
	//exit(print_r($result));
    exit(json_encode($result));
}

/**

 * 字符串截取，支持中文和其他编码

 * @static

 * @access public

 * @param string $str 需要转换的字符串

 * @param string $start 开始位置

 * @param string $length 截取长度

 * @param string $charset 编码格式

 * @param string $suffix 截断显示字符

 * @return string

 */

function mssubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {

    if(function_exists("mb_substr"))

        $slice = mb_substr($str, $start, $length, $charset);

    elseif(function_exists('iconv_substr')) {

        $slice = iconv_substr($str,$start,$length,$charset);

        if(false === $slice) {

            $slice = '';

        }

    }else{

        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";

        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";

        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";

        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";

        preg_match_all($re[$charset], $str, $match);

        $slice = join("",array_slice($match[0], $start, $length));

    }

    return $suffix ? $slice.'' : $slice;

}

//技师级别

function masseur_level($id)
{
	$result=M("MasseurCategory")->where("id=".$id)->find();
	if($result)
	{
		return $result['category_name'];
	}else
	{
		return '';
	}
}

function unique($where,$name,$parm,$table)
{
    $where.=" AND $name='".$parm."'";
    $count=$table->where($where)->count();
    if($count>0)
    {
       return false;
        exit;
    }
    else
    {
        return true;
    }
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

        require_once VENDOR_PATH."PHPExcel/PHPExcel.php";

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
