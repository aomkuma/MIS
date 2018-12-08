<?php

namespace App\Service;

use App\Model\MasterGoal;
use Illuminate\Database\Capsule\Manager as DB;

class MasterGoalService {

    public static function getList($actives = '', $menu_type = '') {
        return MasterGoal::where(function($query) use ($actives, $menu_type) {
                            if (!empty($actives)) {
                                $query->where('actives', $actives);
                            }

                            if (!empty($menu_type)) {
                                $query->where('menu_type', $menu_type);
                            }
                        })
                        ->orderBy("update_date", 'DESC')
                        ->get();
    }

    public static function getData($id) {
        return MasterGoal::find($id);
    }

    public static function updateData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = MasterGoal::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = MasterGoal::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeData($id) {
        return AccountRole::find($id)->delete();
    }

    public static function getmision($name) {
        return MasterGoal::where('goal_name', $name)
                        ->get()
                        ->toArray();
    }

}
