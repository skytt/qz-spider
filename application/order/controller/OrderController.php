<?php
namespace app\order\controller;

use app\order\model\Order_Venue;
use app\order\model\Order_Area;
use app\order\model\Order_Apply;

use app\common\controller\BaseController;

class OrderController extends BaseController
{
    private $key_conn = 'isztu';
    
    public function getVenueList()  //获取场地
    {
        $result = Order_Venue::all();
        $data['ret'] = 1;
        $data['msg'] = '查询成功';
        $data['data'] = $result;
        return json($data);
    }

    public function getAreaList()  //获取场地
    {
        $data_get = input("get.");
        if (!array_key_exists('venueid', $data_get)){
            //未获取场馆ID
            $data['ret'] = 0;
            $data['msg'] = '场馆ID为空';
        }else{
            $venueID = $data_get['venueid'];
            $result = Order_Area::where('venue',$venueID)->where('enable',1)->order('sort')->select();
            if ($result !== null)
            {
                $data['ret'] = 1;
                $data['msg'] = '获取场地列表成功';
                $data['data'] = $result;
            }else{
                $data['ret'] = 0;
                $data['msg'] = '该区域没有场地';
            }
        }
        return json($data);
    }

    public function searchArea()  //获取场地
    {
        $data_get = input("get.");
        if (!array_key_exists('venueid', $data_get)){
            //未获取场馆ID
            $data['ret'] = 0;
            $data['msg'] = '场馆ID为空';
        }else if (!array_key_exists('date', $data_get)){
            //未获取场馆ID
            $data['ret'] = 0;
            $data['msg'] = '搜索日期为空';
        }else{
            $venueID = $data_get['venueid'];
            $date = $data_get['date'];
            $area_result = Order_Area::where('venue',$venueID)->where('enable',1)->order('sort')->select();
            if ($area_result !== null)
            {
                $apply_result = Order_Apply::where('venue',$venueID)->whereBetween('time_start',[strtotime($date." 00:00:00"),strtotime($date." 23:59:59")])->where('states',4)->select();
                foreach($area_result as $key =>$area_item){
                    $areaid = $area_item->id;
                    $apply_arr = [];
                    foreach($apply_result as $key =>$apply_item){
                        if ($apply_item->area == $areaid)
                        {
                            array_push($apply_arr, $apply_item);
                        }
                    }
                    $area_item->apply = $apply_arr;
                }
                $data['ret'] = 1;
                $data['msg'] = '获取场地列表成功';
                $data['data'] = $area_result;
            }else{
                $data['ret'] = 0;
                $data['msg'] = '该区域没有场地';
            }
        }
        return json($data);
    }

    public function getAreaDetail()  //获取场地
    {
        $data_get = input("get.");
        if (!array_key_exists('areaid', $data_get)){
            //未获取场馆ID
            $data['ret'] = 0;
            $data['msg'] = '场馆ID为空';
        }else if (!array_key_exists('date', $data_get)){
            //未获取场馆ID
            $data['ret'] = 0;
            $data['msg'] = '搜索日期为空';
        }else{
            $areaid = $data_get['areaid'];
            $date = $data_get['date'];
            $result = Order_Area::find($areaid);
            if ($result !== null)
            {
                if ($result->enable){
                    $areaid = $result->id;
                    $apply_result = Order_Apply::where('area',$areaid)->whereBetween('time_start',[strtotime($date." 00:00:00"),strtotime($date." 23:59:59")])->where('states',4)->select();
                    $result->apply = $apply_result;
                    $data['ret'] = 1;
                    $data['msg'] = '查询成功';
                    $data['data'] = $result;
                }else{
                    $data['ret'] = 0;
                    $data['msg'] = '该场地已被禁用';
                }
            }else{
                $data['ret'] = 0;
                $data['msg'] = '没有该场馆';
            }
            
        }
        return json($data);
    }

