<?php



// +----------------------------------------------------------------------



// | 文件上传配置



// +----------------------------------------------------------------------



// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.



// +----------------------------------------------------------------------



// | Author: Liuguoqiang <415199201@qq.com>



// +----------------------------------------------------------------------



namespace Chain\Controller;



use Common\Controller\ChainbaseController;



use Think\Upload;



class FileController extends ChainbaseController {



	



	public function uploadPicture()



	{



		$return  = array('status' => 1, 'info' => '上传成功', 'data' => '');



		



		$pic_driver = C('PICTURE_UPLOAD_DRIVER');



		$setting=C('PICTURE_UPLOAD');



		



		$Upload = new Upload($setting,$pic_driver, $config = null);



		



		$info   = $Upload->upload($_FILES);



		 if($info){



			foreach ($info as $key => &$value) {



                /* 记录文件信息 */







                $value['path'] = substr($setting['rootPath'], 1).$value['savepath'].$value['savename'];	//在模板里的url路径



            }



            $return['data'] = $info['file'];



            $return['info'] = $info['file']['name'];



        } else {



            $return['status'] = 0;



            $return['info']   = $Upload->getError();



        }







        /* 返回JSON数据 */







        $this->ajaxReturn($return);



		



		



	}

	

	

	public function uploadExcel()



	{



		$return  = array('status' => 1, 'info' => '上传成功', 'data' => '');



		



		$pic_driver = C('PICTURE_UPLOAD_DRIVER');



		$setting=C('EXCEL_UPLOAD');



		



		$Upload = new Upload($setting,$pic_driver, $config = null);



		



		$info   = $Upload->upload($_FILES);



		 if($info){



			foreach ($info as $key => &$value) {



                /* 记录文件信息 */







                $value['path'] = substr($setting['rootPath'], 1).$value['savepath'].$value['savename'];	//在模板里的url路径



            }



            $return['data'] = $info['file'];



            $return['info'] = $info['file']['name'];



        } else {



            $return['status'] = 0;



            $return['info']   = $Upload->getError();



        }







        /* 返回JSON数据 */







        $this->ajaxReturn($return);



		



		



	}
	
	
	public function uploadAudio()

	{

		$return  = array('status' => 1, 'info' => '上传成功', 'data' => '');
	
		$picname=$_FILES['file']['name'];
		$picsize=$_FILES['file']['size'];
		
		$type=strtolower(strstr($picname,'.'));
		$picname=explode('_',$picname);
		$filename=substr($picname[1],-11); 
		$dir="./Uploads/Audio/2018-01-01/";				
		if(!is_dir($dir))
		{
			mkdir($dir,0777);
		}
		$pics=$filename.$type;
		//上传路径
		$pic_path=$dir.$pics;
		if(move_uploaded_file($_FILES['file']['tmp_name'],$pic_path))
		{
			$pic_path=$pic_path;

		}
		$return  = array('status' => 1, 'info' => '上传成功', 'data' => $pic_path);
        $this->ajaxReturn($return);

	}

	

	







}



?>