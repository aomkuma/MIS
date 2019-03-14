<?php

namespace App\Service;

use App\Model\ProductMilkDetail;
use Illuminate\Database\Capsule\Manager as DB;

class ProductMilkDetailService {

    public static function checkDuplicate($id, $name, $subid) {
        return ProductMilkDetail::where('product_milk_detail.id', '<>', $id)
                        ->join('subproduct_milk', 'subproduct_milk.id', '=', 'product_milk_detail.sub_product_milk_id')
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->where('subproduct_milk.id', $subid)
                        ->where('product_milk_detail.name', $name)
                        ->first();
    }

//    public static function getList($actives = '', $menu_type = '', $condition = []) {
//        return ProductMilkDetail::
//                        orderBy("id", 'DESC')
//                        ->get()->toArray();
//    }

    public static function getList($actives = '', $menu_type = '', $condition = []) {
        return ProductMilkDetail::select("product_milk_detail.*", 'product_milk.name as proname', 'subproduct_milk.name as subname')
                        ->join('subproduct_milk', 'subproduct_milk.id', '=', 'product_milk_detail.sub_product_milk_id')
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->orderBy("product_milk_detail.id", 'DESC')
                        ->get()
                        ->toArray();
    }

    public static function getListByParent($sub_product_milk_id) {
        return ProductMilkDetail::where('sub_product_milk_id', $sub_product_milk_id)
                        ->orderBy("product_milk_detail.id", 'DESC')
                        ->get()
                        ->toArray();
    }

    public static function getListByParent2($sub_product_milk_id) {
        return ProductMilkDetail::select("product_milk_detail.*", 'product_milk.name as proname', 'subproduct_milk.name as subname')
                        ->join('subproduct_milk', 'subproduct_milk.id', '=', 'product_milk_detail.sub_product_milk_id')
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->where('sub_product_milk_id', $sub_product_milk_id)
                        ->orderBy("product_milk_detail.id", 'DESC')
                        ->get()
                        ->toArray();
    }

    public static function getData($id) {
        return ProductMilkDetail::select("product_milk_detail.*", 'product_milk.name as proname', 'subproduct_milk.name as subname', 'subproduct_milk.id as subid')
                        ->join('subproduct_milk', 'subproduct_milk.id', '=', 'product_milk_detail.sub_product_milk_id')
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->where('product_milk_detail.id', $id)
                        ->first();
    }

    public static function updateData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = ProductMilkDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = ProductMilkDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeData($id) {
        return AccountRole::find($id)->delete();
    }

}
