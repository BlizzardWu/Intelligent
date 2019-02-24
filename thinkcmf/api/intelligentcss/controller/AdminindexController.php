<?php

namespace api\intelligentcss\Controller;
use Common\Controller\HomebaseController;
use cmf\controller\RestBaseController;
use think\Db;
use think\Validate;
use think\Session;
use think\Cookie;
class AdminindexController extends RestBaseController
{
	//管理员登录
	public function login()
	{
        $validate = new Validate([
            'username' => 'require',
            'password' => 'require',
	    'yzm' => 'require',
	    'code' => 'require',
        ]);
        $validate->message([
            'username.require' => '请输入用户名!',
            'password.require' => '请输入您的密码!',
	    'yzm.require' => '请输入验证码!',
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
		$adminQuery = Db::name("admins");	
        $adminQuery = $adminQuery->where('A_Name', $data['username']);
        $findadmin = $adminQuery->find();
        if (empty($findadmin)) {
            $this->error("用户不存在!");
        } else {

            switch ($findadmin['A_CheckStatues']) {
                case 0:
                    $this->error('您账户已经被冻结！请联系超级管理员！');
                case 2:
                    $this->error('账户还没有验证成功!');
            }
	    $data['password']=sha1($data['password']);
            if ($data['password'] != $findadmin['A_Password']) {
                $this->error("密码不正确!");
            }
	    if (strtolower($data['yzm']) != strtolower($data['code'])){
		$this->error("验证码错误!");
	    }
        }
        $adminTokenQuery = Db::name("admintooken")
            ->where('AT_Admin_ID', $findadmin['A_ID']);
        $findadminToken  = $adminTokenQuery->find();
        $currentTime    = time();
        $expireTime     = $currentTime + 24 * 3600 * 180;
        $token          = md5(uniqid()) . md5(uniqid());
        if (empty($findadminToken)) {
            $result = $adminTokenQuery->insert([
                'AT_Admin_Token'       => $token,
                'AT_Admin_ID'     => $findadmin['A_ID'],
                'AT_Admin_Expire_Time' => $expireTime,
                'AT_Admin_Create_Time' => $currentTime,
            ]);
        } else {
            $result = $adminTokenQuery
                ->where('AT_Admin_ID', $findadmin['A_ID'])
                ->update([
                    'AT_Admin_Token'       => $token,
                    'AT_Admin_Expire_Time' => $expireTime,
                    'AT_Admin_Create_Time' => $currentTime
                ]);
        }
        if (empty($result)) {
            $this->error("登录失败!");
        }
	$Level = Db::table('admins')->where('A_ID',$findadmin['A_ID'])->value('A_Level');
        $this->success("登录成功!", ['token' => $token,'level'=>$Level]);
    }

    public function yzm()
    {
	    $num = 4;$size = 20; $width = 85;$height = 32;	  
	    //设置验证码字符集合
	    $str = "23456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVW";
	    //保存获取的验证码
	    $code = ''; 
	    //随机选取字符
	    for ($i = 0; $i < $num; $i++) {
	        $code .= $str[mt_rand(0, strlen($str)-1)];
	    }
	    //创建验证码画布
	    $im = imagecreatetruecolor($width, $height);
	    //背景色
	    $back_color = imagecolorallocate($im, mt_rand(0,100),mt_rand(0,100), mt_rand(0,100));
	    //文本色
	    $text_color = imagecolorallocate($im, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
	    imagefilledrectangle($im, 0, 0, $width, $height, $back_color);
	    // 画干扰线
	    for($i = 0;$i < 5;$i++) {
	        $font_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
	        imagearc($im, mt_rand(- $width, $width), mt_rand(- $height, $height), mt_rand(30, $width * 2), mt_rand(20, $height * 2), mt_rand(0, 360), mt_rand(0, 360), $font_color);
	    }
	    // 画干扰点
	    for($i = 0;$i < 50;$i++) {
	        $font_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
	        imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $font_color);
	    }
	    //随机旋转角度数组
	    $array=array(5,4,3,2,1,0,-1,-2,-3,-4,-5);
	    @imagefttext($im, $size , array_rand($array), 12, $size + 6, $text_color, '/var/web/thinkcmf/font/LFAX.TTF', $code);
	    ob_start ();
	    imagepng($im);
	    $image_data = ob_get_contents ();
	    ob_end_clean ();
	    $image_data_base64 = "data:image/png;base64,". base64_encode ($image_data);
	    $this->success("获取验证码成功", ['yzm1' => $image_data_base64,'code' => $code]);
    }

    private function valid_email($address) {
          $mode = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
  	if (preg_match($mode, $address)) {
   		 return true;
  	} else {
    		return false;
  	     }
    }

    public function register()
    {
         $validate = new Validate([
            'username' => 'require',
            'email' => 'require',
            'firml' => 'require',
        ]);   	
        $validate->message([
            'username.require' => '请输入用户名!',
 			'email.require' => '请输入邮箱！',
 			'firml' =>'请输入您的企业！',     
        ]); 
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
    	if (!$this->valid_email($data['email'])) {
      		$this->error("请输入正确的邮箱！"); 
    	}
		$firmQuery = Db::name("firm");
		$firmQuery = $firmQuery->where('F_Name', $data['firml']);
		
		$adminQuery = Db::name("admins");
		$adminQuery = $adminQuery->where('A_Email', $data['email']);

		$findfirm = $firmQuery->find();
		$findadmin = $adminQuery->find();
		if($findfirm)
		{
			$this->error('该企业已经被注册！');
		}
		$result = Db::table('firm')->where('F_CreateEmail',$data['email'])->find();	
		if($result){
			$this->error('该邮箱已经被注册');
		}
		$result = Db::table('admins')->where('A_Name',$data['username'])->find();
		if($result){
			$this->error('该用户名已存在');
		}else{
            $str = "23456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVW";
            $code = ''; 

            for ($i = 0; $i < 4; $i++) {
                $code .= $str[mt_rand(0, strlen($str)-1)];
            }
            $register = array(
                'code'=>$code, 
                'register_username'=>($data['username']),
                'register_email'=>($data['email']),
                'register_firml'=>($data['firml']),
            );
            session::set('register', $register);
            vendor('PHPMail.send');
            $send = new \send();
            $send->sendMail(($data['email']),'智客脑系通知','您的校验码是:'.$code.'<br />'.'请不要泄露给其他人。');
	    $code = md5($code);
            $this->success('我们已经向您的邮箱发送了校验码！请注意查收！',['code' => $code]);
            }		
	}
    

    public function register_new(){
        $validate = new Validate([
            'username' =>'require',
	    'code' =>'require',
            'jym' => 'require',
            'email' => 'require',
            'firml' => 'require',
            'password' => 'require',
            'password1' => 'require',
        ]); 
        $validate->message([
            'jym.require' => '请输入校验码!',
            'password.require' => '请输入您的密码!',
            'password1.require' => '请确认您的密码!',    
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        if ($data['password'] != $data['password1']) {
            $this->error('两次密码不匹配！请您重新输入！');
        }
        if ((strlen($data['password']) < 6) || (strlen($data['password']) > 16)) {
           $this->error('密码不能少于6位且不能大于16位！');
        }
        $data['password'] = sha1( $data['password'] );
	$data['jym'] = md5($data['jym']);
        if($data['jym'] == $data['code']){
            $now_time = strtotime(date('y-m-d h:i:s',time()));
            $row = array();
            $row['F_Name'] = $data['firml'];
            $row['F_CreateEmail'] = $data['email'];
            $row['F_Createtime'] = $now_time;
            $result = Db::table('firm')->where('F_Name', $data['firml'])->select();
            if(count($result) > 0){
                $this->error('该企业已经被注册！');
            }else{
                Db::table('firm')->insert($row);
                $firml = Db::table('firm')->where('F_Name', $data['firml'])->value('F_ID');
                $ip = $_SERVER["REMOTE_ADDR"];
                $row_admin = array(
                    'A_ImageSrc' => 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg',
                    'A_Name' => $data['username'],
                    'A_Email' => $data['email'],            
                    'A_Password' => $data['password'],
                    'A_FirmID' => $firml,
		    'A_Group'=>0,
                    'A_CheckStatues' => 1,
                    'A_Sex' => '0',
                    'A_Address' => 'null',
                    'A_Signature' => 'null',
                    'A_StateID' => 1,
                    'A_Level'  => '1',
		    'A_Maximum'=>100,
                    'A_Credibility' => '100',
                    'A_Experience' => '100',
                    'A_Attitude' => '100',
                    'A_LastIP' => $ip,
                    'A_LastTime' => $now_time,
		    'currentReception'=>0,
		    'allReception'=>0,
 		    'CumulativeTime'=>0,
		    'allConversation'=>0,
                    'A_Createtime' => $now_time,
                    'A_Updatetime' => $now_time,
                );
                Db::table('admins')->insert($row_admin);
                $this->success('注册成功！');              
            }
        }else{
            $this->error('您输入的校验码有误，请重新输入！');
        }       
    }

    // 管理员退出
    public function logout()
    {
        $this->success("退出成功!");
    }

}
