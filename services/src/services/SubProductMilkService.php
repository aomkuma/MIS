<?php

namespace App\Service;

use App\Model\SubProductMilk;
use Illuminate\Database\Capsule\Manager as DB;

class SubProductMilkService {

    public static function checkDuplicate($id, $name) {
        return SubProductMilk::where('id', '<>', $id)
                        ->where('name', $name)
                        ->first();
    }

    public static function getList($actives = '', $menu_type = '', $condition = []) {
        return SubProductMilk::select("subproduct_milk.*", 'product_milk.name as proname')
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->orderBy("subproduct_milk.id", 'DESC')
                        ->get()->toArray();
    }

    public static function getData($id) {
        return SubProductMilk::select("subproduct_milk.id as subid", 'product_milk.name as proname','subproduct_milk.name as subname')
                ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                ->where('subproduct_milk.id', $id)
                ->first();
    }

    public static function updateData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = SubProductMilk::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = SubProductMilk::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeData($id) {
        return AccountRole::find($id)->delete();
    }

}