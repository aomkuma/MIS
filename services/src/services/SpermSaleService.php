<?php

namespace App\Service;

use App\Model\SpermSale;
use App\Model\SpermSaleDetail;
use Illuminate\Database\Capsule\Manager as DB;

class SpermSaleService {

    public static function getMainList($years, $months, $region_id) {
        return SpermSale::select(DB::raw("SUM(amount) AS sum_amount")
                                , DB::raw("SUM(`values`) AS sum_baht")
                                , "sperm_sale.update_date")
                        ->join("sperm_sale_detail", 'sperm_sale_detail.sperm_sale_id', '=', 'sperm_sale.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region_id)
                        ->first()
                        ->toArray();
    }

    public static function getMainListforreport($years, $months,  $sperm_type_id) {
        return SpermSale::select(DB::raw("SUM(amount) AS sum_amount")
                                , DB::raw("SUM(`values`) AS sum_baht")
                                , "sperm_sale.update_date")
                        ->join("sperm_sale_detail", 'sperm_sale_detail.sperm_sale_id', '=', 'sperm_sale.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        
                        ->where("sperm_sale_type_id", $sperm_type_id)
                        ->first()
                        ->toArray();
    }

    public static function getDataByID($id) {
        return SpermSale::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('spermSaleDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->first();
    }

    public static function getData($cooperative_id, $months, $years) {
        return SpermSale::where('cooperative_id', $cooperative_id)
                        ->where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('spermSaleDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->first();
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = SpermSale::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = SpermSale::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = SpermSaleDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = SpermSaleDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeDetailData($id) {

        return SpermSaleDetail::find($id)->delete();
    }

    public static function removeData($id) {
        
    }

    public static function updateDataApprove($id, $obj) {

            return SpermSale::where('id', $id)->update($obj);
        }

}