    public function applyArea()  //获取场地
    {
        $data_post = input("post.");
        if (!array_key_exists('area', $data_post)||
            !array_key_exists('applicant_id', $data_post)||
            !array_key_exists('title', $data_post)||
            !array_key_exists('content', $data_post)||
            !array_key_exists('companion', $data_post)||
            !array_key_exists('time_start', $data_post)||
            !array_key_exists('time_end', $data_post)){
            //未获取场馆ID
            $data['ret'] = 0;
            $data['msg'] = '数据不完整';
        }else if(!$data_post['area'] || !$data_post['applicant_id'] || !$data_post['title'] || !$data_post['content'] || !$data_post['companion'] || !$data_post['time_start'] || !$data_post['time_end']){
            $data['ret'] = 0;
            $data['msg'] = '数据不完整';
        }
        else{
            $areaid_id = $data_post['area'];
            //check time start
            $timeStartCheck = Order_Apply::where('time_start','<=',$data_post['time_start'])->where('time_end','>=',$data_post['time_start'])->where('states',4)->select();
            if ($timeStartCheck != null){
                $data['ret'] = 0;
                $data['msg'] = '该时段已有预约，请推迟开始时间';
                $data['da'] = $timeStartCheck;
            }else{
                //check time end
                $timeEndCheck = Order_Apply::where('time_start','<=',$data_post['time_end'])->where('time_end','>=',$data_post['time_end'])->where('states',4)->select();
                if ($timeEndCheck != null){
                    $data['ret'] = 0;
                    $data['msg'] = '该时段已有预约，请提前结束时间';
                }else{
                    //check time include
                    $timeEndCheck = Order_Apply::where('time_start','>=',$data_post['time_start'])->where('time_end','<=',$data_post['time_end'])->where('states',4)->select();
                    if ($timeEndCheck != null){
                        $data['ret'] = 0;
                        $data['msg'] = '该时段已有预约，请另选时段';
                    }else if((($data_post['time_end'] - $data_post['time_start'])/60) > (new Order_Area)->getTimeLimit($areaid_id)){
                        $data['ret'] = 0;
                        $data['msg'] = '预约时长超限，请重新选择时间';
                    }else if($data_post['time_start'] <= time()){
                        $data['ret'] = 0;
                        $data['msg'] = '预约时间不能前于当前时间';
                    }else{
                        
                        $data_post['venue'] = (new Order_Area)->getVenueID($areaid_id);
                        $data_post['companion_limit'] = (new Order_Area)->getCompanionLimit($areaid_id);
                        $data_post['states'] = 1;
                        $result = new Order_Apply;
                        $result->save($data_post);
                        if ($result)
                        {
                            $data['ret'] = 1;
                            $data['msg'] = '数据新增成功';
                            $data['data'] = $result;
                        }else{
                            $data['ret'] = 0;
                            $data['msg'] = '数据添加失败';
                        }
                    }
                }
            }
        }
        return json($data);
    }

    public function getUserOrder()  //获取场地
    {
        $page_devide = 4;
        $data_get = input("get.");
        if (!array_key_exists('applicant_id', $data_get)){
            //未获取场馆ID
            $data['ret'] = 0;
            $data['msg'] = '数据不完整';
        }else if (!$data_get['applicant_id']){
            //未获取场馆ID
            $data['ret'] = 0;
            $data['msg'] = '学号为空';
        }else{
            $applicant_id = $data_get['applicant_id'];
            $page = (array_key_exists('page',$data_get))? $data_get['page'] : 1;//个数限制
            $result =(array_key_exists('companion', $data_get))
                ?Order_Apply::where('companion','like','%'.$applicant_id.'@%')->order('id','desc')->page($page,$page_devide)->select()
                :Order_Apply::where('applicant_id',$applicant_id)->order('id','desc')->page($page,$page_devide)->select();
            foreach($result as $value){
                $value['area_text'] = (new Order_Area)->getAreaName($value['area']);
            }
            $data['ret'] = 1;
            $data['msg'] = '查询成功';
            $data['number_item'] = (array_key_exists('companion', $data_get))
                ?Order_Apply::where('companion','like','%'.$applicant_id.'@%')->count()
                :Order_Apply::where('applicant_id',$applicant_id)->count();
            $data['number_page'] = ceil($data['number_item'] /$page_devide);
            $data['data'] = $result;
        }
        return json($data);
    }

