<?php
namespace app\order\controller;

use app\order\model\Order_Venue;
use app\order\model\Order_Area;
use app\order\model\Order_Apply;
use app\order\model\Order_User;

use app\common\controller\BaseController;

class AdminController extends BaseController
{
    private $page_info = array(
        'title' => ' - iSZTU场地预约系统',
        'url_front' => '/order/',
    );

    protected $beforeActionList = [
        'checkSession' =>  ['only' => 'index_view,list_all_view,list_states1_view,list_states2_view,list_states4_view,usermanage_view,area_list_view,area_add_view']
    ];

    protected function checkSession()
	{
        $uid = check_login();
        if(!$uid){
            return $this->redirect($this->page_info['url_front'].'login');
        }
    }

    public function _initialize() {
        $uid = check_login();
        if ($uid) {
			$user = Order_User::get($uid);
			$this->assign('user', $user);
		}else{
			$this->assign('user', null);
		}
    }

    //---------------- View ----------------

    public function index_view(){
        $user = Order_User::get(check_login());
        $user_group = ($user['user_group'] == 0)? '%' : $user['user_group'];
        $this->assign('page_info',$this->page_info);
        $count = array(
            'states1' => Order_Apply::where('venue','like',$user_group)->where('states',1)->count(),
            'states2' => Order_Apply::where('venue','like',$user_group)->where('states',2)->count(),
            'states4' => Order_Apply::where('venue','like',$user_group)->where('states',4)->count(),
            'area' => Order_Area::where('venue','like',$user_group)->count()
        );
        $this->assign('count',$count);
        return $this->fetch('/index');
    }

    public function login_view(){
        $this->assign('page_info',$this->page_info);
        return $this->fetch('auth/login');
    }

    public function list_all_view(){
        $user = Order_User::get(check_login());
        $user_group = ($user['user_group'] == 0)? '%' : $user['user_group'];
        $this->assign('page_info',$this->page_info);
        $list = Order_Apply::where('venue','like',$user_group)->order('id', 'desc')->paginate(10);
        $this->assign('list',$list);
        return $this->fetch('/table_order');
    }

    public function list_states1_view(){
        $user = Order_User::get(check_login());
        $user_group = ($user['user_group'] == 0)? '%' : $user['user_group'];
        $this->assign('page_info',$this->page_info);
        $list = Order_Apply::where('venue','like',$user_group)->where('states',1)->order('id', 'desc')->paginate(10);
		$this->assign('list',$list);
        return $this->fetch('/table_order');
    }

    public function list_states2_view(){
        $user = Order_User::get(check_login());
        $user_group = ($user['user_group'] == 0)? '%' : $user['user_group'];
        $this->assign('page_info',$this->page_info);
        $list = Order_Apply::where('venue','like',$user_group)->where('states',2)->order('id', 'desc')->paginate(10);
		$this->assign('list',$list);
        return $this->fetch('/table_order');
    }

    public function list_states4_view(){
        $user = Order_User::get(check_login());
        $user_group = ($user['user_group'] == 0)? '%' : $user['user_group'];
        $this->assign('page_info',$this->page_info);
        $list = Order_Apply::where('venue','like',$user_group)->where('states',4)->order('id', 'desc')->paginate(10);
		$this->assign('list',$list);
        return $this->fetch('/table_order');
    }

    public function area_list_view(){
        $user = Order_User::get(check_login());
        $user_group = ($user['user_group'] == 0)? '%' : $user['user_group'];
        $this->assign('page_info',$this->page_info);
        $list = Order_Area::where('venue','like',$user_group)->order('sort')->paginate(10);
		$this->assign('list',$list);
        return $this->fetch('/table_area');
    }

    public function area_add_view(){
        $this->assign('page_info',$this->page_info);
        return $this->fetch('/area_add');
    }

