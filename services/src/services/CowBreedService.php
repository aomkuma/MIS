<?php

namespace App\Service;

use App\Model\CowBreed;
use App\Model\CowBreedDetail;
use Illuminate\Database\Capsule\Manager as DB;

class CowBreedService {

    public static function getMainList($years, $months, $region_id, $cow_breed_type_id){
            return CowBreed::select(DB::raw("SUM(amount) AS sum_amount")
                                        ,DB::raw("SUM(`price`) AS sum_baht")
                                        ,"cow_breed.update_date")
                            ->join("cow_breed_detail", 'cow_breed_detail.cow_breed_id', '=', 'cow_breed.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("region_id", $region_id)
                            ->where('cow_breed_type_id', $cow_breed_type_id)
                            ->first()
                            ->toArray();
        }

    public static function getDataByID($id) {
        return CowBreed::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('cowbreedDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->first();
    }

    public static function getDetailmonth($years, $months, $type_id, $region) {
        return CowBreed::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(`price`) AS price"))
                        ->join("cow_breed_detail", 'cow_breed_detail.cow_breed_id', '=', 'cow_breed.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region)
                        ->where("cow_breed_type_id", $type_id)
                        ->first()
                        ->toArray();
    }
    public static function getDetailyear($years, $type_id, $region) {
        return CowBreed::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(`price`) AS price"))
                        ->join("cow_breed_detail", 'cow_breed_detail.cow_breed_id', '=', 'cow_breed.id')
                        ->where("years", $years)
                        
                        ->where("region_id", $region)
                        ->where("cow_breed_type_id", $type_id)
                        ->first()
                        ->toArray();
    }
     public static function getDetailquar($years, $type_id, $region, $quar) {
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
        return CowBreed::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(`price`) AS price"))
                        ->join("cow_breed_detail", 'cow_breed_detail.cow_breed_id', '=', 'cow_breed.id')
                        ->where("years", $years)
                        ->whereBetween("months", [$st, $en])
                        ->where("region_id", $region)
                        ->where("cow_breed_type_id", $type_id)
                        ->first()
                        ->toArray();
    }
    

    public static function getData($cooperative_id, $months, $years) {
        return CowBreed::where('cooperative_id', $cooperative_id)
                        ->where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('cowbreedDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->first();
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowBreed::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowBreed::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowBreedDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowBreedDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeDetailData($id) {

        return CowBreedDetail::find($id)->delete();
    }

    public static function removeData($id) {
        
    }

    public static function updateDataApprove($id, $obj) {

            return CowBreed::where('id', $id)->update($obj);
        }

}
