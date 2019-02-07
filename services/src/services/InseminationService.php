<?php

namespace App\Service;

use App\Model\Insemination;
use App\Model\InseminationDetail;
use Illuminate\Database\Capsule\Manager as DB;

class InseminationService {

    public static function getMainList($years, $months, $region_id/*, $farm_type, $item_type*/) {
        return Insemination::select(DB::raw("SUM(cow_amount) AS sum_cow_amount")
                                , DB::raw("SUM(service_cost + sperm_cost + material_cost) AS sum_income_amount")
                                , "insemination.update_date")
                        ->join("insemination_detail", 'insemination_detail.insemination_id', '=', 'insemination.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region_id)
                        ->first()
                        ->toArray();
    }

    public static function getDetailmonth($years, $months, $region) {
        return Insemination::select(DB::raw("SUM(cow_amount) AS amount")
                                , DB::raw("SUM(`service_cost`) AS price"))
                        ->join("insemination_detail", 'insemination_detail.insemination_id', '=', 'insemination.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region)
                        
                        ->first()
                        ->toArray();
    }

    public static function getDetailyear($years, $region) {
        return Insemination::select(DB::raw("SUM(cow_amount) AS amount")
                                , DB::raw("SUM(`service_cost`) AS price"))
                        ->join("insemination_detail", 'insemination_detail.insemination_id', '=', 'insemination.id')
                        ->where("years", $years)
                        ->where("region_id", $region)
                        
                        ->first()
                        ->toArray();
    }

    public static function getDetailquar($years, $region, $quar) {
        $st = 1;
        $en = 3;
        if ($quar == 1) {
            //$years-=1;
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
        return Insemination::select(DB::raw("SUM(cow_amount) AS amount")
                                , DB::raw("SUM(`service_cost`) AS price"))
                        ->join("insemination_detail", 'insemination_detail.insemination_id', '=', 'insemination.id')
                        ->where("years", $years)
                        ->whereBetween("months", [$st, $en])
                        ->where("region_id", $region)
                        
                        ->first()
                        ->toArray();
    }

    public static function getDataByID($id) {
        return Insemination::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('inseminationDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->first();
    }

    public static function getData($cooperative_id, $months, $years) {
        return Insemination::where('cooperative_id', $cooperative_id)
                        ->where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('inseminationDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->first();
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Insemination::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Insemination::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = InseminationDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = InseminationDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeDetailData($id) {

        return InseminationDetail::find($id)->delete();
    }

    public static function removeData($id) {
        
    }

    public static function updateDataApprove($id, $obj) {

            return Insemination::where('id', $id)->update($obj);
        }

}
