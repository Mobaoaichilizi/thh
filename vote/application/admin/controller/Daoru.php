<?php

// +----------------------------------------------------------------------

// | 数据导入文件

// +----------------------------------------------------------------------

// | Copyright (c) 2017-2020 All rights reserved.

// +----------------------------------------------------------------------

// | Author: thh <752106024@qq.com>

// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\common\controller\AdminBasic;

use think\Db;

use think\Url;



class Daoru extends AdminBasic

{

    /**

     * 获取数据

     */

    public function index(){

        if($this->request->isPost())

        {
            $number = input('number');
            $teacherLists = Db::name("Teacher")->order("sort asc,id desc")->select();
            for ($i=0; $i < $number; $i++) { 
                foreach ($teacherLists as $key => $value) {
                    $res = array(

                        'ip' => '127.0.0.1',
        
                        'student_id' => '123456',
        
                        'teacher_id' => $value['id'],
        
                        'votetime' => time(),
        
                    );
                    $result = Db::name("Vote")->insert($res);
                } 
            }
            

            if($result!==false)

            {

                $this->success("投票成功！", Url::build("Daoru/teacher"));

            }else

            {

                $this->error($result['msg']);

            }

        }else

        {

            return $this->fetch();

        }

    }
     /**

     * 获取数据

     */

    public function student(){

        if($this->request->isPost())

        {

            $param = input('post.');


           // $result=ConfigModel::updateConfig($param);

            if($result['code']==1)

            {

                $this->success("更新成功！", Url::build("Daoru/student"));

            }else

            {

                $this->error($result['msg']);

            }

        }else

        {

            return $this->fetch();

        }

    }





}

