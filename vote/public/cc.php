<?php
/*
echo time();
echo "<br >";
echo md5('5e29f483c48848'.time());
echo "<br >";
echo date("ymdHis").substr(base_convert(md5(uniqid(md5(microtime(true)), true)), 16, 10), 0, 5).rand(1000,9999).chr(rand(65, 90)) . chr(rand(65, 90));

echo trim(" 2222  ");


echo strtotime("+3years",time());


$str1 = "143,40,161,161,161,265,265,264,235,235,235,235,235,235,68,268,266,266,202,202,202,202,217,265,267,214,214,140,103,103,103,103,75,195,268,238,214,214,32,32,32,32,32,32";
$str2 = "1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,3,1,1,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1";

$str1_array = explode(',',$str1);
$str2_array = explode(',',$str2);

$str_count = array();

$str1_array_unique = array_unique($str1_array);
foreach($str1_array_unique as $k=>$row)
{
	foreach($str1_array as $key=>$val)
	{
		if($row == $val)
		{
			$str_count[$row]+=$str2_array[$key];
		}
	}
}
print_r($str_count);
*/
echo md5('123456');
?>