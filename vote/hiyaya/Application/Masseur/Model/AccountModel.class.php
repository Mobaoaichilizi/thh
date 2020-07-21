<?php
namespace Masseur\Model;
use Think\Model;
class AccountModel extends Model
{
    protected $tableName = 'shop_masseur';
    public function existaccount($account)
    {
		$count = M('ShopMasseur')->where('tel="'.$account.'"')->count();
		if($count > 0)
		{
            return true;
		}
		else
        {
            return false;
		}
	}
}

