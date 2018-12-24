<?php
namespace Api\Model;
use Think\Model;
class AccountModel extends Model
{
    protected $tableName = 'shop_user';
    public function existaccount($account)
    {
		$count = M('ShopUser')->where('username="'.$account.'"')->count();
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

