<?php

namespace App\Service;

use App\Model\CowGroup;
use App\Model\CowGroupDetail;
use Illuminate\Database\Capsule\Manager as DB;

class CowGroupService {

    public static function getMainList($years, $months, $region_id, $goal_id, $field_name) {
        return CowGroup::select(DB::raw("SUM(".$field_name.") AS sum_baht")
                                // , DB::raw("SUM(".$field_price.") AS sum_baht")
                                , "cow_group.update_date")
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region_id)
                        ->where("cow_group_item_id", $goal_id)
                        ->first()
                        ->toArray();
    }
    public static function getMainListquar($years, $st, $en, $region_id, $goal_id, $field_name) {
        return CowGroup::select(DB::raw("SUM(".$field_name.") AS sum_baht")
                                // , DB::raw("SUM(".$field_price.") AS sum_baht")
                                , "cow_group.update_date")
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group.id')
                        ->where("years", $years)
                         ->whereBetween("months", [$st, $en])
                        ->where("region_id", $region_id)
                        ->where("cow_group_item_id", $goal_id)
                        ->first()
                        ->toArray();
    }

    public static function getDataByID($id) {
        return CowGroup::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('cowGroupDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->first();
    }

    public static function getData($cow_group_name, $cooperative_id, $months, $years) {
        return CowGroup::where('cow_group_name', $cow_group_name)
                        ->where('cooperative_id', $cooperative_id)
                        ->where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('cowGroupDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->first();
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowGroup::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowGroup::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowGroupDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowGroupDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeDetailData($id) {

        return CowGroupDetail::find($id)->delete();
    }

    public static function removeData($id) {
        
    }

    public static function getDetailmonth($years, $months, $type_id, $region) {
        return CowGroup::select(DB::raw("SUM(total_sell) AS amount")
                                , DB::raw("SUM(`total_sell_values`) AS price"))
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group.id')
                        ->where("years", $years)
                        ->where("months", $months)
                    //    ->where("region_id", $region)
                        ->where("cow_group_item_id", $type_id)
                        ->first()
                        ->toArray();
    }

    public static function getDetailyear($years, $type_id, $region) {
        return CowGroup::select(DB::raw("SUM(total_sell) AS amount")
                                , DB::raw("SUM(`total_sell_values`) AS price"))
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group.id')
                        ->where("years", $years)
                        ->where("region_id", $region)
                        ->where("cow_group_item_id", $type_id)
                        ->first()
                        ->toArray();
    }

    public static function getDetailquar($years, $type_id, $region, $quar) {
        $st = 1;
        $en = 3;
         if ($quar == 1) {
      //      $years-=1;
            $st = 10;
            $en = 12;
        } else if ($quar == 2) {
            $st = 1;
            $en = 3;
        } else if ($quar == 3) {
            $st = 4;
            $en = 6;
        } else {
            $st = 7;
            $en = 9;
        }
        return CowGroup::select(DB::raw("SUM(total_sell) AS amount")
                                , DB::raw("SUM(`total_sell_values`) AS price"))
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group.id')
                        ->where("years", $years)
                        ->whereBetween("months", [$st, $en])
                        ->where("region_id", $region)
                        ->where("cow_group_item_id", $type_id)
                        ->first()
                        ->toArray();
    }

    public static function updateDataApprove($id, $obj) {

            return CowGroup::where('id', $id)->update($obj);
        }
}
