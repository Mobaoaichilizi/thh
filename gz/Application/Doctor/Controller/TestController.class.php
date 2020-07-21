<?php
// +----------------------------------------------------------------------
// | 关于监测接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;

class TestController{
	function _initialize() {
		
		parent::_initialize();
	}
	

public function getInformation(){
	$xml = I('post.xml');
	$fun = I('post.fun');
	 // header("Content-type: text/html; charset=utf-8"); 
    //WebService接口数据调用
    $soap=new \SoapClient('http://210.72.80.34:10701/xatu/alpinecloudws?wsdl');    //这里填写你要调用的URL

    $soap->soap_defencoding = 'utf-8';
    $soap->decode_utf8 = false;
    $soap->xml_encoding = 'utf-8';
    $xml = "<?xml version='1.0' encoding='utf-8'?><Request><RequestParameter><CTRL-INFO WEBSVRNAME='获取设备的最后一次连接时间' WEBSVRCODE='CX0008'/><PARAMETERS><DeviceID>Device_0</DeviceID><StartTime>2014-11-01 00:00:00</StartTime><EndTime>2014-11-04 23:59:59</EndTime></PARAMETERS></RequestParameter></Request>";
    $fun =  "GetDeviceLastConnTime";               
    $ParamData = array('arg0'=>$fun,'arg1'=>$xml);  //调用接口用到的参数
       dump($soap);
    $ServiceRestCallByHippotigris = $soap->AlpineCloudFun($ParamData);
    // 接口无法访问，页面直接不跳转, 输出错误信息：用户信息同步异常, 错误码: 404
        if (empty($ServiceRestCallByHippotigris)) {
            echo '用户信息同步异常, 错误码: 404';
            exit();
        }else {
            $xmlResult = simplexml_load_string($ServiceRestCallByHippotigris->return);
            $results = $this->xmlToArr($xmlResult);
            $result = $results['Response']['ResponseParameter'];
            $info = array();
            if($result['RESPONSECODE'] == '000000'){
                $aa = $result['RESULTDATESET']['NODE'];
                foreach ($aa as $key => $value) {
                    foreach ($value as $k => $v) {
                        foreach ($v as $k1 => $v1) {
                            if($k1 == 0){//断面名称
                                $attributes = $v1['attributes'];
                                if($attributes['Name'] == '断面名称'){
                                    $info[] = $attributes['Value'];
                                    
                                }
                               
                            }else if($k1 == 1){//设备名称
                                $attributes = $v1['attributes'];
                                if($attributes['Name'] == '设备名称'){
                                    $info[] = $attributes['Value'];
                                    
                                }
                            }else if($k1 == 2){//最后连接时间
                                $attributes = $v1['attributes'];
                                if($attributes['Name'] == '最后连接时间'){
                                    $info[] = $attributes['Value'];
                                    
                                }
                            }
                            
                        }
                           
                       
                        
                    }
                }
                for($i=0;$i<(count($info)/3);$i++)
                {
                  $bbb[] = array_slice($info, $i * 3 ,3);
                }
                
                $key = array();
                $key[0]="address";
				$key[1]="name";
				$key[2]="time";
                $new_info = array();
                foreach($bbb as $k=>$v) {
                    $new_info[$k] = array_combine($key,$v);
                }
                if($new_info){

				    // $result['content'] = html_entity_decode($result['content']);
					unset($data);
					$data['code'] = 1;
					$data['message'] = "信息加载成功！";
					$data['info'] = $new_info;
					exit(json_encode($data));
				}else{
					unset($data);
					$data['code'] = 0;
					$data['message'] = "暂无信息！";
					exit(json_encode($data));
				}
            }
        }
           
   }
        
   
	
public function xmlToArr($xml, $root = true)
    {

        if(!$xml->children())
        {
            return (string)$xml;
        }
        $array = array();
        foreach($xml->children() as $element => $node)
        {
            $totalElement = count($xml->{$element});
            if(!isset($array[$element]))
            {
                $array[$element] = "";
            }
            // Has attributes
            if($attributes = $node->attributes())
            {
                $data = array('attributes' => array(), 'value' => (count($node) > 0) ? $this->xmlToArr($node, false) : (string)$node);
                foreach($attributes as $attr => $value)
                {
                    $data['attributes'][$attr] = (string)$value;
                }
                if($totalElement > 1)
                {
                    $array[$element][] = $data;
                }
                else
                {
                    $array[$element] = $data;
                }
                // Just a value
            }
            else
            {
                if($totalElement > 1)
                {
                    $array[$element][] = $this->xmlToArr($node, false);
                }
                else
                {
                    $array[$element] = $this->xmlToArr($node, false);
                }
            }
        }
        if($root)
        {
            return array($xml->getName() => $array);
        }
        else
        {
            return $array;
        }

    }

	
}
?>