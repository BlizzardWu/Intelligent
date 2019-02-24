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

class AccountController extends RestBaseController
{	
	public function validates()
	{
		$validate = new Validate([
			'username' => 'require',
			'password' => 'require',
			'email'    => 'require',
			'sex'      => 'require',
			'level'    => 'require',

			'groupname' => 'require',
			'newname'   => 'require',
			
			'rolename'  => 'require',
			'roledescription' => 'require',
			'roletype'  => 'require',
			'newrole'   => 'require',
			'newdescription' => 'require'

		]);
	}
    
    public function allDate()
    {
		Db::execute("CREATE OR REPLACE VIEW allDate AS SELECT admins.A_ID,admins.A_Name,admins.A_Email,admins.A_Maximum,admins.A_Credibility,admins.A_Experience,admins.A_Attitude,groups.G_Name,state.S_Name,accountstatus.AS_Name, rolestypes.RT_Name FROM admins,groups,state,accountstatus,rolestypes WHERE admins.A_Group = groups.G_ID  AND admins.A_StateID = state.S_ID AND admins.A_CheckStatues = accountstatus.AS_ID AND admins.A_Level = rolestypes.RT_ID GROUP BY A_ID");
		$result =Db::table('allDate')->select();
		$this->success('数据接受成功！' ,$result);
    }
    
    public function groupDate()
    {

    	Db::execute("CREATE OR REPLACE VIEW allgroup AS SELECT admins.A_ID,admins.A_Name,groups.G_Name,groups.G_ID  FROM admins right join groups on admins.A_Group = groups.G_ID");
    	Db::execute("CREATE OR REPLACE VIEW cardsbody AS SELECT G_ID,G_Name,GROUP_CONCAT(`A_Name` SEPARATOR ';\n') A_Name  FROM `allgroup` GROUP BY G_Name ,G_ID");
    	//CREATE OR REPLACE VIEW allgroup AS SELECT admins.A_ID,admins.A_Name,groups.G_Name FROM admins,groups WHERE admins.A_Group = groups.G_ID GROUP BY A_ID
    	//$result =Db::execute("select A_Name,G_Name from allgroup where G_Name!='未分组'");
    	//SELECT G_Name,GROUP_CONCAT(`A_Name` SEPARATOR ',') haha  FROM `allgroup` GROUP BY G_Name
    	$result=Db::table('cardsbody')->where('G_Name','<>','未分组')->column('*','G_ID');
    	$result1=Db::table('cardsbody')->where('G_Name','=','未分组')->column('*','G_ID');
    	$this->success('数据接受成功！' ,['cardsbody'=>$result,'defaultcard'=>$result1]);
    }

