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
use think\Session;
use cmf\controller\RestBaseController;

// 指定允许其他域名访问    
header('Access-Control-Allow-Origin:*');    
// 响应类型    
header('Access-Control-Allow-Methods:POST');    
// 响应头设置    
header('Access-Control-Allow-Headers:x-requested-with,content-type'); 

class HistoryController extends RestBaseController
{
	//所有历史会话
    public function history(){
    	$data = Db::table('history')->select();
        return $this->transform($data);
    }

    public function transform($data){
        $arr = array();
        for($i=0;$i<count($data);$i++){
		$ini = $data[$i]['initiator'];
		$rec = $data[$i]['receiver'];
		$iniArr = explode(':',$ini);
		$id = intval(end($iniArr));
		if(reset($iniArr)=='用户'){
			$name = Db::table('users')->where('U_ID',$id)->field('U_Name')->find();
			$iniName = $name['U_Name'];	
		}else{
			$name = Db::table('admins')->where('A_ID',$id)->field('A_Name')->find();
                        $iniName = $name['A_Name'];
		}
		$recArr = explode(':',$rec);
                $id = intval(end($recArr));
                if(reset($recArr)=='用户'){
                        $name = Db::table('users')->where('U_ID',$id)->field('U_Name')->find();
                        $recName = $name['U_Name'];
                }else{
                        $name = Db::table('admins')->where('A_ID',$id)->field('A_Name')->find();
                        $recName = $name['A_Name'];
                }
            $arr[] = array(
                'H_ID' => $data[$i]['H_ID'],
                'startTime' => date("Y-m-d H:i:s",$data[$i]['startTime']),
                'endTime' => date("Y-m-d H:i:s",$data[$i]['endTime']),
                'initiator' => $iniName,
                'receiver' => $recName,
                'conversationTime' => $this->time2string($data[$i]['conversationTime']),
                'satisfaction' => $data[$i]['satisfaction']
            );
        }
        return $arr;
    }
	//查找用户相关会话信息	
	public function find(){
		$validate = new Validate([
                'find' => 'require'
        	]);
        	$validate->message([
            	'find.require' => '请输入问题',
        	]);
        	$data = $this->request->param();
        	if(!$validate->check($data)){
            	$this->error($validate->getError());
        	}
        	$find = $data['find'];
		$arr = [];
		$id2 = Db::table('admins')->where('A_Name','like','%'.$find.'%')->field('A_ID')->select();
		$id3 = Db::table('users')->where('U_Name','like','%'.$find.'%')->field('U_ID')->select();
		$id = [];
		for($i=0;$i<count($id2);$i++){
			array_push($id,'客服:'.$id2[$i]['A_ID']);
		}
		for($i=0;$i<count($id3);$i++){
                        array_push($id,'用户:'.$id3[$i]['U_ID']);
                }
		for($i=0;$i<count($id);$i++){
			$data = Db::table('history')->whereor('initiator',$id[$i])->whereor('receiver',$id[$i])->select();
			for($j=0;$j<count($data);$j++){
				array_push($arr,$data[$j]);
			}
		}
        	if(!count($arr)){
            		$data = [];
            		$this->error('无数据',$data);
        	}else{
			$data = $this->transform($arr);
            		$this->success('查找成功',$data);
        	}
	}	

