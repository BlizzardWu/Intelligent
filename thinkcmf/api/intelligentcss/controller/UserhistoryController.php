<?php

namespace api\intelligentcss\Controller;
use Common\Controller\HomebaseController;
use cmf\controller\RestBaseController;
use GatewayClient\Gateway;
use think\Db;
use think\Validate;
use think\Session;
use think\Cookie;
// 指定允许其他域名访问    
header('Access-Control-Allow-Origin:*');    
// 响应类型    
header('Access-Control-Allow-Methods:POST');    
// 响应头设置    
header('Access-Control-Allow-Headers:x-requested-with,content-type'); 

class UserhistoryController extends RestBaseController{
	public function allHistory()
	{
        $fromID =  $this->request->param('fromID');
        $result = Db::table('session')
                    ->where('S_SendID|S_ReceiveID',$fromID)
                    ->order("S_SendTime desc")
                    ->limit(50)
                    ->select();
        $this->success('获取历史记录成功！', $result);		
	}
    public function list_Img()
    {
        $fromname =  $this->request->param('fromname');
        $result = Db::table('session')
                    ->where('S_SendID|S_ReceiveID',$fromname)
                    ->where('S_MessageType',2)
                    ->order("S_SendTime desc")
                    ->select();
        $this->success('获取聊天图片成功！', $result);        
    }

    public function list_Datehistory()
    {
        $fromname =  $this->request->param('fromname');
        $date = $this->request->param('date');
        $dateTo =  strtotime($date);
        $endDay = $dateTo+1*24*60*60;
        $result = Db::table('session')
                    ->where('S_SendID|S_ReceiveID',$fromname)
                    ->whereTime('S_SendTime','between',[$dateTo,$endDay])
                    ->order("S_SendTime desc")
                    ->select();
        $this->success('获取历史记录成功！', $result);
    }

    public function list_Imghistory()
    {
        $fromname =  $this->request->param('fromname');
        $date = $this->request->param('date');
        $dateTo =  strtotime($date);
        $endDay = $dateTo+1*24*60*60;
        $result = Db::table('session')
                    ->where('S_SendID|S_ReceiveID',$fromname)
                    ->whereTime('S_SendTime','between',[$dateTo,$endDay])
                    ->where('S_MessageType',2)
                    ->order("S_SendTime desc")
                    ->select();
        $this->success('获取聊天图片成功！', $result);
    }  

    public function list_Searchhistory()
    {
        $fromname =  $this->request->param('fromname');
        $keyword = $this->request->param('keyword');
        $result = Db::table('session')
                    ->where('S_SendID|S_ReceiveID',$fromname)
                    ->where('S_Message', 'like','%'.$keyword.'%')
                    ->where('S_MessageType',1)
                    ->order("S_SendTime desc")
                    ->select();
        $this->success('获取历史记录成功！', $result);        
    }
}