    public function roleDate()
    {
    	Db::execute("CREATE OR REPLACE VIEW allRole AS SELECT rolesname.R_ID,rolesname.R_Name,rolestypes.RT_Name,rolesname.R_Description  FROM rolesname,rolestypes WHERE rolesname.R_Type = rolestypes.RT_ID GROUP BY R_ID");
    	$result=Db::table('allRole')->select();
    	$this->success('数据接受成功！' ,$result);
    }
    public function publicGroupDate()
    {
		$validate = new Validate([
			'group' => 'require',
		]);
		$validate->message([
			'group' => '无法获取到组名！请重试！',
		]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $result = Db::table('allgroup')->where('G_Name','<>',$data['group'])->column('A_Name');

        // $result1 = Db::table('allgroup')->where('G_Name','未分组')->column('A_Name');
        // $result3 = Db::table('allgroup')->where('G_Name',['=', $data['group']], ['=', '未分组'], 'or')->column('*','G_ID');
        $this->success('接受组名成功！' ,$result);

    }

	public function findgroupname(){
		$data = $this->request->param();
		$id = intval(reset($data));
		$name = Db::table('groups')->where('G_ID',$id)->find();
		$name = $name['G_Name'];
		return $name;
	}

	//新增客服
	public function addAccount()
	{
		$validate = new Validate([
			'username' => 'require',
			'password' => 'require',
			'email'    => 'require',
			'role'    => 'require',
			'maximum' => 'require|number'
		]);
	#	$validate = $this->validates();
		$validate->message([
			'username.require' => '请输入邮箱或用户名!',
			'password.require' => '请输入您的密码！',
			'email.require'            => '请输入您的邮箱！',
			'role.require'             => '请选择您的身份！',
			'maximum.require'		   => '请输入最大接待值！',
			'maximum.number' => '只能是数字',
		]);
		$data = $this->request->param();
		if (!$validate->check($data)) {
	            	$this->error($validate->getError());
        	}

    	if (!$this->valid_email($data['email'])) {
      		$this->error("请输入正确的邮箱！"); 
    	}
	$checkEmail = Db::table('admins')->where('A_Email',$data['email'])->select();
	if(count($checkEmail)!=0){
		$this->error('该邮箱已被使用');
	}
    	if($data['role']=='管理员'){
    		$data['role'] = 2;
    	}elseif($data['role']=='客服'){
    		$data['role'] = 3;
    	}
        $adminQuery = Db::table("admins");
		$adminQuery = $adminQuery->where('A_Name', $data['username']);
		$findadmin = $adminQuery->find();
		$ip = $_SERVER["REMOTE_ADDR"];
		$now_time = strtotime(date('y-m-d h:i:s',time()));
		if (empty($findadmin))
		{
			$account = [
				'A_ImageSrc' => 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg',
				'A_Name'     => $data['username'],
				'A_Password' => sha1($data['password']),
				'A_Email'    => $data['email'],
				'A_Level'    => $data['role'],
				'A_Maximum'  => $data['maximum'],
				'A_Group'    => 0,
                		'A_FirmID' => 1,  //token解决后通过检查登录用户获取公司名字
                		'A_CheckStatues' => 1,
                		'A_Sex' => '0',
                		'A_Address' => 'null',
                		'A_Signature' => 'null',
                		'A_StateID' => 1,
                		'A_Credibility' => '100',
                		'A_Experience' => '100',
                		'A_Attitude' => '100',
                		'A_LastIP' => $ip,
                		'A_LastTime' => $now_time,
				'currentReception' => 0,
				'allReception' => 0,
				'allConversation' => 0,
				'CumulativeTime' => 0,
				//'A_Now' => 0,
                		'A_Createtime' => $now_time,
                		'A_Updatetime' => $now_time,
				];
		        $result = Db::table('admins')->insert($account);
			$new = Db::query("select * from admins order by A_ID DESC limit 1");
			$this->success("成功新增一名角色!",reset($new)['A_Name']);
		} else {
			$this->error("该用户名已被使用，请勿添加重复用户名");

		}	
	}

    private function valid_email($address) {
          $mode = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
  	if (preg_match($mode, $address)) {
   		 return true;
  	} else {
    		return false;
  	     }
    }
	
	//更新用户
	public function update(){
		$data = $this->request->param();
		$id = intval( reset($data));
		$result = Db::table('admins')->where('A_ID',$id)->find();
		$state = $result['A_CheckStatues'];
		$level = $result['A_Level'];
		$name = $result['A_Name'];
		$A_ID = $result['A_ID'];
		$A_Password = '***';
		$max = $result['A_Maximum'];
		$email = $result['A_Email'];
		$arr = array();
		if($state == 0){
			$stateType = '禁用';
		}else if($state == 1){
			$stateType = '正常';
		}else{
			$stateType = '未验证';
		}
		if($level == 1){
                        $levelType = '超级管理员';
                }else if($level == 2){
                        $levelType = '管理员';
                }else{
                        $levelType = '客服';
                }
		$arr = array(
			'A_ID' => $A_ID,
			'A_StateID' => $stateType,
			'A_Level' => $levelType,
			'A_Name' => $name,
			'A_Email' => $email,
			'A_Password' => $A_Password,
			'A_Maximum' => $max,
		);
		return $arr;
	}

	//更新用户处理
	public function updatedeal(){
		$validate = new Validate([
            		'name' => 'require',
            		'email' => 'require',
            		'password' => 'require',
			'state' => 'require',
			'level' => 'require',
			'max' => 'require'
        	]);
        	$validate->message([
            		'name.require' => '用户名不为空',
            		'email.require' => '邮箱不为空',
           		'password.require' => '初始密码',
			'state.require' => '账号状态不为空',
			'level.require' => '角色不为空',
			'max.require' => '最大接待量'
        	]);
        	$data = $this->request->param();
        	if(!$validate->check($data)){
            		$this->error($validate->getError());
        	}
		$id = intval($data['id']);
		$name = $data['name'];
		$email = $data['email'];
		$state = $data['state'];
		$max = intval($data['max']);
		$level = $data['level'];
		$password = $data['password'];
		if($state == '禁用'){
                        $state = 0;
                }else if($state == '正常'){
                        $state = 1;
                }else if($state == '未验证'){
                        $state = 2;
                }else{
                        $state = intval($data['state']);
                }
		if($level == '管理员'){
			$level = 2;
		}else if($level == '客服'){
			$level = 3;
		}else if($level == '超级管理员'){
			$level = 1;
		}else{
			$level = intval($data['level']);
		}
		if($password == '***'){
			$result = Db::table('admins')->where('A_ID',$id)->update(['A_Name'=>$name,'A_Email'=>$email,'A_CheckStatues'=>$state,'A_Level'=>$level,'A_Maximum'=>$max]);
		}else{
			$result = Db::table('admins')->where('A_ID',$id)->update(['A_Name'=>$name,'A_Email'=>$email,'A_Password'=>sha1($password),'A_CheckStatues'=>$state,'A_Level'=>$level,'A_Maximum'=>$max]);
		}
		if(count($result)){
			$this->success('修改成功');
		}
	}

	//重置分值
	public function reset(){
		$validate = new Validate([
                        'value' => 'require',
                ]);
                $validate->message([
                        'value.require' => '条件不足',
                ]);
                $data = $this->request->param();
                if(!$validate->check($data)){
                        $this->error($validate->getError());
                }
		$id = intval(reset($data));
		$result = Db::table('admins')->where('A_ID',$id)->update([
			'A_Credibility' => 100,
			'A_Experience' => 100,
			'A_Attitude' => 100
		]);
		if(count($result)){
			$this->success('重置分值成功');
		}
	}

	//删除账户
	public function delAccount()
	{
		$validate = new Validate([
                        'A_ID' => 'require',
                ]);
                $validate->message([
                        'A_ID.require' => '条件不足',
                ]);
                $data = $this->request->param();
                if(!$validate->check($data)){
                        $this->error($validate->getError());
                }
		$id = intval(reset($data));
		$name = Db::table('admins')->where('A_ID',$id)->find();
		$name = $name['A_Name'];
		Db::table('admins')->where('A_ID',$id)->delete();
		$this->success("用户名为". $name ."的账户已被删除");
	}

	//批量删除账号
	public function deletes(){
		$id = $this->request->param();
		$result = Db::table('admins')->where('A_ID','in',$id)->delete();
		if(count($result)){
			$this->success('删除成功');
		}
	}
	
	//搜索角色
	public function find(){
		$validate = new Validate([
            		'name' => 'require'
        	]);
        	$validate->message([
            		'name.require' => '请输入问题',
        	]);
        	$data = $this->request->param();
        	if(!$validate->check($data)){
            		$this->error($validate->getError());
        	}
		$keyword = $data['name'];
		$result = Db::table('allDate')->whereor('A_Name','like','%'.$keyword.'%')
					->whereor('A_Email','like','%'.$keyword.'%')
					->whereor('S_Name','like','%'.$keyword.'%')
					->whereor('AS_Name','like','%'.$keyword.'%')
					->whereor('RT_Name','like','%'.$keyword.'%')	
					->select();
		return $result;
	}

	//新建分组
	public function addgroup()
	{
		$validate = new Validate([
			'name' => 'require'
		]);
		$validate->message([
			'name.require' => '分组名称不能为空'
		]);
		$data = $this->request->param();
		if(!$validate->check($data)){
			$this->error($validate->getError());
		}
		$name = reset($data);
                $adminQuery = Db::table("groups")->where('G_Name', $name);
		$findadmin = $adminQuery->find();

		if (empty($findadmin))
		{		
			$newgroup = ['G_Name' => $name];
			Db::table('groups')->insert(['G_Name'=>$name]);
			$this->success("新建分组成功");
		} else {
			$this->error("该组名已被使用，请勿重复使用组名");
		}	
	}

	//更改组名
	public function updateGName()
	{
		$validate = new Validate([
                        'value' => 'require',
                ]);
                $validate->message([
                        'value' => '条件不足！',
                ]);
                $data = $this->request->param();
                if (!$validate->check($data)) {
                        $this->error($validate->getError());
                }
		$id = intval(reset($data));
		$name = Db::table('groups')->where('G_ID',$id)->find();
		$name = $name['G_Name'];
		return $name;
	}
	
	//更改组名后台处理
	public function updateGNamedeal(){
		$validate = new Validate([
			'id' => 'require',
                        'name' => 'require',
                ]);
                $validate->message([
			'id.require' => '条件不足',
                        'name.require' => '组名不能为空',
                ]);
        	$data = $this->request->param();
        	if (!$validate->check($data)) {
            		$this->error($validate->getError());
        	}
		$id = $data['id'];
		$name = $data['name'];
		$result = Db::table('groups')->where('G_Name',$name)->where('G_ID','<>',$id)->select();
		if(count($result)){
			$this->error('该组名已存在');
		}else{
			Db::table('groups')->where('G_ID',$id)->update(['G_Name'=>$name]);
			$this->success('更新成功');
		}
	}

	//删除分组
	public function deletegroup()
	{
		$validate = new Validate([
                        'name' => 'require',
                ]);
                $validate->message([
                        'name' => '条件不足',
                ]);
                $data = $this->request->param();
                if (!$validate->check($data)) {
                        $this->error($validate->getError());
                }
		$name = reset($data);
		$id = Db::table('groups')->where('G_Name',$name)->find();
		$id = $id['G_ID'];
		Db::table('admins')->where('A_Group',$id)->update(['A_Group'=>0]);
		$result = Db::table('groups')->where('G_Name',$name)->delete();
		if($result){
			$this->success('删除成功');
		}		
	}

	//查找分组
	public function findgroup(){
		 $validate = new Validate([
                        'name' => 'require',
                ]);
                $validate->message([
                        'name' => '问题不能为空！',
                ]);
        	$data = $this->request->param();
        	if (!$validate->check($data)) {
            		$this->error($validate->getError());
        	}
		$name = reset($data);
		$result = Db::table('cardsbody')->where('G_Name','like','%'.$name.'%')->select();
		return $result;
	}

	//编辑组员
	public function editGMember()
	{
		$validate = new Validate([
			'username' => 'require',
			'groupname' => 'require',
		]);
		$validate->message([
			'value2' => '无法获取移动信息！请重试！',
			'groupname' => '无法获取组名信息！请重试！',
		]);
        	$data = $this->request->param();
        	if (!$validate->check($data)) {
            		$this->error($validate->getError());
        	} 
        	$findgroupsid=Db::table("groups")
        		->where('G_Name',$data['groupname'])
        		->value('G_ID'); 
		$result=Db::table('admins')
			->where('A_Name','in',$data['username'])
			->update(['A_Group' => $findgroupsid]);
		$this->success('已经成功更改');
	}
	//新建角色
	public function addRole()
	{	
		$validate = new Validate([
                        'roletype' => 'require',
                        'rolename' => 'require',
			'roledescription' => 'require'
                ]);
                $validate->message([
                        'roletype.require' => '角色类型不为空',
                        'rolename.require' => '角色名称不为空',
			'roledescription.require' => '角色描述不为空'
                ]);
        	$data = $this->request->param();
        	if (!$validate->check($data)) {
            	$this->error($validate->getError());
        	}
		$rolename = $data['rolename'];
		$check = Db::table('rolesname')->where('R_Name',$rolename)->select();
		if(count($check)){
			$this->error('该角色名称已存在');
		}
		$roletype = $data['roletype'];
		$roledescription = $data['roledescription'];
		if($data['roletype']=='管理者'){
			$roletype = 2;
		}else{
			$roletype = 3;
		}
		$newrole = ['R_Name' => $rolename,
			 'R_Type'    => $roletype,
			 'R_Description' => $roledescription
			  ];
		Db::table('rolesname')->insert($newrole);
		$this->success("新建角色成功");
	}
	//删除角色
	public function deleterole()
	{
		$validate = new Validate([
			'id' => 'require'
		]);
		$validate->message([
			'id.require' => '没有删除条件'
		]);
		$data = $this->request->param();
		if(!$validate->check($data)){
			$this->error($validate->getError());
		}
		$id = intval(reset($data));
		Db::table('rolesname')->where('R_ID',$id)->delete();
		$this->success("角色名称为 ".$id."的角色已删除");
	}
	//更新角色
	public function updaterole(){
		$validate = new Validate([
                        'value' => 'require',
                ]);
                $validate->message([
                        'value.require' => '条件不足！',
                ]);
                $data = $this->request->param();
                if (!$validate->check($data)) {
                $this->error($validate->getError());
                }
		$id = intval(reset($data));
		$result = Db::table('rolesname')->where('R_ID',$id)->select();
		return $result;
	}
	//更新角色后台处理	
	public function updateroledeal()
	{
		$validate = new Validate([
			'value' => 'require',
                        'roletype' => 'require',
                        'rolename' => 'require',
                        'roledescription' => 'require'
                ]);
                $validate->message([
			'value.require' => '条件不足',
                        'roletype.require' => '角色类型不为空',
                        'rolename.require' => '角色名称不为空',
                        'roledescription.require' => '角色描述不为空'
                ]);
                $data = $this->request->param();
                if (!$validate->check($data)) {
                $this->error($validate->getError());
                }
		$id = intval($data['value']);
		$rolename = $data['rolename'];
		$roletype = intval($data['roletype']);
		$roledescription = $data['roledescription'];
		$updaterole = ['R_Name' => $rolename,
				'R_Type' => $roletype,
				'R_Description' => $roledescription];
		Db::table('rolesname')->where('R_ID',$id)->update($updaterole);
		$this->success("角色信息更改成功");
	}

}
