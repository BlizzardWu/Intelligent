<?php

namespace api\intelligentcss\Controller;
use Common\Controller\HomebaseController;
use cmf\controller\RestBaseController;
use think\Db;
use think\Validate;
use think\Session;
use think\Cookie;
class UserController extends RestBaseController
{
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

	//用户登录
	public function login()
	{
        $validate = new Validate([
            'username' => 'require',
            'password' => 'require',
            'yzm' => 'require',
            'code' => 'require'
        ]);
        $validate->message([
            'username.require' => '请输入用户名!',
            'password.require' => '请输入您的密码!',
            'yzm.require' => '请输入验证码！'
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
		$userQuery = Db::name("users");	
        $userQuery = $userQuery->where('U_Name', $data['username']);
        $finduser = $userQuery->find();
        if (empty($finduser)) {
            $this->error("用户不存在!");
        } 
        if ($data['password'] != $finduser['U_Password']) {
            $this->error("密码不正确!");
        }  
        if (strtolower($data['yzm']) != strtolower($data['code'])){
            $this->error("验证码错误!");
        }
        $userTokenQuery = Db::name("usertooken")
            ->where('US_User_ID', $finduser['U_ID']);
        $finduserToken  = $userTokenQuery->find();
        $currentTime    = time();
        $expireTime     = $currentTime + 24 * 3600 * 180;
        $token          = md5(uniqid()) . md5(uniqid());
        if (empty($finduserToken)) {
            $result = $userTokenQuery->insert([
                'US_User_Token'       => $token,
                'US_User_ID'     => $finduser['U_ID'],
                'US_User_Expire_Time' => $expireTime,
                'US_User_Create_Time' => $currentTime,
            ]);
        } else {
            $result = $userTokenQuery
                ->where('US_User_ID', $finduser['U_ID'])
                ->update([
                    'US_User_Token'       => $token,
                    'US_User_Expire_Time' => $expireTime,
                    'US_User_Create_Time' => $currentTime
                ]);
        }
        if (empty($result)) {
            $this->error("登录失败!");
        }
        $ip = $_SERVER["REMOTE_ADDR"];
        Db::table('users')->where('U_Name', $data['username'])->update(['U_StateID'=>1,'U_LastTime'=>time(),'U_LastIP'=>$ip]);
        $this->success("登录成功!", ['usertoken' => $token]);
    }

    // 用户退出
    public function logout()
    {
        $this->success("退出成功!");
    }

    public function get_personal()
    {
        $user_token =  $this->request->param('user_token');
        if(!$user_token){
            $this->error("没有获取到token，请先登录！");
        }
        $getuserId = Db::table('usertooken')->where('US_User_Token', $user_token)->value('US_User_ID'); 
        if(!$getuserId){
             $this->error("没有找到相应的token信息，请重新登录！");
        }
        $getuserIfo = Db::table('users')->where('U_ID', $getuserId)->select();
        $this->success("获取用户信息成功！",['Ifo' => $getuserIfo]);
        print_r($getuserIfo);
              
    }

}