    public function area_edit_view(){
        $user = Order_User::get(check_login());
        $area_id = input('get.areaid');
        $area_edit = Order_Area::where('id', $area_id)->find();
        if ($area_edit !== null){
            $this->assign('area',$area_edit);
            $this->assign('page_info',$this->page_info);
            return $this->fetch('/area_edit');
        }else{
            return $this->redirect($this->page_info['url_front'].'area_list_view');
        }
    }

    public function usermanage_view(){
        $user = Order_User::get(check_login());
        if ($user['group_admin']){
            $user_group = ($user['user_group'] == 0)? '%' : $user['user_group'];
            $this->assign('page_info',$this->page_info);
            $list = Order_User::where('user_group','like',$user_group)->order('id')->paginate(10);
            $this->assign('list',$list);
            return $this->fetch('/table_user');
        }else{
            return $this->redirect($this->page_info['url_front'].'index');
        }   
    }

    public function user_add_view(){
        $user = Order_User::get(check_login());
        if ($user['group_admin']){
            $this->assign('page_info',$this->page_info);
            return $this->fetch('/user_add');
        }else{
            return $this->redirect($this->page_info['url_front'].'index');
        }   
    }

    public function user_edit_view(){
        $edit_id = input('get.uid');
        $user = Order_User::get(check_login());
        if ($user['group_admin']){
            $user_edit = Order_User::where('id', $edit_id)->find();
            if ($user_edit !== null){
                $this->assign('user',$user_edit);
                $this->assign('page_info',$this->page_info);
                return $this->fetch('/user_edit');
            }else{
                return $this->redirect($this->page_info['url_front'].'usermanage_view');
            }
        }else{
            return $this->redirect($this->page_info['url_front'].'index');
        }   
    }

    //---------------- Action ----------------

    public function doLogin(){
        $data_post = input("post.");
        $username = '';
        $password = '';

        if (array_key_exists('username', $data_post)) {
            $username = $data_post['username'];
        }
        if (array_key_exists('password', $data_post)) {
            $password = $data_post['password'];
        }

        if (!$username || !$password) {
            $data['ret'] = 0;
            $data['msg'] = '用户名或者密码不能为空！';
        }else{
            $uid = (new Order_User)->login($username,$password);
            if ($uid > 0) {
                $data['ret'] = 1;
                $data['msg'] = '登录成功，即将跳转登录前界面';
                $data['return_url'] = input('post.returnurl') ? input('post.returnurl') : $this->page_info['url_front']. 'index';
            }else{
                $data['ret'] = 0;
                $data['msg'] = '登录失败，请检查用户名或密码';
            }
        }
        return json($data);
    }

    public function doLogout()
    {
        (new Order_User)->logout();
        return $this->redirect($this->page_info['url_front'].'login');
    }

    public function orderPass()
    {
        $data_post = input("post.");
        $orderid = null;
        if (array_key_exists('orderid', $data_post)) {
            $orderid = $data_post['orderid'];
        }

        if ($orderid){ 
            $user = Order_User::get(check_login());
            $apply_obj = Order_Apply::where('id',$orderid)->find();
            if ($apply_obj['states'] == 4){
                $data['ret'] = 0;
                $data['msg'] = '该订单已经通过审核';
            }else if ($apply_obj['states'] == 0){
                $data['ret'] = 0;
                $data['msg'] = '已被取消订单不允许再次操作';
            }else if ($user['user_group'] == $apply_obj['venue'] || $user['user_group'] == 0){
                $apply_obj->states = 4;
                $apply_obj->auditor_id = $user['id'];
                $apply_obj->auditor_name = $user['nickname'];
                $apply_obj->audit_time = time();
                if ($apply_obj->save()){
                    //自动处理申请成功
                    $data['ret'] = 1;
                    $data['msg'] = '操作成功';
                    //将时间冲突的订单自动取消
                    (new Order_Apply)->cancelOrderByTime($apply_obj->time_start,$apply_obj->time_end);
                }else{
                    $data['ret'] = 0;
                    $data['msg'] = '系统错误，请联系系统管理员';
                }
            }else{
                $data['ret'] = 0;
                $data['msg'] = '没有该场地的操作权限';
            }
        }else{
            $data['ret'] = 0;
            $data['msg'] = '没有提交场地数据';
        }
        return json($data);
    }

