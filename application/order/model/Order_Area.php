<?php
namespace app\order\model;

use think\Model;

class Order_Area extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'order_area';

    public function getVenueID($areaid){
        //获取名称
        return $this->where('id',$areaid)->value('venue');
    }

    public function getAreaName($areaid){
        return $this->where('id',$areaid)->value('name');
    }

    public function getCompanionLimit($areaid){
        return $this->where('id',$areaid)->value('companion_limit');
    }

    public function getTimeLimit($areaid){
        return $this->where('id',$areaid)->value('booktime_max');
    }

}