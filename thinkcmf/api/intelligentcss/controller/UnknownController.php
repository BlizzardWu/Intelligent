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

class UnknownController extends RestBaseController
{
	public function allDate(){
		$result = Db::table('unknown')->select();
		$this->success('获取未知问题成功',$result);
	}
	public function updateQuick(){
        $validate = new Validate([
        	'id' => 'require',
            'response' => 'require',
        ]);
        $validate->message([
            'response.require' => '答案不能为空!',
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $result = Db::table('unknown')->where('Un_ID',$data['id'])->value('Un_Question');
        $rowQuick = array(
            'Q_Question' => $result,
            'Q_Answer' => $data['response'],  
            'Q_Count' => 0,         
            'Q_Createtime' => time(),
        );
		Db::table('question')->insert($rowQuick);
		Db::table('unknown')->where('Un_ID',$data['id'])->delete();
		$this->success('成功添加未知问题到知识库!');		
	}
	//全选删除
    public function deletes(){
        $id = $this->request->param();
        Db::table('unknown')->where('Un_ID','in',$id)->delete();
        $this->success('删除成功!');
    }
	public function deleteone()
	{
		$deleteoneID = $this->request->param('deleteoneID');
		Db::table('unknown')->where('Un_ID',$deleteoneID)->delete();
		$this->success('删除成功!');
	}
}