<?php
namespace api\intelligentcss\controller;

use cmf\controller\RestBaseController;
use think\Db;
// 指定允许其他域名访问    
header('Access-Control-Allow-Origin:*');    
// 响应类型    
header('Access-Control-Allow-Methods:POST');    
// 响应头设置    
header('Access-Control-Allow-Headers:x-requested-with,content-type'); 
class DatastatisticsController extends RestBaseController{
	public function first(){
		$data = Db::table('datastatistics')->where('D_ID',1)->select();
		$result = Db::table('admins')->where('A_StateID',1)->where('A_Level',3)->select();
		$num = count($result);
		$arr = array();
		$arr[] = array(
			'querynum' => $data[0]['querynum'],
			'waited' => $data[0]['waited'],
			'conversationAll' => $data[0]['conversationAll'],
			'waitedAll' => $data[0]['waitedAll'],
			'unaccessed' => $data[0]['unaccessed'],
			'satisfaction' => $data[0]['satisfaction'],
			'participatenum' => $data[0]['participatenum'],
			'service' => $num
		);
		return $arr;
	}

	public function activity(){
		$result = Db::table('admins a,state s')->where('a.A_StateID = s.S_ID')->where('a.A_Level','between',[2,3])->field('a.A_Name,s.S_Name,a.A_Credibility,a.A_Experience,a.A_Attitude,a.currentReception,a.allReception,a.allConversation,a.CumulativeTime')->order('a.A_Name asc')->select();
		$arr = array();
		for($i=0;$i<count($result);$i++){
			$RelativeSatisfaction = ($result[$i]['A_Credibility']*0.6+$result[$i]['A_Experience']*0.2+$result[$i]['A_Attitude']*0.2);
			$arr[] = array(
				'A_Name' => $result[$i]['A_Name'],
				'S_Name' => $result[$i]['S_Name'],
				'currentReception' => $result[$i]['currentReception'],
				'allReception' => $result[$i]['allReception'],
				'allConversation' => $result[$i]['allConversation'],
				'RelativeSatisfaction' => $RelativeSatisfaction,
				'CumulativeTime' => $this->time2string($result[$i]['CumulativeTime'])
			);
		}
		return $arr;
	}

	public function test($name,$start,$end){
		$arr = array();
		$result = Db::table('statistics')->where('S_Name',$name)->where('S_Time','>',$start)->where('S_Time','<=',$end)->select();
		if(count($result)){
			for($i=0;$i<count($result);$i++){
				array_push($arr,$result[$i]['S_Time']);		
			}
			$time = max($arr);
			$data = Db::table('statistics')->where('S_Name',$name)->where('S_Time',$time)->field('S_Num')->find();
			return $data['S_Num'];
		}else{
			return 0;
		}
	}

	public function statistics(){
		$paidui = array();
		$jieru = array();
		$weijie = array();
		$zong = array();
		$arr = array();
		for($i=0;$i<24;$i++){
			$start = strtotime(date("Y-m-d"),time())+60*60*$i;
			$end = strtotime(date("Y-m-d"),time())+60*60*($i+1);
			$data = $this->test('排队量', $start, $end);
			array_push($paidui, $data);
		}
		$arr[] = array(
			'name' => '排队量',
			'type' => 'line',
			'stack' => '总量',
			'data' => $paidui
		);
		for($i=0;$i<24;$i++){
			$start = strtotime(date("Y-m-d"),time())+60*60*$i;
			$end = strtotime(date("Y-m-d"),time())+60*60*($i+1);
			$data = $this->test('已接入会话量', $start, $end);
			array_push($jieru, $data);
		}
		$arr[] = array(
			'name' => '已接入会话量',
			'type' => 'line',
			'stack' => '总量',
			'data' => $jieru
		);
		for($i=0;$i<24;$i++){
			$start = strtotime(date("Y-m-d"),time())+60*60*$i;
			$end = strtotime(date("Y-m-d"),time())+60*60*($i+1);
			$data = $this->test('未接入会话量', $start, $end);
			array_push($weijie, $data);
		}
		$arr[] = array(
			'name' => '未接入会话量',
			'type' => 'line',
			'stack' => '总量',
			'data' => $weijie
		);
		for($i=0;$i<24;$i++){
			$start = strtotime(date("Y-m-d"),time())+60*60*$i;
			$end = strtotime(date("Y-m-d"),time())+60*60*($i+1);
			$data = $this->test('总会话量', $start, $end);
			array_push($zong, $data);
		}
		$arr[] = array(
			'name' => '总会话量',
			'type' => 'line',
			'stack' => '总量',
			'data' => $zong
		);

		return $arr;
	}

	function time2string($second){
		$day = floor($second/(3600*24));
		$second = $second%(3600*24);
		$hour = floor($second/3600);
		$second = $second%3600;
		$minute = floor($second/60);
		$second = $second%60;
		return $day.'天'.$hour.'小时'.$minute.'分'.$second.'秒';
	}
}
