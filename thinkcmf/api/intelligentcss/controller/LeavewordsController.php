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

class LeavewordsController extends RestBaseController
{
	public function untreated(){
		$result = Db::table("adminleavewords")->where('L_ProcessingState','未处理')->select();
		$this->success('获取未处理留言成功',$result);
	}

	public function processed(){
		$result = Db::table("adminleavewords")->where('L_ProcessingState','已处理')->select();
		$this->success('获取已处理留言成功',$result);		
	}
	public function searchUntreatedLeave(){
		$name = $this->request->param('name');
		$result = Db::table("adminleavewords")->where('L_Name',$name)->where('L_ProcessingState','未处理')->select();
		$this->success('获取用户未处理留言成功',$result);		
	}
	public function searchProcessedLeave(){
		$name = $this->request->param('name');
		$result = Db::table("adminleavewords")->where('L_Name',$name)->where('L_ProcessingState','已处理')->select();
		$this->success('获取用户已处理留言成功',$result);		
	}
	public function editLeaveWord(){
		$id = $this->request->param('id');
		$result = Db::table("adminleavewords")->where('L_ID',$id)->select();
		$this->success('获取用留言信息成功',$result);		
	}
	public function relayLeaveWord(){
		$id = $this->request->param('id');
		$token = $this->request->param('token');
		$answer = $this->request->param('answer');
		$adminID = Db::table('admintooken')->where('AT_Admin_Token',$token)->value('AT_Admin_ID');
		$adminName = Db::table('admins')->where('A_ID',$adminID)->value('A_Name');
		$now_time = time();
		if(empty($answer)||$answer=='空'){
			$this->error('回复内容不能为空');
		}
        $rowLeave = array(
        	'L_ServerName' => $adminName,
            'L_Reply' => $answer,  
            'L_ProcessingState' => '已处理',         
            'L_ReplyTime' => $now_time,
        );
		Db::table("adminleavewords")->where('L_ID',$id)->update($rowLeave);
		Db::table("leavewords")->where('L_ID',$id)->update($rowLeave);
		$this->success('回复留言成功');
	}
	public function deleteLeaveWordOne(){
		$deleteoneID = $this->request->param('deleteoneID');
		Db::table('adminleavewords')->where('L_ID',$deleteoneID)->delete();
		$this->success('删除成功!');
	}
    public function deletesLeaves(){
        $id = $this->request->param();
        Db::table('adminleavewords')->where('L_ID','in',$id)->delete();
        $this->success('删除成功!');
    }
    public function searchUntreated(){
		$searchLeaveText = $this->request->param('searchLeaveText');	
		if(empty($searchLeaveText)){
			$result = Db::table("adminleavewords")->where('L_ProcessingState','未处理')->select();
			$this->error('搜索内容不能为空！',$result);
		}		
        $result = Db::table('adminleavewords')->where('L_Name', 'like','%'.$searchLeaveText.'%')->whereOr('L_Details', 'like','%'.$searchLeaveText.'%')->where('L_ProcessingState','未处理')->select(); 
        if(count($result)==0){
        	$this->error('没有搜索到相关内容！',$result);
        }
        $this->success('成功获取未处理留言!',$result);        	
    }
    public function searchProcessed(){
		$searchLeaveText = $this->request->param('searchLeaveText');	
		if(empty($searchLeaveText)){
			$result = Db::table("adminleavewords")->where('L_ProcessingState','未处理')->select();
			$this->error('搜索内容不能为空！',$result);
		}		
        $result = Db::table('adminleavewords')->where('L_Name', 'like','%'.$searchLeaveText.'%')->whereOr('L_Details', 'like','%'.$searchLeaveText.'%')->whereOr('L_ServerName', 'like','%'.$searchLeaveText.'%')->whereOr('L_Reply', 'like','%'.$searchLeaveText.'%')->where('L_ProcessingState','已处理')->select(); 
        if(count($result)==0){
        	$this->error('没有搜索到相关内容！',$result);
        }
        $this->success('成功获取已处理留言!',$result);        	
    }
}