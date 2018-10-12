<?php 
namespace Think;
class Shopcar  
{ 
//商品列表 
public  $productList=array(); 
 /** 
 *  
 * @param unknown_type $product 传进来的商品 
 * @return true 购物车里面没有该商品  
 */
public function checkProduct($product) 
{ 
	
      
    for($i=0;$i<count($this->productList);$i++ ) 
    { 
      
     if($this->productList[$i]['id']==$product['id'])    
     return $i; 
    } 
      
    return -1; 
  
} 
 //添加到购物车 
public function add($product)  
{ 
    $i=$this->checkProduct($product); 
    if($i==-1) 
    array_push($this->productList,$product); 
    else
	{
    $this->productList[$i]['count']+=$product['count']; 
	}
} 
//修改数量
 public function upd($product,$shuliang)  
{   
    $i=$this->checkProduct($product); 
    if($i==-1) 
    array_push($this->productList,$product); 
    else if($shuliang > 0)
    $this->productList[$i]['count']=$shuliang;	
} 
//修改规格

//删除 
public function delete($product) 
{ 
	
    $i=$this->checkProduct($product); 
	//print_r($this->productList);
    if($i!=-1) 
    array_splice($this->productList,$i,1);    
	//print_r($this->productList);
} 
  
//返回所有的商品的信息 
public function show() 
{ 
  return $this->productList; 
} 
  
  
} 

