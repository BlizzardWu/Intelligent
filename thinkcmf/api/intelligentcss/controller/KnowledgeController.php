<?php
namespace api\intelligentcss\Controller;
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

class PHPTree{  

    protected static $config = array(
        /* 主键 */
        'primary_key'   => 'id',
        /* 父键 */
        'parent_key'    => 'pid',
        /* 展开属性 */
        'expanded_key'  => 'expanded',
        /* 叶子节点属性 */
        'leaf_key'      => 'leaf',
        /* 孩子节点属性 */
        'children_key'  => 'children',
        /* 是否展开子节点 */
        'expanded'      => false
    );
    
    /* 结果集 */
    protected static $result = array();
    
    /* 层次暂存 */
    protected static $level = array();
    /**
     * @name 生成树形结构
     * @param array 二维数组
     * @return mixed 多维数组
     */
    public static function makeTree($data,$options=array() ){
        $dataset = self::buildData($data,$options);
        $r = self::makeTreeCore(0,$dataset,'normal');
        return $r;
    }
    
    /* 生成线性结构, 便于HTML输出, 参数同上 */
    public static function makeTreeForHtml($data,$options=array()){
    
        $dataset = self::buildData($data,$options);
        $r = self::makeTreeCore(0,$dataset,'linear');
        return $r;  
    }
    
    /* 格式化数据, 私有方法 */
    private static function buildData($data,$options){
        $config = array_merge(self::$config,$options);
        self::$config = $config;
        extract($config);

        $r = array();
        foreach($data as $item){
            $id = $item[$primary_key];
            $pid = $item[$parent_key];
            $r[$pid][$id] = $item;
        }
        
        return $r;
    }
    
    /* 生成树核心, 私有方法  */
    private static function makeTreeCore($index,$data,$type='linear')
    {
        extract(self::$config);
        foreach($data[$index] as $id=>$item)
        {
            if($type=='normal'){
                if(isset($data[$id]))
                {
                    $item[$expanded_key]= self::$config['expanded'];
                    $item[$children_key]= self::makeTreeCore($id,$data,$type);
                }
                else
                {
                    $item[$leaf_key]= true;  
                }
                $r[] = $item;
            }else if($type=='linear'){
                $pid = $item[$parent_key];
                self::$level[$id] = $index==0?0:self::$level[$pid]+1;
                $item['level'] = self::$level[$id];
                self::$result[] = $item;
                if(isset($data[$id])){
                    self::makeTreeCore($id,$data,$type);
                }
                
                $r = self::$result;
            }
        }
        return $r;
    }
}

class KnowledgeController extends RestBaseController
{
    public function question()
    {
        $arr = array();
        $data = Db::table('question')->order('Q_ID asc')->select();
        for($i=0;$i<count($data);$i++){
            $user = $this->getClass($data[$i]['Q_ID']);
            $class = implode('->', $user);
            if(count($user)){
                $arr[] = array(
                    'Q_ID' => $data[$i]['Q_ID'],
                    'Q_Question' => $data[$i]['Q_Question'],
                    'Q_Answer' => $data[$i]['Q_Answer'],
		    'PK_Name' => $data[$i]['PK_Name'],
                    'Q_Class' => $class,
                    'Q_SimilarPro' => $data[$i]['Q_SimilarPro'],
                    'Q_Createtime' => date("Y-m-d H:i:s",$data[$i]['Q_Createtime']),
                );
            }else{
                $arr[] = array(
                    'Q_ID' => $data[$i]['Q_ID'],
                    'Q_Question' => $data[$i]['Q_Question'],
		    'PK_Name' => $data[$i]['PK_Name'],
                    'Q_Answer' => $data[$i]['Q_Answer'],
                    'Q_Class' => '',
                    'Q_SimilarPro' => $data[$i]['Q_SimilarPro'],
                    'Q_Createtime' => date("Y-m-d H:i:s",$data[$i]['Q_Createtime']),
                );
            }
        }
        $this->success("问题页面",$arr);
    }

    
    function getClassName($array,$id){
        $arr = array();
        foreach($array as $v){
            if($v['id'] == $id){
                $arr[] = $v['pid'];
                $arr = array_merge($arr,$this->get_all_parents($array,$v['pid']));
            };
        };
        return $arr;
    }
    
