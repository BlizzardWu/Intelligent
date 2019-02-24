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

class ReplayController extends RestBaseController
{
//公共库
	public function publicReplayCl()
	{
		$result = Db::table('quickreplylibrary')->where('Q_Belongs',0)->select();		
		$this->success('获取成功',$result);
	}
	public function publiclibrary()
	{
		$result = Db::table('publiclibrary')->select();
		$this->success('获取成功',$result);
	}
	public function addQuickClassic()
	{
		$addQuickText = $this->request->param('addQuickText');
		if(empty($addQuickText)){
			$this->error('快捷分类不能为空');
		}
		$result = Db::table('quickreplylibrary')->where('Q_classification',$addQuickText)->where('Q_Belongs',0)->find();	
		if($result){
			$this->error('已经存在该快捷分类');
		}
		if(strlen($addQuickText)>16){
			$this->error('快捷分类长度不得大于16');
		}
		$now_time = time();
        $rowQuick = array(
            'Q_classification' => $addQuickText,
            'Q_Belongs' => 0,            
            'Q_Createtime' => $now_time,
            'Q_Updatetime' => $now_time,
        );
		Db::table('quickreplylibrary')->insert($rowQuick);
		$this->success('成功添加快捷分类!');
	}
	//删除公共库快捷分类
	public function deleteQuick()
	{
		$deleteQuickText = $this->request->param('deleteQuickText');
		Db::table('quickreplylibrary')->where('Q_classification',$deleteQuickText)->where('Q_Belongs',0)->delete();
	}
	//修改公共库的快捷分类
	public function editQuick()
	{
		$sEditQuickText = $this->request->param('sEditQuickText');
		$eEditQuickText = $this->request->param('eEditQuickText');
		$result = Db::table('quickreplylibrary')->where('Q_classification',$eEditQuickText)->where('Q_Belongs',0)->find();	
		if($result){
			$this->error('已经存在该快捷分类');
		}		
		if(strlen($eEditQuickText)>16){
			$this->error('快捷分类长度不得大于16');
		}
		Db::table('quickreplylibrary')->where('Q_classification',$sEditQuickText)->update(['Q_classification' =>  $eEditQuickText]);
		$this->success('成功修改快捷分类!');
	}
	//获取相关快捷分类的快捷词和答案
	public function getQuick()
	{
		$libraryTitle = $this->request->param('libraryTitle');
		$result = Db::table('quickreplylibrary')->where('Q_classification',$libraryTitle)->where('Q_Belongs',0)->value('Q_ID');
		$result1 = Db::table('publiclibrary')->where('P_Classify',$result)->select();
		$this->success('成功获取该快捷分类快捷词!',$result1);
	}
	//
	public function saveQuick()
	{
        $validate = new Validate([
            'classes' => 'require',
            'qucik' => 'require',
            'response' => 'require'
        ]);
        $validate->message([
            'classes.require' => '快捷分类不能为空!',
            'qucik.require' => '请输入快捷词!',
            'response.require' => '请输入回复内容！'
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        //寻找快捷分类对应的id
        $findClass = Db::table('quickreplylibrary')->where('Q_classification',$data['classes'])->value('Q_ID');
        $findQuick = Db::table('publiclibrary')->where('P_QuickWord',$data['qucik'])->where('P_Classify',$findClass)->find();	
		if($findQuick){
			$this->error('该快捷分类下已经存在该快捷词！');
		}
		$now_time = time();
        $rowQuick = array(
            'P_QuickWord' => $data['qucik'],
            'P_Reply' => $data['response'],  
            'P_Classify' => $findClass,         
            'P_CreateTime' => $now_time,
            'P_UpdateTime' => $now_time,
        );
		Db::table('publiclibrary')->insert($rowQuick);
		$this->success('成功添加快捷词及答复!');
	}
	//搜索快捷词或者回复内容
	public function searchQuick()
	{
		$searchQuickText = $this->request->param('searchQuickText');
		if(empty($searchQuickText)){
			$result = Db::table('publiclibrary')->select();
			$this->error('搜索内容不能为空！',$result);
		}		
        $result = Db::table('publiclibrary')->where('P_QuickWord', 'like','%'.$searchQuickText.'%')->whereOr('P_Reply', 'like','%'.$searchQuickText.'%')->select(); 
        if(count($result)==0){
        	$this->error('没有搜索到相关内容！',$result);
        }
        $this->success('成功获取快捷词和回复内容!',$result);    		
	}
	//删除快捷词
	public function deleteone()
	{
		$deleteoneID = $this->request->param('deleteoneID');
		Db::table('publiclibrary')->where('P_ID',$deleteoneID)->delete();
		$this->success('删除成功!');
	}

	//全选删除
    public function deletes(){
        $id = $this->request->param();
        Db::table('publiclibrary')->where('P_ID','in',$id)->delete();
        $this->success('删除成功!');
    }

    public function update(){
        $data = $this->request->param('A_ID');
        $result = Db::table('publiclibrary')->where('P_ID',$data)->find();
        // 
        $class = $result['P_Classify'];
        $findclass = Db::table('quickreplylibrary')->where('Q_ID',$class)->value('Q_classification');
        $this->success('查询成功!',['result'=>$result,'class'=>$findclass]);
    }

    //修改快捷词
    public function updateQuick(){
        $validate = new Validate([
        	'id' => 'require',
            'classes' => 'require',
            'qucik' => 'require',
            'response' => 'require'
        ]);
        $validate->message([
            'classes.require' => '快捷分类不能为空!',
            'qucik.require' => '请输入快捷词!',
            'response.require' => '请输入回复内容！'
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        //得到快捷分类的id
        $findClass = Db::table('quickreplylibrary')->where('Q_classification',$data['classes'])->where('Q_Belongs',0)->value('Q_ID');
        //得到快捷词，不能一样
        $findQuick = Db::table('publiclibrary')->where('P_QuickWord',$data['qucik'])->where('P_Classify',$findClass)->find();	
		if($findQuick){
			$this->error('该快捷分类下已经存在该快捷词！');
		}
		$now_time = time();
        $rowQuick = array(
            'P_QuickWord' => $data['qucik'],
            'P_Reply' => $data['response'],  
            'P_Classify' => $findClass,         
            'P_UpdateTime' => $now_time,
        );
		Db::table('publiclibrary')->where('P_ID',$data['id'])->update($rowQuick);
		$this->success('成功更改快捷词及答复!');
    }

//个人库
	public function personalReplayCl()
	{
		$token = $this->request->param('token');
		$adminID = Db::table('admintooken')->where('AT_Admin_Token',$token)->value('AT_Admin_ID');
		$result = Db::table('quickreplylibrary')->where('Q_Belongs',$adminID)->select();		
		$this->success('获取成功',$result);		
	}
	//删除个人库快捷分类
 	public function deletePersonallQuick()
	{
		$token = $this->request->param('token');
		$adminID = Db::table('admintooken')->where('AT_Admin_Token',$token)->value('AT_Admin_ID');
		$deleteQuickText = $this->request->param('deleteQuickText');
		Db::table('quickreplylibrary')->where('Q_classification',$deleteQuickText)->where('Q_Belongs',$adminID)->delete();
	}
	//修改个人库的快捷分类
	public function editPersonallQuick()
	{
		$token = $this->request->param('token');
		$adminID = Db::table('admintooken')->where('AT_Admin_Token',$token)->value('AT_Admin_ID');
		$sEditQuickText = $this->request->param('sEditQuickText');
		$eEditQuickText = $this->request->param('eEditQuickText');
		if(strlen($eEditQuickText)>16){
			$this->error('快捷分类长度不得大于16');
		}
		$result = Db::table('quickreplylibrary')->where('Q_classification',$eEditQuickText)->where('Q_Belongs',$adminID)->find();	
		if($result){
			$this->error('已经存在该快捷分类');
		}		
		Db::table('quickreplylibrary')->where('Q_classification',$sEditQuickText)->update(['Q_classification' =>  $eEditQuickText]);
		$this->success('成功修改快捷分类!');
	}
	//添加快捷库个人分类
	public function addPersonallQuickClassic()
	{
		$token = $this->request->param('token');
		$adminID = Db::table('admintooken')->where('AT_Admin_Token',$token)->value('AT_Admin_ID');
		$addQuickText = $this->request->param('addQuickText');
		if(empty($addQuickText)){
			$this->error('快捷分类不能为空');
		}
		$result = Db::table('quickreplylibrary')->where('Q_classification',$addQuickText)->where('Q_Belongs',$adminID)->find();	
		if($result){
			$this->error('已经存在该快捷分类');
		}
		if(strlen($addQuickText)>16){
			$this->error('快捷分类长度不得大于16');
		}
		$now_time = time();
        $rowQuick = array(
            'Q_classification' => $addQuickText,
            'Q_Belongs' => $adminID,            
            'Q_Createtime' => $now_time,
            'Q_Updatetime' => $now_time,
        );
		Db::table('quickreplylibrary')->insert($rowQuick);
		$this->success('成功添加快捷分类!');
	}
	//获取个人库快捷词
	public function personallibrary()
	{
		$token = $this->request->param('token');
		$adminID = Db::table('admintooken')->where('AT_Admin_Token',$token)->value('AT_Admin_ID');		
		$result = Db::table('personallibrary')->where('P_Belongs',$adminID)->select();
		$this->success('获取成功',$result);
	}
	//获取个人库相关快捷分类的快捷词和答案
	public function getPersonalQuick()
	{
		$token = $this->request->param('token');
		$adminID = Db::table('admintooken')->where('AT_Admin_Token',$token)->value('AT_Admin_ID');	
		$libraryTitle = $this->request->param('libraryTitle');
		$result = Db::table('quickreplylibrary')->where('Q_classification',$libraryTitle)->where('Q_Belongs',$adminID)->value('Q_ID');
		$result1 = Db::table('personallibrary')->where('P_Classify',$result)->select();
		$this->success('成功获取该快捷分类快捷词!',$result1);
	}
	//搜索个人库快捷词或者回复内容
	public function searchPersonalQuick()
	{
		$searchQuickText = $this->request->param('searchQuickText');
		$token = $this->request->param('token');
		$adminID = Db::table('admintooken')->where('AT_Admin_Token',$token)->value('AT_Admin_ID');			
		if(empty($searchQuickText)){
			$result = Db::table('personallibrary')->where('P_Belongs',$adminID)->select();
			$this->error('搜索内容不能为空！',$result);
		}		
        $result = Db::table('personallibrary')->where('P_QuickWord', 'like','%'.$searchQuickText.'%')->whereOr('P_Reply', 'like','%'.$searchQuickText.'%')->where('P_Belongs',$adminID)->select(); 
        if(count($result)==0){
        	$this->error('没有搜索到相关内容！',$result);
        }
        $this->success('成功获取快捷词和回复内容!',$result);    		
	}
	//添加快捷词
	public function savePersonalQuick()
	{
        $validate = new Validate([
        	'token' => 'require',
            'classes' => 'require',
            'qucik' => 'require',
            'response' => 'require'
        ]);
        $validate->message([
            'classes.require' => '快捷分类不能为空!',
            'qucik.require' => '请输入快捷词!',
            'response.require' => '请输入回复内容！'
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $adminID = Db::table('admintooken')->where('AT_Admin_Token',$data['token'])->value('AT_Admin_ID');	
        //寻找快捷分类对应的id
        $findClass = Db::table('quickreplylibrary')->where('Q_classification',$data['classes'])->where('Q_Belongs',$adminID)->value('Q_ID');
  //       $findQuick = Db::table('personallibrary')->where('P_QuickWord',$data['qucik'])->where('P_Classify',$findClass)->where('P_Belongs',$adminID)->find();	
		// if($findQuick){
		// 	$this->error('该快捷分类下已经存在该快捷词！');
		// }
		$now_time = time();
        $rowQuick = array(
            'P_QuickWord' => $data['qucik'],
            'P_Reply' => $data['response'],  
            'P_Classify' => $findClass, 
            'P_Belongs' => $adminID,    
            'P_CreateTime' => $now_time,
            'P_UpdateTime' => $now_time,
        );
		Db::table('personallibrary')->insert($rowQuick);
		$this->success('成功添加快捷词及答复!');
	}
	//删除快捷词
	public function deletePersonallOne()
	{
		$deleteoneID = $this->request->param('deleteoneID');
		Db::table('personallibrary')->where('P_ID',$deleteoneID)->delete();
		$this->success('删除成功!');
	}

	//全选删除
    public function deletesPersonall(){
        $id = $this->request->param();
        Db::table('personallibrary')->where('P_ID','in',$id)->delete();
        $this->success('删除成功!');
    }
    public function updatePersonal()
    {
        $data = $this->request->param('A_ID');
        $result = Db::table('personallibrary')->where('P_ID',$data)->find();
        $class = $result['P_Classify'];
        $findclass = Db::table('quickreplylibrary')->where('Q_ID',$class)->value('Q_classification');
        $this->success('查询成功!',['result'=>$result,'class'=>$findclass]);   	
    }
    //修改快捷词
    public function updatePersonalQuick(){
        $validate = new Validate([
        	'token' => 'require',
        	'id' => 'require',
            'classes' => 'require',
            'qucik' => 'require',
            'response' => 'require'
        ]);
        $validate->message([
            'classes.require' => '快捷分类不能为空!',
            'qucik.require' => '请输入快捷词!',
            'response.require' => '请输入回复内容！'
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $adminID = Db::table('admintooken')->where('AT_Admin_Token',$data['token'])->value('AT_Admin_ID');	
        //得到快捷分类的id
        $findClass = Db::table('quickreplylibrary')->where('Q_classification',$data['classes'])->where('Q_Belongs',$adminID)->value('Q_ID');
        //得到快捷词，不能一样
  //       $findQuick = Db::table('personallibrary')->where('P_QuickWord',$data['qucik'])->where('P_Classify',$findClass)->where('P_Belongs',$adminID)->find();	
		// if($findQuick){
		// 	$this->error('该快捷分类下已经存在该快捷词！');
		// }
		$now_time = time();
        $rowQuick = array(
            'P_QuickWord' => $data['qucik'],
            'P_Reply' => $data['response'],  
            'P_Classify' => $findClass,         
            'P_UpdateTime' => $now_time,
        );
		Db::table('personallibrary')->where('P_ID',$data['id'])->update($rowQuick);
		$this->success('成功更改快捷词及答复!');
    }
}