<?php
// +----------------------------------------------------------------------
// | 用户管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2020 All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use think\Controller;
use think\Upload;

class File extends Controller
{

    public function index(){
        $id = input('get.id');
        $this->assign('id',$id);
        return $this->fetch();
    }
    public function uploadPicture()
    {
        if($this->request->has('image', 'file')){
            $files = $this->request->file('image');
        }else if($this->request->has('file', 'file')){
            $files = $this->request->file('file');
        }else{
            return false;
        }
	

        $info = $files->move('./Uploads/Picture');
        if($info){
            $arr = [
                'code' => 1,
                'msg' => '上传成功',
                'url' => '/Uploads/Picture/'.str_replace('\\', '/', $info->getSaveName())
            ];
        }else{
            // 上传失败获取错误信息
            $arr = [
                'code' => -1,
                'msg' => '上传失败'
            ];
        }
        outJson($arr);
    }


}
