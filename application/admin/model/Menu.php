<?php
/**
 *
 * @author: love_fat<45199201@qq.com>
 * @day: 2019/08/15
 */
namespace app\admin\model;
use think\Model;
use think\Db;
use think\Tree;
class Menu extends Model
{
	 
	 public static function getMenuTree($parentid=0)
	 {
		//$menu= new Menu;
		$result = self::order(array("listorder" => "ASC"))->select();
        $tree = new Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        foreach ($result as $n=> $r) {
            $result[$n]['app']=$r['app']."/".$r['model']."/".$r['action'];
        }
        $tree->init($result);
		$str=array();
        $categorys = $tree->get_child($parentid);
		foreach($categorys as $row)
		{
			$str[]=$row;
			if($tree->get_child($row['id']))
			{
				foreach($tree->get_child($row['id']) as $val)
				{
					$val['name']='&nbsp;&nbsp;&nbsp;├─ '.$val['name'];
					$str[]=$val;
					if($tree->get_child($val['id']))
					{
						foreach($tree->get_child($val['id']) as $valt)
						{
							$valt['name']='&nbsp;&nbsp;&nbsp;├─├─'.$valt['name'];
							$str[]=$valt;
							if($tree->get_child($valt['id']))
							{
									foreach($tree->get_child($valt['id']) as $valtt)
									{
										$valtt['name']='&nbsp;&nbsp;&nbsp;├─├─├─'.$valtt['name'];
										$str[]=$valtt;
									}
							}
						}
					}		
				}
			}	
		} 
		return $str;
	 }
	/**
     * 根据菜单id获取菜单信息
     * @param $id
     */
    public static function getOneMenu($id)
    {
        $model = new self;
        return $model->where('id', $id)->find();
    }
	/**
     * 插入菜单信息
     * @param $param
     */ 
	public static function insertMenu($param)
	{
		$model = new self;
		$result =  $model->validate('Menu')->save($param);
		if(false === $result){
			// 验证失败 输出错误信息
			return msg(0, '', $model->getError());
		}else{
			return msg(1,$result,'获取成功！');
		}
	}
	/**
     * 编辑菜单信息
     * @param $param
     */ 
	public static function updateMenu($param)
	{
		$model = new self;
		$result =  $model->validate('Menu')->save($param, ['id' => $param['id']]);
		if(false === $result){
			// 验证失败 输出错误信息
			return msg(0, '', $model->getError());
		}else{
			return msg(1,$param['id'],'获取成功！');
		}
	}
}

?>