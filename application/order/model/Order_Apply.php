<?php
namespace app\order\model;

use app\order\model\Order_Area;
use think\Model;

class Order_Apply extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'order_apply';

    public function getname(){
        //获取名称
    }

    public function autoAudit($apply_id){
        if (is_numeric($apply_id)){
            $apply_obj = $this->where('id',$apply_id)->find();
            if ($apply_obj->states == 1){
                //开始处理
                $number_agree = substr_count($apply_obj->companion,"@1@");
                if ($number_agree >= $apply_obj->companion_limit){
                    //可以进行处理
                    $area_obj = Order_Area::where('id',$apply_obj->area)->find();
                    if ($area_obj->autopass == 1){
                        //开始自动处理
                        $apply_obj->states = 4;
                        $apply_obj->auditor_id = 0;
                        $apply_obj->auditor_name = '系统操作';
                        $apply_obj->audit_time = time();
                    }else{
                        //状态置为2（待审核），等待管理员处理
                        $apply_obj->states = 2;
                    }
                    if ($apply_obj->save()){
                        //自动处理申请成功
                        $data['ret'] = 1;
                        $data['msg'] = '操作成功';
                        //将时间冲突的订单自动取消
                        $this->cancelOrderByTime($apply_obj->time_start,$apply_obj->time_end);
                    }else{
                        $data['ret'] = 0;
                        $data['msg'] = '储存失败';
                    }
                }else{
                    $data['ret'] = 0;
                    $data['msg'] = '申请条目状态不可做处理';
                }
            }else{
                $data['ret'] = 0;
                $data['msg'] = '申请条目状态不可做处理';
            }
        }else{
            $data['ret'] = 0;
            $data['msg'] = '申请条目id非法';
        }
        return $data;
    }

    public function cancelOrderByTime($timebegin,$timeend){
        $data = array(
            'states' => 0,
            'auditor_id' => 0,
            'auditor_name' => '系统操作',
            'remarks'=> '该时段已经有其他订单预约成功'
        );
        $data['audit_time'] = time();
        $this->where(function ($query) use ($timebegin,$timeend) {
            $query->where(function ($query) use ($timebegin) {
                $query->where('time_end','>', $timebegin)->where('time_start','<', $timebegin);
            })->whereor(function ($query) use ($timeend) {
                $query->where('time_start','<', $timeend)->where('time_end','>', $timeend);
            })->whereor(function ($query) use ($timebegin,$timeend) {
                $query->where('time_start','>', $timebegin)->where('time_end','<', $timeend);
            });
        })->where('states',1)->setField($data);
    }

    public function getTimeStart()
    {
        $uptime = $this->getAttr('time_start');
        return date("Y-m-d H:i:s",$uptime);
    }

    public function getTimeEnd()
    {
        $uptime = $this->getAttr('time_end');
        return date("Y-m-d H:i:s",$uptime);
    }

    public function getAreaName()
    {
        $areaid = $this->getAttr('area');
        return (new Order_Area)->getAreaName($areaid);
    }

    public function getStates()
    {
        $states_arr = array('取消','待同行确认','待审核','','已确认');
        return $states_arr[$this->getAttr('states')];
    }


}