<?php

namespace App\Service;

use App\Model\Travel;
use App\Model\TravelDetail;
use Illuminate\Database\Capsule\Manager as DB;

class TravelService {

    public static function getMainList($years, $months, $field_amount, $field_price) {
        return Travel::select(DB::raw("SUM(".$field_amount.") AS sum_amount")
                                , DB::raw("SUM(".$field_price.") AS sum_baht")
                                , "travel.update_date")
                        ->join("travel_detail", 'travel_detail.travel_id', '=', 'travel.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        // ->where("region_id", $region_id)
                        ->first()
                        ->toArray();
    }

    public static function getDataByID($id) {
        return Travel::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('travelDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->first();
    }

    public static function getData($months, $years) {
        return Travel::where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('travelDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->first();
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Travel::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Travel::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = TravelDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = TravelDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeDetailData($id) {

        return TravelDetail::find($id)->delete();
    }

    public static function removeData($id) {
        
    }

    public static function getDetailmonth($years, $month, $id) {
        return Travel::select(DB::raw("SUM(adult_pay) AS apay")
                                , DB::raw("SUM(`child_pay`) AS cpay")
                                , DB::raw("SUM(`student_pay`) AS spay")
                                , DB::raw("SUM(`adult_price`) AS p_adult")
                                , DB::raw("SUM(`child_price`) AS p_child")
                                , DB::raw("SUM(`student_price`) AS p_student")
                                , DB::raw("SUM(`adult_except`) AS a_except")
                                , DB::raw("SUM(`child_except`) AS c_except")
                                , DB::raw("SUM(`student_except`) AS s_except"))
                        ->join("travel_detail", 'travel_detail.travel_id', '=', 'travel.id')
                        ->where("years", $years)
                       // ->where("travel_type_id", $id)
                        ->where("months", $month)
                        ->first()
                        ->toArray();
    }

    public static function getDetailyear($years, $region) {
        return Travel::select(DB::raw("SUM(adult_pay) AS apay")
                                , DB::raw("SUM(`child_pay`) AS cpay")
                                , DB::raw("SUM(`student_pay`) AS spay")
                                , DB::raw("SUM(`adult_price`) AS p_adult")
                                , DB::raw("SUM(`child_price`) AS p_child")
                                , DB::raw("SUM(`student_price`) AS p_student")
                                , DB::raw("SUM(`adult_except`) AS a_except")
                                , DB::raw("SUM(`child_except`) AS c_except")
                                , DB::raw("SUM(`student_except`) AS s_except"))
                        ->join("travel_detail", 'travel_detail.travel_id', '=', 'travel.id')
                        ->where("years", $years)
                        ->where("region_id", $region)
                        ->first()
                        ->toArray();
    }

    public static function getDetailquar($years, $region, $quar) {
        $st = 1;
        $en = 3;
        if ($quar == 1) {
            $years-=1;
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

        return Travel::select(DB::raw("SUM(adult_pay) AS apay")
                                , DB::raw("SUM(`child_pay`) AS cpay")
                                , DB::raw("SUM(`student_pay`) AS spay")
                                , DB::raw("SUM(`adult_price`) AS p_adult")
                                , DB::raw("SUM(`child_price`) AS p_child")
                                , DB::raw("SUM(`student_price`) AS p_student")
                                , DB::raw("SUM(`adult_except`) AS a_except")
                                , DB::raw("SUM(`child_except`) AS c_except")
                                , DB::raw("SUM(`student_except`) AS s_except"))
                        ->join("travel_detail", 'travel_detail.travel_id', '=', 'travel.id')
                        ->where("years", $years)
//                        ->where("region_id", $region)
                        ->whereBetween("months", [$st, $en])
                        ->first()
                        ->toArray();
    }

}
