<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
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

class InformationController extends RestBaseController
{
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');
    	$data = $this->request->param();
    	$token = reset($data);
    	$id = Db::table('admintooken')->where('AT_Admin_Token',$token)->field('AT_Admin_ID')->find();
    	$id = $id['AT_Admin_ID'];
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->validate(['ext'=>'jpg,png,gif'])->move(ROOT_PATH.  'public' . DS . 'upload');
            if($info){
                $filePath = 'centos2.huangdf.com' .DS . 'thinkcmf'.DS.'public'.DS. 'upload' . DS . $info->getSaveName();
		$url = Db::table('admins')->where('A_ID',$id)->field('A_ImageSrc')->find();
		$url = $url['A_ImageSrc'];
		if($url == 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg'){
			Db::table('admins')->where('A_ID',$id)->update(['A_ImageSrc'=>$filePath]);
		}else{
			$arr = explode('/',$url);
			$addr = $arr[count($arr)-2].'/'.$arr[count($arr)-1];
			$this->deletePic($addr);
                	Db::table('admins')->where('A_ID',$id)->update(['A_ImageSrc'=>$filePath]);
		}
            }else{
                // 上传失败获取错误信息
                $this->error('上传文件格式不为jpg,png,gif');
            }
        }else{
            $this->error('未选择上传文件');
        }
    }

    public function created(){
        $token = $this->request->param();
        $id = Db::table('admintooken')->where('AT_Admin_Token',reset($token))->field('AT_Admin_ID')->find();
	$leaveWord = Db::table('adminleavewords')->where('L_ProcessingState','未处理')->count();
    	$data = Db::table('admins')->where('A_ID',$id['AT_Admin_ID'])->select();
        $this->success('获取个人信息成功',['data'=>$data,'leaveWord'=>$leaveWord]);
    }

	public function deletePic($addr){
		unlink(ROOT_PATH.'public/upload/'.$addr);
	}

    private function valid_email($address) {
          $mode = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
    if (preg_match($mode, $address)) {
         return true;
    } else {
            return false;
         }
    }

    public function editEmail(){
        $id = $this->request->param('id');
        $email = $this->request->param('email');
        if (!$this->valid_email($email)) {
            $this->error("请输入正确的邮箱！"); 
        }
        Db::table('admins')->where('A_ID', $id)->update(['A_Email' => $email]);
        $this->success('更改邮箱成功！');
    }

    public function editUserName(){
        $id = $this->request->param('id');
        $username = $this->request->param('username');
        if (empty($username)) {
            $this->error("请输入您的用户名！"); 
        }
        if(strlen($username)>16){
            $this->error('用户名长度不得大于16！');
        }
        $findusername = Db::table('admins')->where('A_Name', $username)->find();
        if($findusername){
            $this->error('该用户名已存在！请重新输入！');
        }
        Db::table('admins')->where('A_ID', $id)->update(['A_Name' => $username]);
        $this->success('更改用户名成功！');       
    }

    public function editAddress(){
        $id = $this->request->param('id');
        $address = $this->request->param('address');  
        if (empty($address)) {
            $this->error("请输入您的地址！"); 
        }
        Db::table('admins')->where('A_ID', $id)->update(['A_Address' => $address]);
        $this->success('更改地址成功！');                
    }

    public function editSex(){
        $id = $this->request->param('id');
        $sex = $this->request->param('sex');     
        if($sex == '男'){
            $sexID = 1;
            Db::table('admins')->where('A_ID', $id)->update(['A_Sex' => $sexID]);
        }  
        if($sex == '女'){
            $sexID = 2;
            Db::table('admins')->where('A_ID', $id)->update(['A_Sex' => $sexID]);
        }
        if($sex == '保密'){
            $sexID = 0;
            Db::table('admins')->where('A_ID', $id)->update(['A_Sex' => $sexID]);
        }   
        $this->success('更改性别成功！');  
    }

    public function editSignature(){
        $id = $this->request->param('id');
        $signature = $this->request->param('signature');  
        if (empty($signature)) {
            $this->error("请输入您的个性签名！"); 
        }
        if(strlen($signature)>100){
            $this->error('个性签名太长啦！');
        }        
        Db::table('admins')->where('A_ID', $id)->update(['A_Signature' => $signature]);
        $this->success('更改个性签名成功！');                
    }

    public function editPassword(){
        $validate = new Validate([
            'id' => 'require',
            'password' => 'require',
            'newpassword' => 'require',
            'newpassword1' => 'require',
        ]); 
        $validate->message([
            'password.require' => '请输入您的原密码!',
            'newpassword.require' => '请输入您的新密码!',
            'newpassword1.require' => '请确认您的新密码!',    
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $result = Db::table('admins')->where('A_ID',$data['id'])->value('A_Password');
        $jmPassword = sha1( $data['password'] );
        if($result != $jmPassword){
            $this->error('原密码不匹配，请重新输入！');
        }
        if ($data['newpassword'] != $data['newpassword1']) {
            $this->error('两次新密码不匹配！请您重新输入！');
        }
        if ((strlen($data['newpassword']) < 6) || (strlen($data['newpassword1']) > 16)) {
           $this->error('新密码不能少于6位且不能大于16位！');
        }
        Db::name('admins')
            ->where('A_ID',$data['id'])
            ->update(['A_Password'=>$jmPassword]);
        $this->success('更改密码成功！');
    }
}