    public function getClass($Q_ID){
        $id = Db::table('question')->where('Q_ID',$Q_ID)->field('id')->find();
        $data = Db::table('classify')->select();
        $user = $this->getClassName($data,$id['id']);
        array_push($user, $id['id']);
        $arr = array();
        for($i=0;$i<count($user);$i++){
            if($user[$i]==0){

            }else{
                array_push($arr, $user[$i]);
            }
        }
        $result = Db::table('classify')->where('id','in',$arr)->field('name')->select();
        $options = array();
        for($i=0;$i<count($result);$i++){
            array_push($options,$result[$i]['name']);
        }
        return $options;
    }	
    //查找问题
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
        $data = Db::table('question')->where('Q_Question','like','%'.$find.'%')->order('Q_ID asc')->select();
        if(!count($data)){
            $data = [];
            $this->error('无数据',$data);
        }else{
	   for($i=0;$i<count($data);$i++){
            	$user = $this->getClass($data[$i]['Q_ID']);
            	$class = implode('->', $user);
            	if(count($user)){
                $arr[] = array(
                    'Q_ID' => $data[$i]['Q_ID'],
                    'Q_Question' => $data[$i]['Q_Question'],
                    'Q_Answer' => $data[$i]['Q_Answer'],
                    'PK_Name' => $data[$i]['PK_Name'],
                    'Q_Class' => $class,
                    'Q_SimilarPro' => $data[$i]['Q_SimilarPro'],
                    'Q_Createtime' => date("Y-m-d H:i:s",$data[$i]['Q_Createtime']),
                );
            	}else{
                	$arr[] = array(
                    	'Q_ID' => $data[$i]['Q_ID'],
                    	'Q_Question' => $data[$i]['Q_Question'],
                    	'PK_Name' => $data[$i]['PK_Name'],
                    	'Q_Answer' => $data[$i]['Q_Answer'],
                    	'Q_Class' => '',
                    	'Q_SimilarPro' => $data[$i]['Q_SimilarPro'],
                    	'Q_Createtime' => date("Y-m-d H:i:s",$data[$i]['Q_Createtime']),
                );
            }
        }
            $this->success('查找成功',$arr);
        }
    }
	//查找知识点
    public function findPoint(){
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
        $data = Db::table('pointknowledge')->where('PK_Name','like','%'.$find.'%')->order('PK_ID asc')->select();
        if(!count($data)){
            $data = [];
            $this->error('无数据',$data);
        }else{
            $this->success('查找成功',$data);
        }
    }
	//添加、修改问题的知识点选择
    public function addPoint(){
        $validate = new Validate([
            'params' => 'require'
        ]);
        $validate->message([
            'params.require' => '请选择要添加的知识点'
        ]);
        $data = $this->request->param();
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $PK_ID = $data['params'];
        if(count($PK_ID)!=1){
            $this->error('只能选择一个知识点');
        }else{
            $result = Db::table('pointknowledge')->where('PK_ID',$PK_ID[0])->select();
            $this->success('添加成功',$result);
        }
    }
    //未分类的所有问题
    public function no_question(){
       $data = Db::table('question')->whereor('id','0')->whereor('id','null')->order('Q_ID asc')->select();
	for($i=0;$i<count($data);$i++){
            $user = $this->getClass($data[$i]['Q_ID']);
            $class = implode('->', $user);
            if(count($user)){
                $arr[] = array(
                    'Q_ID' => $data[$i]['Q_ID'],
                    'Q_Question' => $data[$i]['Q_Question'],
                    'Q_Answer' => $data[$i]['Q_Answer'],
                    'PK_Name' => $data[$i]['PK_Name'],
                    'Q_Class' => $class,
                    'Q_SimilarPro' => $data[$i]['Q_SimilarPro'],
                    'Q_Createtime' => date("Y-m-d H:i:s",$data[$i]['Q_Createtime']),
                );
            }else{
                $arr[] = array(
                    'Q_ID' => $data[$i]['Q_ID'],
                    'Q_Question' => $data[$i]['Q_Question'],
                    'PK_Name' => $data[$i]['PK_Name'],
                    'Q_Answer' => $data[$i]['Q_Answer'],
                    'Q_Class' => '',
                    'Q_SimilarPro' => $data[$i]['Q_SimilarPro'],
                    'Q_Createtime' => date("Y-m-d H:i:s",$data[$i]['Q_Createtime']),
                );
            }
        }
       if($arr){
            $this->success('未分类页面',$arr);
       }else{
            $this->error('无数据');
       }
    }
    //已分类的所有问题
    public function yes_question(){
       $data = Db::table('question')->where("id","<>","0")->order('Q_ID asc')->select();
	for($i=0;$i<count($data);$i++){
            $user = $this->getClass($data[$i]['Q_ID']);
            $class = implode('->', $user);
            if(count($user)){
                $arr[] = array(
                    'Q_ID' => $data[$i]['Q_ID'],
                    'Q_Question' => $data[$i]['Q_Question'],
                    'Q_Answer' => $data[$i]['Q_Answer'],
                    'PK_Name' => $data[$i]['PK_Name'],
                    'Q_Class' => $class,
                    'Q_SimilarPro' => $data[$i]['Q_SimilarPro'],
                    'Q_Createtime' => date("Y-m-d H:i:s",$data[$i]['Q_Createtime']),
                );
            }else{
                $arr[] = array(
                    'Q_ID' => $data[$i]['Q_ID'],
                    'Q_Question' => $data[$i]['Q_Question'],
                    'PK_Name' => $data[$i]['PK_Name'],
                    'Q_Answer' => $data[$i]['Q_Answer'],
                    'Q_Class' => '',
                    'Q_SimilarPro' => $data[$i]['Q_SimilarPro'],
                    'Q_Createtime' => date("Y-m-d H:i:s",$data[$i]['Q_Createtime']),
                );
            }
        }
       if(count($arr)){
        $this->success('已分类页面',$arr);
       }else{
            $this->error('无数据');
       }
    }
    //未分类的所有知识点
    public function no_point(){
       $data = Db::table('pointknowledge')->whereor('id','0')->whereor('id','null')->order('PK_ID asc')->select();
       if($data){
            $this->success('未分类页面',$data);
       }else{
            $this->error('无数据');
       }
    }
    //已分类的所有知识点
    public function yes_point(){
       $data = Db::table('pointknowledge')->where("id","<>","0")->order('PK_ID asc')->select();
       if($data){
        $this->success('已分类页面',$data);
       }else{
            $this->error('无数据');
       }
    }
    //知识点页面
    public function point()
    {
        $data = Db::table('pointknowledge')->select();
        $this->success('知识点页面',$data);
    }
    //添加问题
    public function insertdeal(){
        $data = $this->request->param();
        $validate = new Validate([
            'Q_Question' => 'require',
            'Q_Answer' => 'require',
        ]);
        $validate->message([
            'Q_Question.require' => '问题不能为空',
            'Q_Answer.require' => '答案不能为空',
        ]);
        $data = $this->request->param();
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        @$Q_SimilarPro = $data['Q_SimilarPro'];
        $arr = array();
        if($Q_SimilarPro!=""){
            array_push($arr, $Q_SimilarPro);
        }
	$other = next($data);
        for($i=0;$i<count($other);$i++){
            if($other[$i]!=""){
                array_push($arr, $other[$i]);
            }
        }
        $str = implode('--', $arr);
        $Q_Question = $data['Q_Question'];
        $Q_Answer = $data['Q_Answer'];
        $result = Db::table('question')->where('Q_Question',$Q_Question)->select();
        if(count($result)){
            $this->error('问题已存在');
        }else{
            @$PK_Name = $data['PK_Name'];
            $Q_Createtime = time();
            $row = array();
            $row['Q_Question'] = $Q_Question;
            $row['Q_SimilarPro'] = $str;
            $row['Q_Answer'] = $Q_Answer;
            $row['PK_Name'] = $PK_Name;

            @$str = implode(',', end($data));
            $a = explode(',', $str);
            $id = '';
            for($i=0;$i<count($a);$i++){
                if($i == (count($a)-1)){
                    $id = $a[$i];
                }
            }
            $row['id'] = $id;
            $row['Q_Createtime'] = $Q_Createtime;
            $result = Db::table('question')->insert($row);
            if($result){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
    }
    //删除问题
    public function delete()
    {
        $validate = new Validate([
            'Q_ID' => 'require',
        ]);
        $validate->message([
            'Q_ID.require' => 'ID不存在',
        ]);
        $data = $this->request->param();
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $Q_ID = $data['Q_ID'];
        $result = Db::table('question')->where('Q_ID',$Q_ID)->delete();
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    //删除知识点
    public function delete_point()
    {
        $validate = new Validate([
            'PK_ID' => 'require',
        ]);
        $validate->message([
            'PK_ID.require' => 'ID不存在'
        ]);
        $data = $this->request->param();
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $PK_ID = $data['PK_ID'];
	$name = Db::table('pointknowledge')->where('PK_ID',$PK_ID)->field('PK_Name')->find();
	$name = $name['PK_Name'];
	Db::table('question')->where('PK_Name',$name)->update(['PK_Name'=>'']);
        $result = Db::table('pointknowledge')->where('PK_ID',$PK_ID)->delete();
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    //批量删除问题
    public function deletes(){
	//$validate = new Validate([
        //    'Q_ID' => 'require',
        //]);
        //$validate->message([
        //    'Q_ID.require' => 'ID不存在',
        //]);
        $data = $this->request->param();
        //if(!$validate->check($data)){
        //    $this->error($validate->getError());
        //}
	//$data = reset($data);
        $result = Db::table('question')
        ->where('Q_ID','in',$data)->delete();
        if($result){
            $this->success('删除成功');
        }
        
    }
    //批量删除知识点
    public function deletes_point(){
        $data = $this->request->param();
	for($i=0;$i<count($data);$i++){
		$PK_ID = $data[$i];
		$name = Db::table('pointknowledge')->where('PK_ID',$PK_ID)->field('PK_Name')->find();
        	$name = $name['PK_Name'];
        	Db::table('question')->where('PK_Name',$name)->update(['PK_Name'=>'']);
	}
        $result = Db::table('pointknowledge')->where('PK_ID','in',$data)->delete();
        if($result){
            $this->success('删除成功');
        }
    }
    //更新问题
    public function update(){
        $validate = new Validate([
            'Q_ID' => 'require',
        ]);
        $validate->message([
            'Q_ID.require' => 'ID不存在',
        ]);
        $data = $this->request->param();
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $Q_ID = intval($data['Q_ID']);
        $data = Db::table('question')->where('Q_ID',$Q_ID)->select();
	$this->success('修改问题',$data);
    }
    //更新知识点
    public function update_point(){
        $validate = new Validate([
            'PK_ID' => 'require',
        ]);
        $validate->message([
            'PK_ID.require' => 'ID不存在',
        ]);
        $data = $this->request->param();
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $PK_ID = intval($data['PK_ID']);
        $data = Db::table('pointknowledge')->where('PK_ID',$PK_ID)->select();
        $this->success('更新知识点',$data);
    }
    //更新问题处理
    public function updateDeal()
    {
        $validate = new Validate([
            'Q_ID' => 'require',
            'Q_Question' => 'require',
            'Q_Answer' => 'require',
        ]);
        $validate->message([
            'Q_ID.require' => 'ID不存在',
            'Q_Question.require' => '问题不能为空',
            'Q_Answer.require' => '答案不能为空',
        ]);
        $data = $this->request->param();
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $Q_ID = $data['Q_ID'];
        $Q_Question = $data['Q_Question'];
        @$Q_SimilarPro = $data['Q_SimilarPro'];
        $Q_Answer = $data['Q_Answer'];
        @$PK_Name = $data['PK_Name'];
        $Q_Answer = $data['Q_Answer'];
        @$CK_Name = implode(',', end($data));
        $arr = explode(',', $CK_Name);
        $id = '';
        for($i=0;$i<count($arr);$i++){
            if($i == (count($arr)-1)){
                $id = $arr[$i];
            }
        }
        $Q_Updatetime = time();
        $result = Db::table('question')->where('Q_ID',$Q_ID)->update([
            'Q_Question' => $Q_Question,
            'Q_SimilarPro' => $Q_SimilarPro,
            'Q_Answer' => $Q_Answer,
            'PK_Name' => $PK_Name,
            'id' => $id
        ]);
        if(count($result)){
            $this->success('更新成功');
        }
    }
    //关联问题
    public function linkQuestion(){
        $validate = new Validate([
            'Q_ID' => 'requre',
            'PK_Name' => 'require'
        ]);
        $validate->message([
            'Q_ID.require' => 'Q_ID不能为空',
            'PK_Name.require' => '知识点不能为空'
        ]);
        $data = $this->request->param();
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $id = $data['params'];
        $PK_Name = $data['PK_Name'];
        $result = Db::table('question')->where('Q_ID','in',$id)->update([
            'PK_Name' => $PK_Name
        ]);
        if($result){
            $this->success('关联成功');
        }else{
            $this->error('你已经关联过此问题');
        }
    }
    //更新知识点处理
    public function update_pointDeal()
    {
        $validate = new Validate([
            'PK_ID' => 'require',
            'PK_Name' => 'require'
        ]);
        $validate->message([
            'PK_ID.require' => 'ID不能为空',
            'PK_Name.require' => '知识点不能为空'
        ]);
        $data = $this->request->param();
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $PK_ID = $data['PK_ID'];
        $PK_Name = $data['PK_Name'];
        $check = Db::table('pointknowledge')->where('PK_Name',$PK_Name)->select();
        $PK_Updatetime = time();
        $str = implode(',', end($data));
        $arr = explode(',', $str);
        $id = '';
        for($i=0;$i<count($arr);$i++){
            if($i == (count($arr)-1)){
                $id = $arr[$i];
            }
        }
        Db::table('pointknowledge')->where('PK_ID',$PK_ID)->update([
            'PK_Updatetime' => $PK_Updatetime,
            'PK_Name' => $PK_Name,
            'id' => $id
        ]);
        $this->success('更新成功');
    }
    
    public function add_knowledgeDeal(){
        $validate = new Validate([
            'PK_Name' => 'require'
        ]);
        $validate->message([
            'PK_Name.require' => '知识点不能为空'
        ]);
        $data = $this->request->param();
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $PK_Name = $data['PK_Name'];
        $result = Db::table('pointknowledge')->where('PK_Name',$PK_Name)->select();
        if(count($result)){
            $this->error('该知识点已存在');
        }else{
            $PK_Createtime = time();
            $PK_Updatetime = time();
            @$str = implode(',', end($data));
            $arr = explode(',', $str);
            $id = '';
            for($i=0;$i<count($arr);$i++){
                if($i == (count($arr)-1)){
                    $id = $arr[$i];
                }
            }
            $row = array();
            $row['PK_Name'] = $PK_Name;
            $row['PK_Createtime'] = $PK_Createtime;
            $row['PK_Updatetime'] = $PK_Updatetime;
            $row['id'] = $id;

            $result = Db::table('pointknowledge')->insert($row);
            if($result){
                $this->success('添加成功');
            }
        }
    }
    //列出所有知识分类
    public function classify(){
        $data = Db::table('classify')->select();
        if(count($data)=="0"){
            return $data;
        }else{
            $r = PHPTree::makeTree($data);
            return $r;
        }
    }
    //添加、修改知识分类
    public function addClassify(){
	$validate = new Validate([
            'id' => 'require',
		'name' => 'require',
		'pid' => 'require',
		'level' => 'require',
        ]);
        $validate->message([
            'id.require' => 'id不能为空',
		'name.require' => '名称不能为空',
		'pid.require' => '父级id不能为空',
		'level.require' => '等级不能为空'
        ]);
        $data = $this->request->param();
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $row = array();
        $id = $data['id'];
        $name = $data['name'];
        if(empty($name)){
            $this->error('分类不为空');
        }else{
            $level = intval($data['level']);
            $pid = intval($data['pid']);
            $check_id = Db::table('classify')->where('id',$id)->select();
            if(count($check_id)==0){
                $check = Db::table('classify')->where('name',$name)->where('pid',$pid)->where('level',$level)->select();
                if(count($check)){
                    $this->error('同级分类已存在');
                }else{
			$row['name'] = $name;
                    $row['label'] = $name;
                    $row['pid'] = $pid;
                    $row['usedit'] = 'false';
			$row['value'] = $id;
                    $row['level'] = $level;
                    $result = Db::table('classify')->insert($row);
                    if($result){
                        $getid = Db::table('classify')->where('name',$name)->where('level',$level)->field('id')->select();
                        $d = Db::table('classify')->where('id',$getid[0]['id'])->update(['value'=>$getid[0]['id']]);
                        $this->success('分类添加成功');
                    }
                }
            }else{
                $check2 = Db::table('classify')->where('name',$name)->where('pid',$pid)->where('id','<>',$id)->where('level',$level)->select();
                if(count($check2)){
                    $this->error('同级分类已存在');
                }else{
                    $result = Db::table('classify')->where('id',$id)->update(['name'=>$name,'label'=>$name]);
                    if(count($result)){
                        $this->success('修改成功');
                    }
                }
            }   
        }
    }
    //获取数据表classify的最大id
    public function maxexpandId(){
        $data = Db::table('classify')->select();
        $num = 0;
        for($i=0;$i<count($data);$i++){
            if($data[$i]['id']>=$num){
                $num = $data[$i]['id'];
            }
        }
        return $num;
    }
    //删除知识分类
    public function deleteclassify(){
	$validate = new Validate([
            'id' => 'require'
        ]);
        $validate->message([
            'id.require' => '要删除的知识分类ID不能为空'
        ]);
        $data = $this->request->param();
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $id = intval(end($data));
	$idArr = $this->test($id);  //获取所有下级分类
		$result = Db::table('question')->where('id','in',$idArr)->update(['id'=>0]);
		Db::table('pointknowledge')->where('id','in',$idArr)->update(['id'=>0]);
        $result = Db::table('classify')->where('id',$id)->delete();
        if($result){
            $this->success('删除成功');
        }
    }
    //查看某个知识分类下的所有问题
    //public function searchclassify(){
    //    $data = $this->request->param();
    //    $id = $data['id'];
    //    $pid = $data['pid'];
    //    if($pid=="0"){
    //        $result = Db::table('question')->where('id',$id)->select();
    //        return $result;
    //    }else{
    //        $result = Db::table('question')->where('id',$id)->select();
    //        return $result;
    //    }    
    //}

    function test($getid){
        $data = Db::table('classify')->select();
        $a = $this->getSonx($getid, $data);
        $id = Db::table('classify')->where('id',$getid)->field('id')->find();
        array_push($a, $id['id']);
        return $a;
    }


    function getSonx($id, $data){
        $returnData = array();
        foreach($data as $k=>$v){
            if($v['pid'] == $id){
                $returnData[]       = $v['id'];
                $a  = $this->getSonx($v['id'], $data);
                if($a){
                    $returnData = array_merge($returnData, $a);
                }
                //从最底层数据，向上合并。
                //for pid=5层，id:7,9是最底层了，getSonx()返回的是2个空array因为(没有子类)
                //结果：$returnData = array(7,9); 即$returnData[];
                //for pid=2层，id:5  合并刚才的7,9  $returnData = array(7,9);
                //结果：$returnData = array(5,7,9);
            }
        }
    
        return $returnData;
    }
    //查找某级知识分类的所有问题
    public function searchQuestion(){
	$validate = new Validate([
            'id' => 'require'
        ]);
        $validate->message([
            'id.require' => '条件不足！'
        ]);
        $getid = $this->request->param();
        if(!$validate->check($getid)){
            $this->error($validate->getError());
        }
        //$getid = $this->request->param();
        $id = $getid['id'];
        $a = $this->test($id);
        $data = Db::table('question')->where('id','in',$a)->select();
	$arr = array();
	for($i=0;$i<count($data);$i++){
            $user = $this->getClass($data[$i]['Q_ID']);
            $class = implode('->', $user);
            if(count($user)){
                $arr[] = array(
                    'Q_ID' => $data[$i]['Q_ID'],
                    'Q_Question' => $data[$i]['Q_Question'],
                    'Q_Answer' => $data[$i]['Q_Answer'],
                    'PK_Name' => $data[$i]['PK_Name'],
                    'Q_Class' => $class,
                    'Q_SimilarPro' => $data[$i]['Q_SimilarPro'],
                    'Q_Createtime' => date("Y-m-d H:i:s",$data[$i]['Q_Createtime']),
                );
            }else{
                $arr[] = array(
                    'Q_ID' => $data[$i]['Q_ID'],
                    'Q_Question' => $data[$i]['Q_Question'],
                    'PK_Name' => $data[$i]['PK_Name'],
                    'Q_Answer' => $data[$i]['Q_Answer'],
                    'Q_Class' => '',
                    'Q_SimilarPro' => $data[$i]['Q_SimilarPro'],
                    'Q_Createtime' => date("Y-m-d H:i:s",$data[$i]['Q_Createtime']),
                );
            }
        }
         $this->success('问题',$arr);
    }
    //查找某级知识分类的所有知识点
    public function searchPoint(){
	
        $getid = $this->request->param();
        $id = $getid['id'];
        $arr = $this->test($id);
        $data = Db::table('pointknowledge')->where('id','in',$arr)->select();
        $this->success('知识点',$data);
    }
	
	//五级分类的默认分类
    function get_all_parents($array,$id){
        $arr = array();
        foreach($array as $v){
            if($v['id'] == $id){
                $arr[] = $v['pid'];
                $arr = array_merge($arr,$this->get_all_parents($array,$v['pid']));
            };
        };
        return $arr;
    }
    //修改问题时显示的默认分类
    public function defaultOptions(){
        $data = $this->request->param();
        // return reset($data);
        $Q_ID = intval(reset($data));
        $id = Db::table('question')->where('Q_ID',$Q_ID)->field('id')->find();
        $data = Db::table('classify')->select();
        $user = $this->get_all_parents($data,$id['id']);
        array_push($user, $id['id']);
        $arr = array();
        for($i=0;$i<count($user);$i++){
            if($user[$i]==0){

            }else{
                array_push($arr, $user[$i]);
            }
        }
        $result = Db::table('classify')->where('id','in',$arr)->field('value')->select();
        $options = array();
        for($i=0;$i<count($result);$i++){
            array_push($options,$result[$i]['value']);
        }
        return $options;
    }
    //修改知识点时显示的默认分类
    public function defaultOptionsPoint(){
        $data = $this->request->param();
        $PK_ID = intval(reset($data));
        $id = Db::table('pointknowledge')->where('PK_ID',$PK_ID)->field('id')->find();
        $data = Db::table('classify')->select();
        $user = $this->get_all_parents($data,$id['id']);
        array_push($user, $id['id']);
        $arr = array();
        for($i=0;$i<count($user);$i++){
            if($user[$i]==0){

            }else{
                array_push($arr, $user[$i]);
            }
        }
        $result = Db::table('classify')->where('id','in',$arr)->field('value')->select();
        $options = array();
        for($i=0;$i<count($result);$i++){
            array_push($options,$result[$i]['value']);
        }
        return $options;
    }
	
    //excel问题导入
    public function excelinput()
    {
        $request = \think\Request::instance();
        $excel = request()->file('myfile')->getInfo();//excel为file中的name
        $name = $excel['name'];
        $ext = pathinfo($name)['extension'];
        if($ext == 'xls' || $ext == 'xlsx'){
            vendor("PHPExcel.PHPExcel.IOFactory");
            $objPHPExcel = \PHPExcel_IOFactory::load($excel['tmp_name']);//读取上传的文件
            $arrExcel = $objPHPExcel->getSheet(0)->toArray();//获取其中的数据
	    if(count($arrExcel)<=1){
		$this->error('未填写表格内容');
	    }
            // $word = $arrExcel[0];
            array_shift($arrExcel);

            $data = array();
            // $num = count($arrExcel);
            $Q_Createtime = time();
            $errorMsg = array();
            $error = 0;
            $success = 0;
            //$check = array();
            //foreach ($arrExcel as $key => $value){
            //    array_push($check, $arrExcel[$key][0]);
            //}
            foreach ($arrExcel as $key => $value) {
                $same = Db::table('question')->where('Q_Question',$arrExcel[$key][0])->select();
                if(count($arrExcel[$key])>=12){
                    $this->error('请按照格式填写');
                }else if(count($arrExcel[$key])<3){
			$error = $error + 1;
			array_push($errorMsg,$key+1);
			continue;
		}else if(strlen(trim($arrExcel[$key][0]))==0){
                    $error = $error + 1;
                    array_push($errorMsg, $key+1);
                    continue;
                }else if(count($same)){
                    $error = $error + 1;
                    array_push($errorMsg, $key+1);
                    continue;
                }else if(strlen(trim($arrExcel[$key][1]))==0){
                    $error = $error + 1;
                    array_push($errorMsg, $key+1);
                    continue;
                }else if(strlen(trim($arrExcel[$key][2]))==0){
                    $error = $error + 1;
                    array_push($errorMsg, $key+1);
                    continue;
                }
//else if(array_count_values($check)[$arrExcel[$key][0]]>=2){
  //                  $error = $error + 1;
    //                array_push($errorMsg, $key+1);
      //              continue;
        //        }
                else{
                    $id = 0;
                    $name = $arrExcel[$key][2];
                    $result = Db::table('classify')->where('name',$name)->where('pid',0)->select();
                    if(count($result)){
                        $getid = Db::table('classify')->where('name',$name)->where('pid',0)->find();
                        $id = $getid['id'];
                        if(strlen(trim($arrExcel[$key][3]))==0){
                            // return '1';
                        }else{
                            $result = Db::table('classify')->where('name',$arrExcel[$key][3])->where('pid',$id)->select();
                            if(count($result)){
                                $getid = Db::table('classify')->where('name',$arrExcel[$key][3])->where('pid',$id)->find();
                                $id = $getid['id'];
                                if(strlen(trim($arrExcel[$key][4]))==0){
                                    // return '211';
                                }else{
                                    $result = Db::table('classify')->where('name',$arrExcel[$key][4])->where('pid',$id)->select();
                                    if(count($result)){
                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][4])->where('pid',$id)->find();
                                        $id = $getid['id'];
                                        if(strlen(trim($arrExcel[$key][5]))==0){
                                            // return '211';
                                        }else{
                                            $result = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->select();
                                            if(count($result)){
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                if(strlen(trim($arrExcel[$key][6]))==0){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }else{
                                                Db::table('classify')->insert(['name'=>$arrExcel[$key][5],'label'=>$arrExcel[$key][5],'pid'=>$id,'level'=>4,'usedit'=>'false','value'=>0]);
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                Db::table('classify')->where('name',$arrExcel[$key][5])->where('value',0)->update(['value'=>$id]);
                                                if(strlen(trim($arrExcel[$key][6]))){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }
                                        }
                                    }else{
                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][4],'label'=>$arrExcel[$key][4],'pid'=>$id,'level'=>3,'usedit'=>'false','value'=>0]);
                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][4])->where('pid',$id)->find();
                                        $id = $getid['id'];
                                        Db::table('classify')->where('name',$arrExcel[$key][4])->where('value',0)->update(['value'=>$id]);
                                        if(strlen(trim($arrExcel[$key][5]))==0){
                                            // return '211';
                                        }else{
                                            $result = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->select();
                                            if(count($result)){
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                if(strlen(trim($arrExcel[$key][6]))){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }else{
                                                Db::table('classify')->insert(['name'=>$arrExcel[$key][5],'label'=>$arrExcel[$key][5],'pid'=>$id,'level'=>4,'usedit'=>'false','value'=>0]);
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                Db::table('classify')->where('name',$arrExcel[$key][5])->where('value',0)->update(['value'=>$id]);
                                                if(strlen(trim($arrExcel[$key][6]))==0){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }else{
                                Db::table('classify')->insert(['name'=>$arrExcel[$key][3],'label'=>$arrExcel[$key][3],'pid'=>$id,'level'=>2,'usedit'=>'false','value'=>0]);
                                $getid = Db::table('classify')->where('name',$arrExcel[$key][3])->where('pid',$id)->find();
                                $id = $getid['id'];
                                Db::table('classify')->where('name',$arrExcel[$key][3])->where('value',0)->update(['value'=>$id]);
                                if(strlen(trim($arrExcel[$key][4]))==0){
                                    // return '211';
                                }else{
                                    $result = Db::table('classify')->where('name',$arrExcel[$key][4])->where('pid',$id)->select();
                                    if(count($result)){
                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][4])->where('pid',$id)->find();
                                        $id = $getid['id'];
                                        if(strlen(trim($arrExcel[$key][5]))==0){
                                            // return '211';
                                        }else{
                                            $result = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->select();
                                            if(count($result)){
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                if(strlen(trim($arrExcel[$key][6]))){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }else{
                                                Db::table('classify')->insert(['name'=>$arrExcel[$key][5],'label'=>$arrExcel[$key][5],'pid'=>$id,'level'=>4,'usedit'=>'false','value'=>0]);
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                Db::table('classify')->where('name',$arrExcel[$key][5])->where('value',0)->update(['value'=>$id]);
                                                if(strlen(trim($arrExcel[$key][6]))==0){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }
                                        }
                                    }else{
                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][4],'label'=>$arrExcel[$key][4],'pid'=>$id,'level'=>3,'usedit'=>'false','value'=>0]);
                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][4])->where('pid',$id)->find();
                                        $id = $getid['id'];
                                        Db::table('classify')->where('name',$arrExcel[$key][4])->where('value',0)->update(['value'=>$id]);
                                        if(strlen(trim($arrExcel[$key][5]))==0){
                                            // return '211';
                                        }else{
                                            $result = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->select();
                                            if(count($result)){
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                if(strlen(trim($arrExcel[$key][6]))==0){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }else{
                                                Db::table('classify')->insert(['name'=>$arrExcel[$key][5],'label'=>$arrExcel[$key][5],'pid'=>$id,'level'=>4,'usedit'=>'false','value'=>0]);
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                Db::table('classify')->where('name',$arrExcel[$key][5])->where('value',0)->update(['value'=>$id]);
                                                if(strlen(trim($arrExcel[$key][6]))==0){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }else{
                        Db::table('classify')->insert(['name'=>$name,'label'=>$name,'pid'=>0,'level'=>1,'usedit'=>'false','value'=>0]);
                        $getid = Db::table('classify')->where('name',$name)->where('pid',0)->find();
                        $id = $getid['id'];
                        Db::table('classify')->where('name',$name)->where('pid',0)->update(['value'=>$id]);
                        if(strlen(trim($arrExcel[$key][3]))==0){
                            // return '211';
                        }else{
                            $result = Db::table('classify')->where('name',$arrExcel[$key][3])->where('pid',$id)->select();
                            if(count($result)){
                                $getid = Db::table('classify')->where('name',$arrExcel[$key][3])->where('pid',$id)->find();
                                $id = $getid['id'];
                                if(strlen(trim($arrExcel[$key][4]))==0){
                                    // return '211';
                                }else{
                                    $result = Db::table('classify')->where('name',$arrExcel[$key][4])->where('pid',$id)->select();
                                    if(count($result)){
                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][4])->where('pid',$id)->find();
                                        $id = $getid['id'];
                                        if(strlen(trim($arrExcel[$key][5]))==0){
                                            // return '211';
                                        }else{
                                            $result = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->select();
                                            if(count($result)){
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                if(strlen(trim($arrExcel[$key][6]))==0){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }else{
                                                Db::table('classify')->insert(['name'=>$arrExcel[$key][5],'label'=>$arrExcel[$key][5],'pid'=>$id,'level'=>4,'usedit'=>'false','value'=>0]);
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                Db::table('classify')->where('name',$arrExcel[$key][5])->where('value',0)->update(['value'=>$id]);
                                                if(strlen(trim($arrExcel[$key][6]))==0){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }
                                        }
                                    }else{
                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][4],'label'=>$arrExcel[$key][4],'pid'=>$id,'level'=>3,'usedit'=>'false','value'=>0]);
                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][4])->where('pid',$id)->find();
                                        $id = $getid['id'];
                                        Db::table('classify')->where('name',$arrExcel[$key][4])->where('value',0)->update(['value'=>$id]);
                                        if(strlen(trim($arrExcel[$key][5]))==0){
                                            // return '211';
                                        }else{
                                            $result = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->select();
                                            if(count($result)){
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                if(strlen(trim($arrExcel[$key][6]))==0){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }else{
                                                Db::table('classify')->insert(['name'=>$arrExcel[$key][5],'label'=>$arrExcel[$key][5],'pid'=>$id,'level'=>4,'usedit'=>'false','value'=>0]);
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                Db::table('classify')->where('name',$arrExcel[$key][5])->where('value',0)->update(['value'=>$id]);
                                                if(strlen(trim($arrExcel[$key][6]))==0){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }else{
                                Db::table('classify')->insert(['name'=>$arrExcel[$key][3],'label'=>$arrExcel[$key][3],'pid'=>$id,'level'=>2,'usedit'=>'false','value'=>0]);
                                $getid = Db::table('classify')->where('name',$arrExcel[$key][3])->where('pid',$id)->find();
                                $id = $getid['id'];
                                Db::table('classify')->where('name',$arrExcel[$key][3])->where('value',0)->update(['value'=>$id]);
                                if(strlen(trim($arrExcel[$key][4]))==0){
                                    // return '211';
                                }else{
                                    $result = Db::table('classify')->where('name',$arrExcel[$key][4])->where('pid',$id)->select();
                                    if(count($result)){
                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][4])->where('pid',$id)->find();
                                        $id = $getid['id'];
                                        if(strlen(trim($arrExcel[$key][5]))==0){
                                            // return '211';
                                        }else{
                                            $result = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->select();
                                            if(count($result)){
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                if(strlen(trim($arrExcel[$key][6]))==0){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }else{
                                                Db::table('classify')->insert(['name'=>$arrExcel[$key][5],'label'=>$arrExcel[$key][5],'pid'=>$id,'level'=>4,'usedit'=>'false','value'=>0]);
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                Db::table('classify')->where('name',$arrExcel[$key][5])->where('value',0)->update(['value'=>$id]);
                                                if(strlen(trim($arrExcel[$key][6]))==0){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }
                                        }
                                    }else{
                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][4],'label'=>$arrExcel[$key][4],'pid'=>$id,'level'=>3,'usedit'=>'false','value'=>0]);
                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][4])->where('pid',$id)->find();
                                        $id = $getid['id'];
                                        Db::table('classify')->where('name',$arrExcel[$key][4])->where('value',0)->update(['value'=>$id]);
                                        if(strlen(trim($arrExcel[$key][5]))==0){
                                            // return '211';
                                        }else{
                                            $result = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->select();
                                            if(count($result)){
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                if(strlen(trim($arrExcel[$key][6]))){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }else{
                                                Db::table('classify')->insert(['name'=>$arrExcel[$key][5],'label'=>$arrExcel[$key][5],'pid'=>$id,'level'=>4,'usedit'=>'false','value'=>0]);
                                                $getid = Db::table('classify')->where('name',$arrExcel[$key][5])->where('pid',$id)->find();
                                                $id = $getid['id'];
                                                Db::table('classify')->where('name',$arrExcel[$key][5])->where('value',0)->update(['value'=>$id]);
                                                if(strlen(trim($arrExcel[$key][6]))==0){
                                                    // return '211';
                                                }else{
                                                    $result = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->select();
                                                    if(count($result)){
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                    }else{
                                                        Db::table('classify')->insert(['name'=>$arrExcel[$key][6],'label'=>$arrExcel[$key][6],'pid'=>$id,'level'=>5,'usedit'=>'false','value'=>0]);
                                                        $getid = Db::table('classify')->where('name',$arrExcel[$key][6])->where('pid',$id)->find();
                                                        $id = $getid['id'];
                                                        Db::table('classify')->where('name',$arrExcel[$key][6])->where('value',0)->update(['value'=>$id]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $Q_Question = $arrExcel[$key][0];
                    $Q_Answer = $arrExcel[$key][1];
                    @$Q_SimilarPro = $arrExcel[$key][7].'|'.$arrExcel[$key][8].'|'.$arrExcel[$key][9].'|'.$arrExcel[$key][10];
		$arr = explode('|',$Q_SimilarPro);
		$arrSimilar = array();
		if(count($arr)){
			for($i=0;$i<count($arr);$i++){
				if(strlen(trim($arr[$i]))==0){
				}else{
					array_push($arrSimilar,$arr[$i]);
				}
			}
		}
		$Q_SimilarPro = implode('--',$arrSimilar);
                    $data[] = array(
                        'Q_Question'=>$Q_Question,
                        'Q_Answer'=>$Q_Answer,
                        'id' => $id,
			'Q_Count' => 0,
                        'Q_SimilarPro' => $Q_SimilarPro,
                        'Q_Createtime'=>$Q_Createtime
                    );
                    $success = $success+1;
                }
            }
            $num = count($data);
            if($num!=0){
                $result = Db::table('question')->insertAll($data);
                if(count($result)){
                        $this->success('导入成功',['success'=>$success,'error'=>$error,'errorMsg'=>$errorMsg]);
                }
            }else{
                $this->error('导入失败',['error'=>$error,'errorMsg'=>$errorMsg]);
            }
        }else{
            $this->error('文件格式错误，请选择.xls/xlsx文件！');
        }
    }
}