    public function orderCancel()
    {
        $data_post = input("post.");
        $orderid = null;
        $reason = '无';
        if (array_key_exists('orderid', $data_post)) {
            $orderid = $data_post['orderid'];
        }
        if (array_key_exists('reason', $data_post)) {
            $reason = $data_post['reason'];
        }

        if ($orderid){
            $user = Order_User::get(check_login());
            $apply_obj = Order_Apply::where('id',$orderid)->find();
            if ($apply_obj['states'] == 0){
                $data['ret'] = 0;
                $data['msg'] = '该订单已经被取消';
            }else if ($user['user_group'] == $apply_obj['venue'] || $user['user_group'] == 0){
                $apply_obj->states = 0;
                $apply_obj->auditor_id = $user['id'];
                $apply_obj->auditor_name = $user['nickname'];
                $apply_obj->audit_time = time();
                $apply_obj->remarks = $reason;
                if ($apply_obj->save()){
                    //自动处理申请成功
                    $data['ret'] = 1;
                    $data['msg'] = '操作成功';
                }else{
                    $data['ret'] = 0;
                    $data['msg'] = '系统错误，请联系系统管理员';
                }
            }else{
                $data['ret'] = 0;
                $data['msg'] = '没有该场地的操作权限';
            }
        }else{
            $data['ret'] = 0;
            $data['msg'] = '没有提交场地数据';
        }
        return json($data);
    }

    public function changeAreaState()
    {
        $data_post = input("post.");
        $areaid = null;
        $value = null;
        if (array_key_exists('areaid', $data_post)) {
            $areaid = $data_post['areaid'];
        }
        if (array_key_exists('value', $data_post)) {
            $value = $data_post['value'];
        }

        if ($areaid){
            $user = Order_User::get(check_login());
            $area_obj = Order_Area::where('id',$areaid)->find();
            if (!$area_obj){
                $data['ret'] = 0;
                $data['msg'] = '场地数据失效';
            }else if ($user['user_group'] == $area_obj['venue'] || $user['user_group'] == 0){
                if ($value == 1){
                    $area_obj->enable = 1;
                }else{
                    $area_obj->enable = 0;
                }
                if ($area_obj->save()){
                    $data['ret'] = 1;
                    $data['msg'] = '操作成功';
                }else{
                    $data['ret'] = 0;
                    $data['msg'] = '系统错误，请联系系统管理员';
                }
            }else{
                $data['ret'] = 0;
                $data['msg'] = '没有该场地的操作权限';
            }
        }else{
            $data['ret'] = 0;
            $data['msg'] = '没有提交场地数据';
        }
        return json($data);
    }

    public function addArea()
    {
        $data_post = input("post.");

        if ($data_post){
            $user = Order_User::get(check_login());
            $new_area_obj = new Order_Area;
            $new_area_obj->venue = $user['user_group'];
            
            if ($new_area_obj->save($data_post))
            {
                $data['ret'] = 1;
                $data['msg'] = '场地添加成功';
            }else{
                $data['ret'] = 0;
                $data['msg'] = '系统错误，场地添加失败，请联系管理员';
            }
        }else{
            $data['ret'] = 0;
            $data['msg'] = '没有提交场地数据';
        }
        
        return json($data);
    }

