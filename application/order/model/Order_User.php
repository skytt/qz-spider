<?php
namespace app\order\model;

use think\Model;

class Order_User extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'order_user';

    public function login($username = '', $password = '') {
        $user = $this->where(function ($query) use ($username,$password) {
                $query->where('username',$username)->where('password',md5($password));
            })->whereor(function ($query) use ($username,$password) {
                $query->where('phone',$username)->where('password',md5($password));
            })->find();
        if(isset($user['id']) && $user['id']){
            $auth = array(
                'id' => $user['id'],
                'username' => $user['username']
            );
            session('user_auth', $auth);
            return $user['id'];
        }else{
            return 0;
        }
    }

    public function logout(){
		session('user_auth', null);
    }
    


}