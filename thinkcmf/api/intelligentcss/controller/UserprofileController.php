<?php
namespace api\intelligentcss\controller;

use think\Db;
use think\Paginator;
use think\Validate;
use think\Validatess;
use Think\Upload; 
use cmf\controller\RestBaseController;

// 指定允许其他域名访问    
header('Access-Control-Allow-Origin:*');    
// 响应类型    
header('Access-Control-Allow-Methods:POST');    
// 响应头设置    
header('Access-Control-Allow-Headers:x-requested-with,content-type'); 

class UserprofileController extends RestBaseController
{
    public function editUserName(){
        $id = $this->request->param('id');
        $username = $this->request->param('username');
        if (empty($username)) {
            $this->error("请输入您的用户名！"); 
        }
        if(strlen($username)>16){
            $this->error('用户名长度不得大于16！');
        }
        $findusername = Db::table('users')->where('U_Name', $username)->find();
        if($findusername){
            $this->error('该用户名已存在！请重新输入！');
        }
        Db::table('users')->where('U_ID', $id)->update(['U_Name' => $username]);
        $this->success('更改用户名成功！');       
    }

    public function editAddress(){
        $id = $this->request->param('id');
        $address = $this->request->param('address'); 
        if(strlen($address)>36){
            $this->error('地址写到市区就可以了哦！');
        }       
        if (empty($address)) {
            $this->error("请输入您的地址！"); 
        }
        Db::table('users')->where('U_ID', $id)->update(['U_Address' => $address]);
        $this->success('更改地址成功！');                
    }

    public function editSex(){
        $id = $this->request->param('id');
        $sex = $this->request->param('sex');     
        if($sex == '男'){
            $sexID = 1;
            Db::table('users')->where('U_ID', $id)->update(['U_Sex' => $sexID]);
        }  
        if($sex == '女'){
            $sexID = 2;
            Db::table('users')->where('U_ID', $id)->update(['U_Sex' => $sexID]);
        }
        if($sex == '保密'){
            $sexID = 0;
            Db::table('users')->where('U_ID', $id)->update(['U_Sex' => $sexID]);
        }   
        $this->success('更改性别成功！');  
    }

    public function editSignature(){
        $id = $this->request->param('id');
        $signature = $this->request->param('signature');  
        if (empty($signature)) {
            $this->error("请输入您的个性签名！"); 
        }
        if(strlen($signature)>24){
            $this->error('个性签名太长了哦！');
        }        
        Db::table('users')->where('U_ID', $id)->update(['U_Signature' => $signature]);
        $this->success('更改个性签名成功！');                
    }

    public function uploadHead(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');
        $data = $this->request->param();
        $token = reset($data);
        $id = Db::table('usertooken')->where('US_User_Token',$token)->field('US_User_ID')->find();
        $id = $id['US_User_ID'];
          // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
          $info = $file->validate(['ext'=>'jpg,png,gif'])->move(ROOT_PATH.  'public' . DS . 'upload'. DS . 'userHead');
          if($info){
            $filePath = 'centos2.huangdf.com' .DS . 'thinkcmf'.DS.'public'.DS. 'upload' . DS . 'userHead'.DS .$info->getSaveName();
            $url = Db::table('users')->where('U_ID',$id)->field('U_ImageSrc')->find();
            $url = $url['U_ImageSrc'];
            if($url == 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg'){
              Db::table('users')->where('U_ID',$id)->update(['U_ImageSrc'=>$filePath]);
            }else{
              $arr = explode('/',$url);
              $addr = $arr[count($arr)-2].'/'.$arr[count($arr)-1];
              $this->deletePic($addr);
              Db::table('users')->where('U_ID',$id)->update(['U_ImageSrc'=>$filePath]);
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
      unlink(ROOT_PATH.'public/upload/userHead/'.$addr);
    }
}