    public function editArea()
    {
        $data_post = input("post.");

        if ($data_post){
            $user = Order_User::get(check_login());
            $area_obj = Order_Area::where('id',$data_post['areaid'])->find();
            if ($area_obj === null){
                $data['ret'] = 0;
                $data['msg'] = '场地ID参数非法';
            }else if ($area_obj['venue'] != $user['user_group'] && $user['user_group'] != 0){
                $data['ret'] = 0;
                $data['msg'] = '当前用户组无权修改该场地信息';
            }else{
                //
                $change = array();
                if($data_post['name']){
                    $change['name'] = $data_post['name'];
                }
                if($data_post['capacity']){
                    $change['capacity'] = $data_post['capacity'];
                }
                if($data_post['companion_limit']){
                    $change['companion_limit'] = $data_post['companion_limit'];
                }
                if($data_post['booktime_start']){
                    $change['booktime_start'] = $data_post['booktime_start'];
                }
                if($data_post['booktime_end']){
                    $change['booktime_end'] = $data_post['booktime_end'];
                }
                if($data_post['booktime_max']){
                    $change['booktime_max'] = $data_post['booktime_max'];
                }
                if($data_post['sort']){
                    $change['sort'] = $data_post['sort'];
                }
                $change['attest'] = $data_post['attest'];
                $change['autopass'] = $data_post['autopass'];
                $change['enable'] = $data_post['enable'];
                if ($area_obj->save($change) !== false)
                {
                    $data['ret'] = 1;
                    $data['msg'] = '场地修改成功';
                }else{
                    $data['ret'] = 0;
                    $data['msg'] = '系统错误，场地修改失败，请联系管理员';
                }
            }
        }else{
            $data['ret'] = 0;
            $data['msg'] = '没有提交场地数据';
        }
        
        return json($data);
    }

    public function addUser()
    {
        $data_post = input("post.");

        if ($data_post){
            $user = Order_User::get(check_login());
            if ($user['group_admin']){
                $new_user_obj = new Order_User;
                $new_user_obj->user_group = $user['user_group'];
                $new_user_obj->add_by = $user['id'];
                $data_post['password'] = md5($data_post['password']);
                
                if ($new_user_obj->save($data_post))
                {
                    $data['ret'] = 1;
                    $data['msg'] = '用户添加成功';
                }else{
                    $data['ret'] = 0;
                    $data['msg'] = '系统错误，用户添加失败，请联系管理员';
                }
            }else{
                $data['ret'] = 0;
                $data['msg'] = '没有添加用户的权限';
            }
        }else{
            $data['ret'] = 0;
            $data['msg'] = '没有提交用户数据';
        }
        
        return json($data);
    }

    public function editUser()
    {
        $data_post = input("post.");

        if ($data_post){
            $user = Order_User::get(check_login());
            if ($user['group_admin']){
                $user_obj = Order_User::where('id', $data_post['uid'])->find();
                if ($user_obj['user_group'] !=$user['user_group'] && $user['user_group'] != 0){
                    $data['ret'] = 0;
                    $data['msg'] = '当前用户组无权修改该用户信息';
                }else if ($user_obj !== null){
                    $change = array();
                    if($data_post['username']){
                        $change['username'] = $data_post['username'];
                    }
                    if($data_post['password']){
                        $change['password'] = md5($data_post['password']);
                    }
                    if($data_post['nickname']){
                        $change['nickname'] = $data_post['nickname'];
                    }
                    if($data_post['phone']){
                        $change['phone'] = $data_post['phone'];
                    }
                    $change['group_admin'] = $data_post['group_admin'];
                    
                    if ($user_obj->save($change) !== false)
                    {
                        $data['ret'] = 1;
                        $data['msg'] = '用户修改成功';
                    }else{
                        $data['ret'] = 0;
                        $data['msg'] = '系统错误，用户修改失败，请联系管理员';
                    }
                }else{
                    $data['ret'] = 0;
                    $data['msg'] = '用户ID非法';
                }
            }else{
                $data['ret'] = 0;
                $data['msg'] = '没有添加用户的权限';
            }
        }else{
            $data['ret'] = 0;
            $data['msg'] = '没有提交用户数据';
        }
        
        return json($data);
    }
    


}
