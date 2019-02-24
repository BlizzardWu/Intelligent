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

class GatewayserverController extends RestBaseController{
    //根据日期寻找图片
    public function list_Imghistory()
    {
        $fromname =  $this->request->param('fromname');
        $toname = $this->request->param('toname');
        $date = $this->request->param('date');
        $dateTo =  strtotime($date);
        $endDay = $dateTo+1*24*60*60;
        $result = Db::table('session')
                    ->where('S_SendID|S_ReceiveID',$fromname)
                    ->where('S_ReceiveID|S_SendID',$toname)
                    ->whereTime('S_SendTime','between',[$dateTo,$endDay])
                    ->where('S_MessageType',2)
                    ->order("S_SendTime desc")
                    ->select();
        $this->success('获取聊天图片成功！', $result);
    }  
    //列出管理员历史记录图片
    public function list_Img()
    {
        $fromname =  $this->request->param('fromname');
        $toname = $this->request->param('toname');
        $result = Db::table('session')
                    ->where('S_SendID|S_ReceiveID',$fromname)
                    ->where('S_ReceiveID|S_SendID',$toname)
                    ->where('S_MessageType',2)
                    ->order("S_SendTime desc")
                    ->select();
        $this->success('获取聊天图片成功！', $result);        
    }
    public function cut_word(){
	$unknown = Db::table('robots')->where('R_ID',1)->value('R_Unknown');
        $userContent = $this->request->param('userContent');
	vendor('phpanalysis.phpanalysis');
        $pa = new \PhpAnalysis();
        $pa->SetSource($userContent);
        $pa->resultType=2;
        $pa->differMax=true;
        $pa->StartAnalysis(true);
        $arr=$pa->GetFinallyKeywords();
        $tagsArr = explode (",",$arr);
        $tagsArrLength = count($tagsArr);
        $arr1=array();
        $arr2=array();
        //没有切到词，可能为标点符号、小于三位数的数字、字母
        //为了弥补不足，增加快捷词
        if(empty($tagsArr[0])){
            $result1 = Db::table('publiclibrary')
                    ->where('P_QuickWord',$userContent)
                    ->value('P_Reply');   
            if(empty($result1)){
	         if(strlen($userContent)>24){
			Db::table('unknown')->insert(['Un_Question'=>$userContent,'Un_Answer'=>'无','Un_Createtime'=>time()]);
			$this->error('answer',['Q_Answer' => $unknown]);
		 }
		 $this->error('answer',['Q_Answer' => $unknown]);
            }         
            $this->success('answer',['Q_Answer' => $result1]);
        }
        for ($i=0; $i<$tagsArrLength; $i++) {
            //获取问题或相似问题，放到一个数组里
            $result = Db::table('question')
                    ->where('Q_Question', 'like','%'.$tagsArr[$i].'%')
                    ->whereOr('Q_SimilarPro', 'like','%'.$tagsArr[$i].'%')
                    ->column('Q_Question'); 
           // print_r($result);
            if(!empty($result)){
                $arr1[]=$result;
            }
        }
        for($k = 0; $k < count($arr1); $k++) {
            for($j = 0; $j < count($arr1[$k]); $j++) {
                $arr2[]=$arr1[$k][$j];
            }
        }        
        //获取频率最高的
        $array = array_count_values($arr2);  // 统计数组中所有值出现的次数
        arsort($array); // 按照键值对关联数组进行降序排序

        //问题
        $first_key = key($array);
        //猜你想问问题
        $qc = array_unique($arr2);
        $Guess = array_slice($qc,0,5);            
        if(empty($first_key)){
             if(strlen($userContent)>24){
                Db::table('unknown')->insert(['Un_Question'=>$userContent,'Un_Answer'=>'无','Un_Createtime'=>time()]);
                $this->error('answer',['Q_Answer' => $unknown,'Guessword'=>$Guess]); 
	    }
            $this->error('answer',['Q_Answer' => $unknown,'Guessword'=>$Guess]);
        }
        $answer = Db::table('question')
                ->where('Q_Question',$first_key)
                ->value('Q_Answer'); 
        $count = Db::table('question')->where('Q_Question',$first_key)->value('Q_Count');
        $countIt = $count+1;
        Db::table('question')->where('Q_Question',$first_key)->update(['Q_Count' => $countIt]);    
        $this->success('answer',['Q_Answer' =>$answer,'Guessword'=>$Guess]);   
        
    }
    public function hotQuestion(){
        $hot = Db::table('question')->limit(5)->order("Q_Count desc")->select();
        $this->success('answer',$hot);   
    }
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');
        $data = $this->request->param();
        // 移动到框架应用根目录/public/upload/chatPictures/ 目录下
        if($file){
            $info = $file->validate(['ext'=>'jpg,png,gif'])->move(ROOT_PATH.  'public' . DS . 'upload'. DS . 'chatPictures');
            if($info){
                $filePath = 'centos2.huangdf.com' .DS . 'thinkcmf'.DS.'public'.DS. 'upload' . DS . 'chatPictures'. DS .$info->getSaveName();
		$this->success('',$filePath);
            }else{
                // 上传失败获取错误信息
                $this->error('发送图片格式不为jpg,png,gif');
            }
        }else{
            $this->error('未选择图片');
        }
    }
    //获取公共库内容
    public function listPublicQuick()
    {
        $result = Db::table('publiclibrary')->select();
        $this->success('获取成功', $result);
    }
    //获取个人快捷库内容
    public function listPersonalQuick()
    {
        $token = $this->request->param('token');
        $adminID = Db::table('admintooken')->where('AT_Admin_Token',$token)->value('AT_Admin_ID');             
        $result = Db::table('personallibrary')->where('P_Belongs',$adminID)->select();
        $this->success('获取成功', $result);
    }
    //搜索问题
    public function findKnowledge()
    {
        $searchQuestion = $this->request->param('searchQuestion');
        if(empty($searchQuestion)){
            $result = Db::table('question')->select();
            $this->error('搜索内容不能为空！',$result);
        }   
        $result = Db::table('question')->where('Q_Question', 'like','%'.$searchQuestion.'%')->whereOr('Q_SimilarPro', 'like','%'.$searchQuestion.'%')->select();
        if(count($result)==0){
            $this->error('没有搜索到相关内容！',$result);
        }
        $this->success('获取成功', $result);

    }
    //获取知识库内容
    public function listKnowledge()
    {
        $result = Db::table('question')->select();
        $this->success('获取成功', $result);
    }
    //登录后台，该管理员/客服状态在线
    public function switchStateOn()
    {
        $token = $this->request->param('token');
        $result = Db::table('admintooken')->where('AT_Admin_Token',$token)->value('AT_Admin_ID');
        Db::table('admins')->where('A_ID',$result)->update(['A_StateID' =>  1]);

    }
    //退出后台，该管理员/客服状态离线
    public function switchStateOff()
    {
        $token = $this->request->param('token');
        $result = Db::table('admintooken')->where('AT_Admin_Token',$token)->value('AT_Admin_ID');
        Db::table('admins')->where('A_ID',$result)->update(['A_StateID' =>  2]);

    }    
    //参评人数+1,相对满意度
    public function participate()
    {
        $satisfaction =  $this->request->param('satisfaction');
        $satisfaction1 = Db::table('datastatistics')->where('D_ID',1)->value('satisfaction');  
        $satisfactioncount = ($satisfaction + $satisfaction1)/2;
        Db::table('datastatistics')->where('D_ID',1)->update(['satisfaction' =>$satisfactioncount]);
	Db::table('datastatistics')->where('D_ID',1)->setInc('participatenum');
    }
    //未接入会话量
    public function getTodayUnSession()
    {
        $querynum = Db::table('datastatistics')->where('D_ID',1)->value('unaccessed');
        $querynumer = $querynum  + 1;     
        Db::table('datastatistics')->where('D_ID',1)->update(['unaccessed' => $querynumer]); 
    }
    //今日已经接入会话量
    public function getTodaySession()
    {
        //总会话量+1
        $querynum = Db::table('datastatistics')->where('D_ID',1)->value('conversationAll');
        $querynumer = $querynum  + 1;     
        Db::table('datastatistics')->where('D_ID',1)->update(['conversationAll' => $querynumer]); 

        //已经接入会话量+1
        $querynum1 = Db::table('datastatistics')->where('D_ID',1)->value('inaccessed');
        $querynumer1 = $querynum1  + 1;     
        Db::table('datastatistics')->where('D_ID',1)->update(['inaccessed' => $querynumer1]);         
    }
    //插入排队总数，不管状态，只要一进入排队就是排队
    public function getLineUpCount()
    {
        $querynum = Db::table('datastatistics')->where('D_ID',1)->value('waitedAll');
        $querynumer = $querynum  + 1;     
        Db::table('datastatistics')->where('D_ID',1)->update(['waitedAll' => $querynumer]);          
    }
    //插入排队分组人数-1
    public function getUnlineUp1()
    {
        $querynum = Db::table('datastatistics')->where('D_ID',1)->value('waited');
        $querynumer = $querynum  - 1;
        Db::table('datastatistics')->where('D_ID',1)->update(['waited' => $querynumer]);
    }
    //插入排队分组人数-1,未接入会话量-1,今日会话量-1
    public function getUnlineUp()
    {
        $querynum = Db::table('datastatistics')->where('D_ID',1)->value('waited');
        $querynumer = $querynum  - 1;     
        Db::table('datastatistics')->where('D_ID',1)->update(['waited' => $querynumer]);   

        $queryUnSession = Db::table('datastatistics')->where('D_ID',1)->value('unaccessed');
        $queryUnSession1 = $queryUnSession  - 1;     
        Db::table('datastatistics')->where('D_ID',1)->update(['unaccessed' => $queryUnSession1]); 

        $queryInSession = Db::table('datastatistics')->where('D_ID',1)->value('conversationAll');
        $queryInSession1 = $queryInSession  - 1;     
        Db::table('datastatistics')->where('D_ID',1)->update(['conversationAll' => $queryInSession1]); 
    }
    //插入排队分组人数+1,未接入会话量+1,今日会话量+1
    public function getInlineUp()
    {
        $querynum = Db::table('datastatistics')->where('D_ID',1)->value('waited');
        $querynumer = $querynum  + 1;     
        Db::table('datastatistics')->where('D_ID',1)->update(['waited' => $querynumer]);   

        $queryUnSession = Db::table('datastatistics')->where('D_ID',1)->value('unaccessed');
        $queryUnSession1 = $queryUnSession  + 1;     
        Db::table('datastatistics')->where('D_ID',1)->update(['unaccessed' => $queryUnSession1]); 

        $queryInSession = Db::table('datastatistics')->where('D_ID',1)->value('conversationAll');
        $queryInSession1 = $queryInSession  + 1;     
        Db::table('datastatistics')->where('D_ID',1)->update(['conversationAll' => $queryInSession1]); 

    }
    //插入正在咨询，咨询人数减去一
    public function getUnChatUser()
    { 
        $querynum = Db::table('datastatistics')->where('D_ID',1)->value('querynum');
        $querynumer = $querynum  - 1;     
        Db::table('datastatistics')->where('D_ID',1)->update(['querynum' => $querynumer]);        
    }
    //插入正在咨询，咨询人数加一
    public function getInChatUser()
    {
        $querynum = Db::table('datastatistics')->where('D_ID',1)->value('querynum');
        $querynumer = $querynum  + 1;     
        Db::table('datastatistics')->where('D_ID',1)->update(['querynum' => $querynumer]);        
    }
    //用户留言‘我的留言’
    public function listUserLeave()
    {
        $userid = $this->request->param('userid');
        $myLeave = Db::table('leavewords')->where('U_ID',$userid)->select();
        $this->success('查看留言成功！',$myLeave);
    }
    //用户留言‘我要留言’
    public function saveUserLeave()
    {
        $validate = new Validate([
	    'userid' =>'require',
            'username' =>'require',
            'nikename' => 'require',
            'phone' => 'require',
            'email' => 'require',
            'details' => 'require'
        ]);
        $validate->message([
            'nikename.require' => '请输入称呼',
            'phone.require' => '请输入您的电话号码!',
            'email.require' => '请输入您的邮箱！',
            'details.require' =>'请输入留言内容',
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $DetailsTime=time();
        $row_data = array(
	    'U_ID'=>$data['userid'],
            'L_Name' => $data['username'],
            'L_NikeName' => $data['nikename'],           
            'L_Phone' => $data['phone'],
            'L_Email' => $data['email'],
            'L_Details' => $data['details'],
            'L_DetailsTime' => $DetailsTime,
            'L_ServerName' =>'无',
            'L_Reply' => '无',
            'L_ReplyTime' => 0,
            'L_ProcessingState' => '未处理'
        );
        Db::table('leavewords')->insert($row_data);
        Db::table('adminleavewords')->insert($row_data);
        $this->success('留言成功！');
    }
    public function serverToGrade()
    {
        $toname = $this->request->param('toname');
        $sessionScore = $this->request->param('sessionScore'); 
        $originalScore =  Db::table('users')->where('U_ID', $toname)->value('U_Evaluation');
        $finallScore = $originalScore*9/10 + $sessionScore*1/10;
        Db::table('users')->where('U_ID', $toname)->update(['U_Evaluation' => $finallScore]);
        $this->success('评价成功！',$finallScore);      
    }

    public function get_adminDate()
    {
        $admin_token = $this->request->param('admin_token');
        $getAdminId = Db::table('admintooken')->where('AT_Admin_Token', $admin_token)->value('AT_Admin_ID');
        $getAdminIfo = Db::table('admins')->where('A_ID', $getAdminId)->select(); 
        $this->success('获取信息成功！',$getAdminIfo);
    }

    public function get_personal()
    {
        $user_token = $this->request->param('user_token');
        $getuserId = Db::table('usertooken')->where('US_User_Token', $user_token)->value('US_User_ID'); 
        $getuserIfo = Db::table('users')->where('U_ID', $getuserId)->select();      
        $this->success('获取信息成功！',$getuserIfo); 
    }

    public function get_adminID()
    {
        $fGroup = array();
        $group =  $this->request->param('group');
        $group = htmlspecialchars_decode(stripslashes($group));
        $data = (array)(json_decode($group));
        if(count($data) == 0){
            $this->error('抱歉，当前没有客服在线！已将您列入排队区！');
        }
        foreach($data as $d){
            $change_data = str_replace("server.","",$d);
            array_push($fGroup,$change_data);       
        }
        //获取信誉分最好的客服
        $getAdmin = Db::table('admins')
                    ->where('A_ID','in',$fGroup)
                    ->where('A_Maximum','exp',Db::raw('>`currentReception`'))
                    ->order('A_Credibility', 'desc')
                    ->select();
        if(count($getAdmin) == 0){
            $this->error('抱歉，当前客服接待已满，已将您列入排队区！');
        }
        $this->success('获取信息成功！', $getAdmin[0]);
    }


    public function save_history()
    {
	$who = $this->request->param('who');
	$fromid = $this->request->param('fromid');
        $toid = $this->request->param('toid');
	$fromname = $this->request->param('fromname');
	$toname = $this->request->param('toname');
        $message = $this->request->param('message');
        $messageType = $this->request->param('messageType');
        $sendTime = $receiveTime = time();
        $sendStatus = '已发送';
        $receiveStatus = '已接收';
        $row_history = array(
            'S_Message' => $message,
            'S_SendStatus' => $sendStatus,           
            'S_ReceiveStatus' => $receiveStatus,
            'S_SendID' => $fromid,
	    'S_SendName' => $fromname,
            'S_ReceiveID' => $toid,
	    'S_ReceiveName' => $toname,
            'S_SendTime' => $sendTime,
            'S_ReceiveTime' => $receiveTime,
            'S_MessageType' => $messageType,
        );
        Db::table('session')->insert($row_history);
	Db::table('admins')->where('A_ID',$who)->setInc('allConversation');   
    }
    public function list_history()
    {
        $fromname =  $this->request->param('fromname');
        $toname = $this->request->param('toname');
        $result = Db::table('session')
                    ->where('S_SendID|S_ReceiveID',$fromname)
                    ->where('S_ReceiveID|S_SendID',$toname)
                    ->limit(5)
                    ->order("S_SendTime desc")
                    ->select();
        $this->success('获取历史记录成功！', $result);
    }

    public function list_Allhistory()
    {
        $fromname =  $this->request->param('fromname');
        $toname = $this->request->param('toname');
        $result = Db::table('session')
                    ->where('S_SendID|S_ReceiveID',$fromname)
                    ->where('S_ReceiveID|S_SendID',$toname)
                    ->order("S_SendTime desc")
                    ->limit(50)
                    ->select();
        $this->success('获取历史记录成功！', $result);
    }

    public function list_Datehistory()
    {
        $fromname =  $this->request->param('fromname');
        $toname = $this->request->param('toname');
        $date = $this->request->param('date');
        $dateTo =  strtotime($date);
        $endDay = $dateTo+1*24*60*60;
        // echo date('Y-m-d H:i:s',$dateTo+1*24*60*60);
        // echo $date;
        $result = Db::table('session')
                    ->where('S_SendID|S_ReceiveID',$fromname)
                    ->where('S_ReceiveID|S_SendID',$toname)
                    // ->where('S_SendTime','>',$dateTo)
                    // ->where('S_SendTime','<',$endDay)
                    ->whereTime('S_SendTime','between',[$dateTo,$endDay])
                    ->order("S_SendTime desc")
                    ->select();
        $this->success('获取历史记录成功！', $result);
    }

    public function list_Searchhistory()
    {
        $fromname =  $this->request->param('fromname');
        $toname = $this->request->param('toname');
        $keyword = $this->request->param('keyword');
        $result = Db::table('session')
                    ->where('S_SendID|S_ReceiveID',$fromname)
                    ->where('S_ReceiveID|S_SendID',$toname)
                    ->where('S_Message', 'like','%'.$keyword.'%')
		    ->where('S_MessageType',1)
                    ->order("S_SendTime desc")
                    ->select();
        $this->success('获取历史记录成功！', $result);        
    }

    public function startTime_historySession()
    {
       $startTime = time();
       $this->success('获取历史会话开始时间成功！', $startTime);
    }
    //插入历史会话表
    public function insert_historySession()
    {
        $initiator =  $this->request->param('initiator');
        $receiver =  $this->request->param('receiver');
        $startTime =  $this->request->param('startTime');
        $satisfaction =  $this->request->param('satisfaction');
        $who = $this->request->param('who');
        $endTime = time();
        $conversationTime = $endTime-$startTime;
        $row_historySession = array(
            'startTime' => $startTime,
            'endTime' => $endTime,           
            'initiator' => $initiator,
            'receiver' => $receiver,
            'conversationTime' => $conversationTime,
            'satisfaction' => $satisfaction,
            'id' => $who,
        );  
        if(Db::table('history')->where(array('startTime'=>$startTime,'initiator'=>$initiator))->count()){
            Db::table('history')->where(array('startTime'=>$startTime,'initiator'=>$initiator))->update(['endTime' => $endTime,'conversationTime' => $conversationTime,'satisfaction' => $satisfaction]);  
             
        }else{
            Db::table('history')->insert($row_historySession);             
        } 
             
    }

    //用户或者客服下线更新历史会话表
    public function update_historySession()
    {

    }

    //用户评分
    public function score()
    {
        $sessionScore = $this->request->param('sessionScore');
        $attitudeScore = $this->request->param('attitudeScore');
        $comprehensiveScore = $this->request->param('comprehensiveScore');
        $fromname =  $this->request->param('fromname');
        $toname = $this->request->param('toname');     
        //管理员表

        //会话表   
    }
    public function check()
    {
        $adminToken = $this->request->param('admin_token');
        $adminID = Db::table('admintooken')->where('AT_Admin_Token',$adminToken)->value('AT_ID');
        $adminMax = Db::table('admins')->where('A_ID',$adminID)->value('A_Maximum');
        $adminMin = Db::table('admins')->where('A_ID',$adminID)->value('currentReception');
        if($adminMax>$adminMin){
            $currentReception = $adminMin + 1;
            Db::table('admins')->where('A_ID',$adminID)->update(['currentReception'=>$currentReception]);
            $this->success('接待成功！');
        }else{
            $this->error('您当前接待量已满！请联系管理员修改接待量！');
        }

    }
    public function giveScore()
    {
        $toID = $this->request->param('toID');
        $credibility = $this->request->param('credibility');
        $attitude = $this->request->param('attitude');
        $experience = $this->request->param('experience');
        $admin = Db::table('admins')->where('A_ID',$toID)->find();
        $oldCredibility = $admin['A_Credibility'];
        $oldAttitude = $admin['A_Attitude'];
        $oldExperience = $admin['A_Experience'];
        $newCredibility = $oldAttitude*(9/10)+$credibility*(1/10);
        $newAttitude =  $oldAttitude*(9/10)+$attitude*(1/10);
        $newExperience = $oldExperience*(9/10)+$experience*(1/10);
        Db::table('admins')->where('A_ID',$toID)->update(['A_Credibility'=>$newCredibility,'A_Attitude'=>$newAttitude,'A_Experience'=>$newExperience]);
        $this->success('给客服评分成功！');
    }	
    public function getAdminDate()
    {
        $adminID = $this->request->param('adminID');
        $result =  Db::table('admins')->where('A_ID',$adminID)->find();
        $this->success('获取接待客服信息成功',$result);
    }
    //当前接待量-1
    public function deleteAdminMin()
    {
        $adminID = $this->request->param('adminID');
        $currentReception =  Db::table('admins')->where('A_ID',$adminID)->value('currentReception');
        $currentReceptionNew = $currentReception-1;
        Db::table('admins')->where('A_ID',$adminID)->update(['currentReception'=>$currentReceptionNew]);
	$this->success('获取接待客服信息成功',$currentReceptionNew);
    }

    //当前接待量+1,累计会话量+1
    public function addAdminMin()
    {
        $adminID = $this->request->param('adminID');
        $currentReception =  Db::table('admins')->where('A_ID',$adminID)->value('currentReception');
	$allReception =  Db::table('admins')->where('A_ID',$adminID)->value('allReception');
        $currentReceptionNew = $currentReception+1;
	$allReceptionNew = $allReception +1;
        Db::table('admins')->where('A_ID',$adminID)->update(['currentReception'=>$currentReceptionNew,'allReception'=>$allReceptionNew]);
        $this->success('获取接待客服信息成功',$currentReceptionNew);
    }

    //获取用户的资料
    public function getUserDate()
    {
        $fGroup = array();
        $group =  $this->request->param('userID');
        $group = htmlspecialchars_decode(stripslashes($group));
	$data = (array)(json_decode($group));
//	$tyepe = gettype($data);
        $result =  Db::table('users')->where('U_ID','in',$data)->select();
        $this->success('获取接待用户信息成功',$result);
    }
 
}
