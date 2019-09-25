<?php

namespace App\Service;

use App\Model\MasterGoal;
use Illuminate\Database\Capsule\Manager as DB;

class MasterGoalService {

    public static function getIDByName($goal_name, $factory_id) {
        $res = MasterGoal::where('goal_name', $goal_name)
                        ->where(function($query) use ($factory_id) {
                            if (!empty($factory_id)) {
                                $query->where('factory_id', $factory_id);
                            }
                        })
                        ->first();
        return empty($res->id)?0:$res->id;
    }

    public static function getGoalIDByName($goal_name, $menu_type, $factory_id) {
        return MasterGoal::where('goal_name', $goal_name)
                        ->where('menu_type', $menu_type)
                        ->where('factory_id', $factory_id)
                        ->first();
    }

    public static function checkDuplicate($id, $menu_type, $goal_name, $factory_id = '') {
        return MasterGoal::where('id', '<>', $id)
                        ->where('menu_type', $menu_type)
                        ->where('goal_name', $goal_name)
                        ->where(function($query) use ($factory_id) {
                            if (!empty($factory_id)) {
                                $query->where('factory_id', $factory_id);
                            }
                        })
                        ->first();
    }

    public static function getList($actives = '', $menu_type = '', $condition = [], $sub_goal_type= '', $factory_id = '') {
        return MasterGoal::select("master_goal.*", "factory.factory_name")
                        ->where(function($query) use ($actives, $menu_type, $condition, $sub_goal_type, $factory_id) {
                            if (!empty($actives)) {
                                $query->where('master_goal.actives', $actives);
                            }

                            if (!empty($menu_type)) {
                                $query->where('master_goal.menu_type', $menu_type);
                            }

                            if (!empty($condition['goal_type'])) {
                                $query->where('master_goal.goal_type', $condition['goal_type']);
                            }

                            if (!empty($condition['menu_type'])) {
                                $query->where('master_goal.menu_type', $condition['menu_type']);
                            }

                            if (!empty($condition['keyword'])) {
                                $query->where('master_goal.goal_name', 'LIKE', DB::raw("'%". $condition['keyword'] ."%'"));
                            }

                            if(!empty($condition['RegionID'])){
                                $query->where('master_goal.menu_type', $condition['menu_type']);
                            }

                            if (!empty($sub_goal_type)) {
                                $query->where('master_goal.sub_goal_type', $sub_goal_type);
                            }

                            if (!empty($factory_id)) {
                                $query->where('master_goal.factory_id', $factory_id);
                            }

                        })
                        ->leftJoin('factory', 'factory.id', '=', 'master_goal.factory_id')
                        ->orderBy("update_date", 'DESC')
                        ->get();
    }

    public static function getListOrderByName($actives = '', $menu_type = '', $condition = [], $sub_goal_type= '', $factory_id = '') {
        return MasterGoal::select("master_goal.*", "factory.factory_name")
                        ->where(function($query) use ($actives, $menu_type, $condition, $sub_goal_type, $factory_id) {
                            if (!empty($actives)) {
                                $query->where('actives', $actives);
                            }

                            if (!empty($menu_type)) {
                                $query->where('menu_type', $menu_type);
                            }

                            if (!empty($condition['goal_type'])) {
                                $query->where('goal_type', $condition['goal_type']);
                            }

                            if (!empty($condition['menu_type'])) {
                                $query->where('menu_type', $condition['menu_type']);
                            }

                            if (!empty($condition['keyword'])) {
                                $query->where('goal_name', 'LIKE', DB::raw("'%". $condition['keyword'] ."%'"));
                            }

                            if(!empty($condition['RegionID'])){
                                $query->where('menu_type', $condition['menu_type']);
                            }

                            if (!empty($sub_goal_type)) {
                                $query->where('sub_goal_type', $sub_goal_type);
                            }

                            if (!empty($factory_id)) {
                                $query->where('factory_id', $factory_id);
                            }

                        })
                        ->leftJoin('factory', 'factory.id', '=', 'master_goal.factory_id')
                        // ->orderBy("menu_type", 'DESC')
                        ->orderBy("sub_goal_type", 'ASC')
                        ->get();
    }

    public static function getData($id) {
        return MasterGoal::find($id);
    }

    public static function getSubTypeList($menu_type) {
        return MasterGoal::select("sub_goal_type")
                ->where('menu_type', $menu_type)
                ->whereNotNull('sub_goal_type')
                ->groupBy('sub_goal_type')
                ->get();
    }

    public static function getDataByName($goal_name, $menu_type = '', $factory_id = '') {
        return MasterGoal::where('goal_name', $goal_name)
                ->where(function($query) use ($menu_type, $factory_id) {
                            if (!empty($menu_type)) {
                                $query->where('menu_type', $menu_type);
                            }
                            if (!empty($factory_id)) {
                                $query->where('factory_id', $factory_id);
                            }
                })
                ->get()->toArray()[0];
    }

    public static function updateData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = MasterGoal::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            // print_r($obj);exit;
            $model = MasterGoal::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeData($id) {
        return AccountRole::find($id)->delete();
    }

    public static function removeDataByCondition($menu_type, $goal_name, $factory_id) {
        return MasterGoal::where('menu_type', $menu_type)
                        ->where('goal_name', $goal_name)
                        ->where('factory_id', $factory_id)
                        ->delete();
    }

    public static function getmision($name) {
        return MasterGoal::where('goal_name', $name)
                        ->get()
                        ->toArray();
    }

}