    public function doCompanionAction(){
        $data_post = input("post.");
        if (!array_key_exists('applicant_id', $data_post)||!array_key_exists('apply_id', $data_post)||!array_key_exists('action', $data_post)||!array_key_exists('key', $data_post)){
            $data['ret'] = 0;
            $data['msg'] = '数据不完整';
        }else if (!$data_post['applicant_id']||!$data_post['apply_id']||!$data_post['action']||!$data_post['key']){
            $data['ret'] = 0;
            $data['msg'] = '数据为空';
        }else{
            $order_obj = Order_Apply::where('id',$data_post['apply_id'])->find();
            if ($order_obj !== null)
            {
                if (strpos($order_obj->companion, $data_post['applicant_id'].'@') === false){
                    //companion字段中没有对应的用户
                    $data['ret'] = 0;
                    $data['msg'] = '操作非法，您不是本订单的同行人';
                }else{
                    $key_create = sha1($data_post['applicant_id'].$this->key_conn.$data_post['apply_id']);
                    if ($data_post['key'] != $key_create){
                        $data['ret'] = 0;
                        $data['msg'] = '请求非法！通信密钥校验失败';
                    }else{
                        if($order_obj->states != 1){
                            $data['ret'] = 0;
                            $data['msg'] = '当前订单状态不允许修改同行人信息';
                        }else{
                            $check = false;
                            if ($data_post['action'] == 1){
                                //make agree
                                if (strpos($order_obj->companion, $data_post['applicant_id'].'@0') !== false){
                                    $order_obj->companion = preg_replace("/".$data_post['applicant_id']."@[0-9]+@[0-9]+/",$data_post['applicant_id']."@1@".time(),$order_obj->companion);
                                    $check = true;
                                }else if (strpos($order_obj->companion, $data_post['applicant_id'].'@2') !== false){
                                    $data['ret'] = 0;
                                    $data['msg'] = '已拒绝的申请不可再次接受！';
                                }else if (strpos($order_obj->companion, $data_post['applicant_id'].'@1') !== false){
                                    $data['ret'] = 0;
                                    $data['msg'] = '该订单已经接受同行';
                                }else{
                                    $data['ret'] = 0;
                                    $data['msg'] = '未知States状态错误';
                                }
                            }else if ($data_post['action'] == 2){
                                if (strpos($order_obj->companion, $data_post['applicant_id'].'@2') === false){
                                    $order_obj->companion = preg_replace("/".$data_post['applicant_id']."@[0-9]+@[0-9]+/",$data_post['applicant_id']."@2@".time(),$order_obj->companion);
                                    $check = true;
                                }else{
                                    $data['ret'] = 0;
                                    $data['msg'] = '该订单已经被拒绝同行';
                                }
                            }
                            if ($check){
                                if ($order_obj->save()){
                                    $data['ret'] = 1;
                                    $data['msg'] = '操作成功';
                                    //开始自动处理
                                    $data['order_auto'] = (new Order_Apply)->autoAudit($order_obj->id);
                                }else{
                                    $data['ret'] = 0;
                                    $data['msg'] = '储存失败';
                                }
                            }
                        }
                    }
                }
            }else{
                $data['ret'] = 0;
                $data['msg'] = '申请订单未找到';
            }
        }
        return json($data);
    }

    public function getApplyDetail(){
        $data_post = input("post.");
        if (!array_key_exists('applicant_id', $data_post)||!array_key_exists('apply_id', $data_post)||!array_key_exists('key', $data_post)){
            $data['ret'] = 0;
            $data['msg'] = '数据不完整';
        }else if (!$data_post['applicant_id']||!$data_post['apply_id']||!$data_post['key']){
            $data['ret'] = 0;
            $data['msg'] = '数据为空';
        }else{
            $order_obj = Order_Apply::where('id',$data_post['apply_id'])->find();
            if ($order_obj !== null)
            {
                if ((strpos($order_obj->companion, $data_post['applicant_id'].'@') === false)&&($order_obj->applicant_id != $data_post['applicant_id'])){
                    //companion字段中没有对应的用户
                    $data['ret'] = 0;
                    $data['msg'] = '操作非法，您不是本订单的相关用户';
                }else{
                    $key_create = sha1($data_post['applicant_id'].$this->key_conn.$data_post['apply_id']);
                    if ($data_post['key'] != $key_create){
                        $data['ret'] = 0;
                        $data['msg'] = '请求非法！通信密钥校验失败';
                    }else{
                        $data['ret'] = 1;
                        $data['msg'] = '获取订单数据成功';
                        $order_obj['area_text'] = (new Order_Area)->getAreaName($order_obj['area']);
                        $data['data'] = $order_obj;
                    }
                }
            }else{
                $data['ret'] = 0;
                $data['msg'] = '申请订单未找到';
            }
        }
        return json($data);
    }


}