    public function time(){
    	$data = $this->request->param();
    	if(empty($data['time'])){
    		if(empty($data['initiator'])){
    			if(empty($data['satisfaction'])){

    			}else{
    				$n = strlen($data['satisfaction']);
    				switch ($n) {
    					case '1':
    						$result = Db::table('history')->where('satisfaction','>=',90)->where('satisfaction','<=',100)->select();
    						return $this->transform($result);
    						break;
    					case '2':
    						$result = Db::table('history')->where('satisfaction','>=',80)->where('satisfaction','<',90)->select();
    						return $this->transform($result);
    						break;
    					case '3':
						$result = Db::table('history')->where('satisfaction','>=',60)->where('satisfaction','<',80)->select();
						return $this->transform($result);
						break;
					case '4':
						$result = Db::table('history')->where('satisfaction','<',60)->select();
						return $this->transform($result);
						break;
					case '5':
						$result = Db::table('history')->where('satisfaction','>',100)->select();
                                                return $this->transform($result);
						break;
    				}
    			}
    		}else{
    			if(empty($data['satisfaction'])){
    				$n = strlen($data['initiator']);
    				switch ($n) {
    					case '1':
    						$result = Db::table('history')->where('conversationTime','between',[0,300])->select();
    						return $this->transform($result);
    						break;
    					case '2':
    						$result = Db::table('history')->where('conversationTime','between',[300,600])->select();
    						return $this->transform($result);
    						break;
    					case '3':
						$result = Db::table('history')->where('conversationTime','>',600)->select();
						return $this->transform($result);
						break;
    				}
    			}else{
    				$num = strlen($data['satisfaction']);
    				$n = strlen($data['initiator']);
    				if($n == 1){
    					switch ($num) {
	    					case '1':
    						$result = Db::table('history')->where('conversationTime','between',[0,300])->where('satisfaction','>=',90)->where('satisfaction','<=',100)->select();
	    						return $this->transform($result);
	    						break;
	    					case '2':
	    						$result = Db::table('history')->where('conversationTime','between',[0,300])->where('satisfaction','>=',80)->where('satisfaction','<',90)->select();
	    						return $this->transform($result);
	    						break;
	    					case '3':
							$result = Db::table('history')->where('conversationTime','between',[0,300])->where('satisfaction','>=',60)->where('satisfaction','<',80)->select();
							return $this->transform($result);
							break;
						case '4':
							$result = Db::table('history')->where('conversationTime','between',[0,300])->where('satisfaction','<',60)->select();
							return $this->transform($result);
							break;
						case '5':
                                                        $result = Db::table('history')->where('conversationTime','between',[0,300])->where('satisfaction','>',100)->select();
                                                        return $this->transform($result);
                                                        break;
    					}
    				}else if($n == 2){
    					switch ($num) {
	    					case '1':
    						$result = Db::table('history')->where('conversationTime','between',[300,600])->where('satisfaction','>=',90)->where('satisfaction','<=',100)->select();
	    						return $this->transform($result);
	    						break;
	    					case '2':
	    						$result = Db::table('history')->where('conversationTime','between',[300,600])->where('satisfaction','>=',80)->where('satisfaction','<',90)->select();
	    						return $this->transform($result);
	    						break;
	    					case '3':
							$result = Db::table('history')->where('conversationTime','between',[300,600])->where('satisfaction','>=',60)->where('satisfaction','<',80)->select();
							return $this->transform($result);
							break;
						case '4':
							$result = Db::table('history')->where('conversationTime','between',[300,600])->where('satisfaction','<',60)->select();
							return $this->transform($result);
							break;
						 case '5':
                                                        $result = Db::table('history')->where('conversationTime','between',[300,600])->where('satisfaction','>',100)->select();
                                                        return $this->transform($result);
                                                        break;
    					}
    				}else if($n == 3){
    					switch ($num) {
	    					case '1':
    						$result = Db::table('history')->where('conversationTime','>',600)->where('satisfaction','>=',90)->where('satisfaction','<=',100)->select();
	    						return $this->transform($result);
	    						break;
	    					case '2':
	    						$result = Db::table('history')->where('conversationTime','>',600)->where('satisfaction','>=',80)->where('satisfaction','<',90)->select();
	    						return $this->transform($result);
	    						break;
	    					case '3':
							$result = Db::table('history')->where('conversationTime','>',600)->where('satisfaction','>=',60)->where('satisfaction','<',80)->select();
							return $this->transform($result);
							break;
						case '4':
							$result = Db::table('history')->where('conversationTime','>',600)->where('satisfaction','<',60)->select();
							return $this->transform($result);
							break;
						 case '5':
                                                        $result = Db::table('history')->where('conversationTime','>',600)->where('satisfaction','>',100)->select();
                                                        return $this->transform($result);
                                                        break;
    					}
    				}
    			}
    		}
    	}else{
    		if(empty($data['initiator'])){
    			if(empty($data['satisfaction'])){
    				$startTime = strtotime($data['startTime']);
			    	$endTime = strtotime($data['endTime']);
					$result = Db::table('history')->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
					return $this->transform($result);
    			}else{
    				$n = strlen($data['satisfaction']);
    				$startTime = strtotime($data['startTime']);
			    	$endTime = strtotime($data['endTime']);
    				switch ($n) {
    					case '1':
    						$result = Db::table('history')->where('satisfaction','>=',90)->where('satisfaction','<=',100)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
    						return $this->transform($result);
    						break;
    					case '2':
    						$result = Db::table('history')->where('satisfaction','>=',80)->where('satisfaction','<',90)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
    						return $this->transform($result);
    						break;
    					case '3':
						$result = Db::table('history')->where('satisfaction','>=',60)->where('satisfaction','<',80)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
						return $this->transform($result);
						break;
					case '4':
						$result = Db::table('history')->where('satisfaction','<',60)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
						return $this->transform($result);
						break;
					case '5':
                                                $result = Db::table('history')->where('satisfaction','>',100)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
                                                return $this->transform($result);
                                                break;
    				}
    			}
    		}else{
    			if(empty($data['satisfaction'])){
    				$n = strlen($data['initiator']);
    				$startTime = strtotime($data['startTime']);
			    	$endTime = strtotime($data['endTime']);
    				switch ($n) {
    					case '1':
    						$result = Db::table('history')->where('conversationTime','between',[0,300])->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
    						return $this->transform($result);
    						break;
    					case '2':
    						$result = Db::table('history')->where('conversationTime','between',[300,600])->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
    						return $this->transform($result);
    						break;
    					case '3':
						$result = Db::table('history')->where('conversationTime','>',600)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
						return $this->transform($result);
						break;
    				}
    			}else{
    				$num = strlen($data['satisfaction']);
    				$n = strlen($data['initiator']);
    				$startTime = strtotime($data['startTime']);
			    	$endTime = strtotime($data['endTime']);
    				if($n == 1){
    					switch ($num) {
	    					case '1':
    						$result = Db::table('history')->where('conversationTime','between',[0,300])->where('satisfaction','>=',90)->where('satisfaction','<=',100)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
	    						return $this->transform($result);
	    						break;
	    					case '2':
	    						$result = Db::table('history')->where('conversationTime','between',[0,300])->where('satisfaction','>=',80)->where('satisfaction','<',90)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
	    						return $this->transform($result);
	    						break;
	    					case '3':
							$result = Db::table('history')->where('conversationTime','between',[0,300])->where('satisfaction','>=',60)->where('satisfaction','<',80)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
							return $this->transform($result);
							break;
						case '4':
							$result = Db::table('history')->where('conversationTime','between',[0,300])->where('satisfaction','<',60)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
							return $this->transform($result);
							break;
						case '5':
                                                        $result = Db::table('history')->where('conversationTime','between',[0,300])->where('satisfaction','>',100)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
                                                        return $this->transform($result);
                                                        break;
    					}
    				}else if($n == 2){
    					switch ($num) {
	    					case '1':
    						$result = Db::table('history')->where('conversationTime','between',[300,600])->where('satisfaction','>=',90)->where('satisfaction','<=',100)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
	    						return $this->transform($result);
	    						break;
	    					case '2':
	    						$result = Db::table('history')->where('conversationTime','between',[300,600])->where('satisfaction','>=',80)->where('satisfaction','<',90)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
	    						return $this->transform($result);
	    						break;
	    					case '3':
							$result = Db::table('history')->where('conversationTime','between',[300,600])->where('satisfaction','>=',60)->where('satisfaction','<',80)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
							return $this->transform($result);
							break;
						case '4':
							$result = Db::table('history')->where('conversationTime','between',[300,600])->where('satisfaction','<',60)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
							return $this->transform($result);
							break;
						case '5':
                                                        $result = Db::table('history')->where('conversationTime','between',[300,600])->where('satisfaction','>',100)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
                                                        return $this->transform($result);
                                                        break;
    					}
    				}else if($n == 3){
    					switch ($num) {
	    					case '1':
    						$result = Db::table('history')->where('conversationTime','>',600)->where('satisfaction','>=',90)->where('satisfaction','<=',100)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
	    						return $this->transform($result);
	    						break;
	    					case '2':
	    						$result = Db::table('history')->where('conversationTime','>',600)->where('satisfaction','>=',80)->where('satisfaction','<',90)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
	    						return $this->transform($result);
	    						break;
	    					case '3':
							$result = Db::table('history')->where('conversationTime','>',600)->where('satisfaction','>=',60)->where('satisfaction','<',80)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
							return $this->transform($result);
							break;
						case '4':
							$result = Db::table('history')->where('conversationTime','>',600)->where('satisfaction','<',60)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
							return $this->transform($result);
							break;
						case '5':
                                                        $result = Db::table('history')->where('conversationTime','>',600)->where('satisfaction','>',100)->where('startTime','>',$startTime)->where('startTime','<',$endTime)->select();
                                                        return $this->transform($result);
                                                        break;
    					}
    				}
    			}
    		}
    	}
    }

    public function delete(){
    	$data = $this->request->param();
    	$H_ID = intval(reset($data));
    	$result = Db::table('history')->where('H_ID',$H_ID)->delete();
    	if($result){
    		$this->success('删除成功');
    	}else{
    		$this->error('删除失败');
    	}
    }

    public function deletes(){
    	$data = $this->request->param();
    	$result = Db::table('history')->where('H_ID','in',$data)->delete();
    	if($result){
    		$this->success('删除成功');
    	}else{
    		$this->error('删除失败');
    	}
    }

    function time2string($second){
		// $day = floor($second/(3600*24));
		// $second = $second%(3600*24);
		$hour = floor($second/3600);
		$second = $second%3600;
		$minute = floor($second/60);
		$second = $second%60;
		return $hour.'小时'.$minute.'分'.$second.'秒';
	}
}
