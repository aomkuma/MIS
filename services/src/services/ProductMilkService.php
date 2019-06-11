<?php

namespace App\Service;

use App\Model\ProductMilk;
use Illuminate\Database\Capsule\Manager as DB;

class ProductMilkService {

    public static function getIDByName($name, $facid) {
        $res = ProductMilk::where('factory_id', $facid)
                        ->where('product_milk.name', $name)
                        ->first();
        return empty($res->id)?0:$res->id;
    }    

    public static function checkDuplicate($id, $name, $facit) {
        return ProductMilk::join('factory', 'factory.id', '=', 'product_milk.factory_id')
                        ->where('factory.id', $facit)
                        ->where('product_milk.id', '<>', $id)
                        ->where('product_milk.name', $name)
                        ->first();
    }

    public static function getList($actives = '', $menu_type = '', $condition = [], $factory_id = '') {
        //  
        return ProductMilk:: select("product_milk.*", 'factory.factory_name','factory.id as factory_id')
                        ->join('factory', 'factory.id', '=', 'product_milk.factory_id')
                        ->where(function($query) use ($factory_id) {
                            if (!empty($factory_id)) {
                                $query->where('factory_id', $factory_id);
                            }
                        })
                        ->where(function($query) use ($actives) {
                            if (!empty($actives)) {
                                $query->where('actives', $actives);
                            }
                        })
                        ->orderBy("product_milk.id", 'DESC')
                        ->get()->toArray();
    }

    public static function getData($id) {
        return ProductMilk::find($id);
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = ProductMilk::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = ProductMilk::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeData($id) {
        return AccountRole::find($id)->delete();
    }

}
