<?php

namespace App\Service;

use App\Model\ProductMilk;
use Illuminate\Database\Capsule\Manager as DB;

class ProductMilkService {

    public static function checkDuplicate($id, $name) {
        return ProductMilk::where('id', '<>', $id)
                        ->where('name', $name)
                        
                        ->first();
    }

    public static function getList($actives = '', $menu_type = '', $condition = []) {
        return ProductMilk::
                        orderBy("id", 'DESC')
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
