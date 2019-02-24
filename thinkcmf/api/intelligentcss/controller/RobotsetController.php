<?php

namespace api\intelligentcss\controller;

use think\Db;
use cmf\controller\RestBaseController;
use think\Validate;

// 指定允许其他域名访问    
header('Access-Control-Allow-Origin:*');    
// 响应类型    
header('Access-Control-Allow-Methods:POST');    
// 响应头设置    
header('Access-Control-Allow-Headers:x-requested-with,content-type'); 

class RobotsetController extends RestBaseController
{
    public function editUnknown(){
        $unknown = $this->request->param('unknown');  
        if (empty($unknown)) {
            $this->error("请输入未知问题回答！"); 
        }
        Db::table('robots')->where('R_ID', 1)->update(['R_Unknown' => $unknown]);
        $this->success('更改未知问题回答成功！');                
    }

    public function editWelcome(){
        $welcome = $this->request->param('welcome');  
        if (empty($welcome)) {
            $this->error("请输入欢迎语！"); 
        }
        Db::table('robots')->where('R_ID', 1)->update(['R_Welcome' => $welcome]);
        $this->success('更改欢迎语成功！');                
    }

    public function editNote(){
        $note = $this->request->param('note');
        if (empty($note)) {
            $this->error("请输入机器人备注！"); 
        }
        Db::table('robots')->where('R_ID', 1)->update(['R_Note' => $note]);
        $this->success('更改机器人备注成功！');
    }

	public function editUserName(){
        $username = $this->request->param('username');
        if (empty($username)) {
            $this->error("请输入机器人昵称！"); 
        }
        if(strlen($username)>100){
            $this->error('机器人昵称太长啦！');
        }
        Db::table('robots')->where('R_ID', 1)->update(['R_Name' => $username]);
        $this->success('更改机器人昵称成功！');  		
	}

	public function allDate(){
		$result = Db::table('robots')->where('R_ID',1)->select();
		$this->success('获取机器人资料成功！',$result);
	}

	public function headImg(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->validate(['ext'=>'jpg,png,gif'])->move(ROOT_PATH.  'public' . DS . 'upload'. DS . 'robotHead');
            if($info){
                $filePath = 'centos2.huangdf.com' .DS . 'thinkcmf'.DS.'public'.DS. 'upload' . DS . 'robotHead'.DS .$info->getSaveName();
		$url = Db::table('robots')->where('R_ID',1)->field('R_ImageSrc')->find();
		$url = $url['R_ImageSrc'];
		if($url == 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg'){
			Db::table('robots')->where('R_ID',1)->update(['R_ImageSrc'=>$filePath]);
		}else{
			$arr = explode('/',$url);
			$addr = $arr[count($arr)-2].'/'.$arr[count($arr)-1];
			$this->deletePic($addr);
                	Db::table('robots')->where('R_ID',1)->update(['R_ImageSrc'=>$filePath]);
		}
            }else{
                // 上传失败获取错误信息
                $this->error('上传文件格式不为jpg,png,gif');
            }
        }else{
            $this->error('未选择上传文件');
        }
	}

	public function deletePic($addr){
		unlink(ROOT_PATH.'public/upload/robotHead/'.$addr);
	}

	public function getRobot(){
		$result = Db::table('robots')->where('R_ID',1)->select();
		$this->error('获取机器人信息成功',$result);
	}	
}