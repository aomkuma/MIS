<?php

namespace App\Service;

use App\Model\GoalMission;
use App\Model\GoalMissionAvg;
use App\Model\GoalMissionHistory;
use App\Model\Veterinary;
use App\Model\Mineral;
// use App\Model\Insemination;
use App\Model\Sperm;
use App\Model\SpermSale;
use App\Model\Material;
use App\Model\CowGroup;
use App\Model\Travel;
use App\Model\CowBreed;
use App\Model\TrainingCowBreed;
use App\Model\CooperativeMilk;
use App\Model\CowGroupFather;

use Illuminate\Database\Capsule\Manager as DB;

class GoalMissionService {

    public static function getGoalMissionByGoalName($menu_type, $goal_name, $factory_id, $years, $chanel_id = '') {
        return GoalMission::select('goal_mission.id',DB::raw("SUM(amount) AS total_amount")
                                , DB::raw("SUM(price_value) AS price_value")
                                , DB::raw("SUM(addon_amount) AS addon_amount")
                                )
                        ->join('master_goal', 'goal_mission.goal_id', '=', 'master_goal.id')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->where('goal_mission.years', $years)
                        ->where('goal_mission.factory_id', $factory_id)
                        ->where('goal_mission.goal_id', trim($goal_name))
                        // ->where(function($query) use ($chanel_id) {
                        //     if (!empty($chanel_id)) {
                        //         $query->where('sale_chanel', $chanel_id);
                        //     }
                        // })
                        // ->toSql();
                        ->first();
    }

    public static function getGoalMissionYear($menu_type, $years) {
        return GoalMission::select(DB::raw("SUM(amount) AS total_amount")
                                , DB::raw("SUM(price_value) AS price_value")
                                )
                        ->where('years', $years)
                        ->where('menu_type', $menu_type)
                        ->first();
    }

    public static function getMenuTotal($menu_type, $years, $months = '') {
        $res = GoalMission::select(DB::raw("SUM(mis_goal_mission_avg.amount) AS total_amount")
                                , DB::raw("SUM(mis_goal_mission_avg.price_value) AS price_value")
                                )
                        ->join('goal_mission_avg', 'goal_mission_avg.goal_mission_id', '=', 'goal_mission.id')
                        // ->where('years', $years)
                        ->where(function($query) use ($years, $months) {
                            if (!empty($months)) {
                                $query->where('avg_date', $years . '-' . $months. '-01');
                            }
                        })
                        ->where('menu_type', $menu_type)
                        ->first();
        return $res['price_value'];
        // switch($menu_type){
        //     case 'บริการสัตวแพทย์': $res = GoalMissionService::getVeterinaryTotal($menu_type, $years, $months);break;
        //     // case 'ผสมเทียม': $res = GoalMissionService::getVeterinaryTotal($menu_type, $years, $months);break;
        //     case 'แร่ธาตุ พรีมิกซ์ และอาหาร': $res = GoalMissionService::getMineralTotal($menu_type, $years, $months);break;
        //     case 'ผลิตน้ำเชื้อแช่แข็ง': $res = GoalMissionService::getSpermTotal($menu_type, $years, $months);break;
        //     case 'จำหน่ายน้ำเชื้อแช่แข็ง': $res = GoalMissionService::getSpermSaleTotal($menu_type, $years, $months);break;
        //     case 'วัสดุผสมเทียมและอื่นๆ': $res = GoalMissionService::getMaterialTotal($menu_type, $years, $months);break;
        //     case 'ปัจจัยการเลี้ยงโค': $res = GoalMissionService::getCowBreedTotal($menu_type, $years, $months);break;
        //     case 'ฝึกอบรม': $res = GoalMissionService::getTrainingCowBreedTotal($menu_type, $years, $months);break;
        //     case 'ท่องเที่ยว': $res = GoalMissionService::getTraveTotal($menu_type, $years, $months);break;
        //     case 'สหกรณ์และปริมาณน้ำนม': $res = GoalMissionService::getCooperativeMilkTotal($menu_type, $years, $months);break;
        //     case 'ข้อมูลฝูงโค': $res = GoalMissionService::getCowGroupTotal($menu_type, $years, $months);break;
        //     case 'ข้อมูลฝูงโคพ่อพันธุ์': $res = GoalMissionService::getCowGroupFatherTotal($menu_type, $years, $months);break;
        // }

        // return $res;
    }


    public static function getVeterinaryTotal($menu_type, $years, $months = '') {
        $data = Veterinary::select(DB::raw("SUM(item_amount) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("veterinary_item", "veterinary_item.veterinary_id", '=', 'veterinary.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }   

    public static function getMineralTotal($menu_type, $years, $months = '') {
        $data = Mineral::select(DB::raw("SUM(`values`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("mineral_detail", "mineral_detail.mineral_id", '=', 'mineral.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }    

    public static function getSpermTotal($menu_type, $years, $months = '') {
        $data = Sperm::select(DB::raw("SUM(`price`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("sperm_detail", "sperm_detail.sperm_id", '=', 'sperm.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }

    public static function getSpermSaleTotal($menu_type, $years, $months = '') {
        $data = SpermSale::select(DB::raw("SUM(`values`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("sperm_sale_detail", "sperm_sale_detail.sperm_id", '=', 'sperm_sale.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }

    public static function getMaterialTotal($menu_type, $years, $months = '') {
        $data = Material::select(DB::raw("SUM(`price`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("material_detail", "material_detail.material_id", '=', 'material.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }

    public static function getCowBreedTotal($menu_type, $years, $months = '') {
        $data = CowBreed::select(DB::raw("SUM(`price`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("cow_breed_detail", "cow_breed_detail.cow_breed_id", '=', 'cow_breed.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }

    public static function getTrainingCowBreedTotal($menu_type, $years, $months = '') {
        $data = TrainingCowBreed::select(DB::raw("SUM(`values`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("training_cowbreed_detail", "training_cowbreed_detail.training_cowbreed_id", '=', 'training_cowbreed.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }    

    public static function getTraveTotal($menu_type, $years, $months = '') {
        $data = Travel::select(DB::raw("SUM(`total_price`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("travel_item", "travel_item.travel_id", '=', 'travel_item.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }    

    public static function getCooperativeMilkTotal($menu_type, $years, $months = '') {
        $data = CooperativeMilk::select(DB::raw("SUM(`total_values`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("cooperative_milk_detail", "cooperative_milk_detail.cooperative_milk_id", '=', 'cooperative_milk.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }

    public static function getCowGroupTotal($menu_type, $years, $months = '') {
        $data = CowGroup::select(DB::raw("SUM(`go_factory_values` + cow_values + decline_values) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("cow_group_detail", "cow_group_detail.cow_group_id", '=', 'cow_group.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }

    

    public static function getList($condition, $UserID, $RegionList) {
        return GoalMission::select("goal_mission.*"
                                    , "region.RegionName"
                                    , "master_goal.goal_name"
                                )
                        ->where(function($query) use ($condition) {
                            if (!empty($condition['Year']['yearText'])) {
                                $query->where('years', $condition['Year']['yearText']);
                            }
                            if (!empty($condition['Region']['RegionID'])) {
                                $query->where('region_id', $condition['Region']['RegionID']);
                            }
                            if (!empty($condition['Goal']['id'])) {
                                $query->where('goal_id', $condition['Goal']['id']);
                            }
                            if (!empty($condition['goal_type'])) {
                                $query->where('goal_mission.goal_type', $condition['goal_type']);
                            }
                            if (!empty($condition['menu_type'])) {
                                $query->where('goal_mission.menu_type', $condition['menu_type']);
                            }
                        })
        
                        ->where(function($query) use ($UserID) {

                            // $query->where('create_by', $UserID);
                            // $query->orWhere('update_by', $UserID);
                            // $query->orWhere('dep_approve_id', $UserID);
                            // $query->orWhere('division_approve_id', $UserID);
                            // $query->orWhere('office_approve_id', $UserID);
                        })
                        // ->whereIn('goal_mission.region_id', $RegionList)
                        ->join('region', 'region.RegionID', '=', 'goal_mission.region_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->orderBy("update_date", 'DESC')
                        ->get();
    }

    public static function getyearGoal($regid, $year) {
        return GoalMission::where('years', $year)
                        ->where('region_id', $regid)
                        ->get()
                        ->toArray();
    }

    public static function checkDuplicate($id, $years, $goal_id, $region_id) {
        return GoalMission::where('id', '<>', $id)
                        ->where('years', $years)
                        ->where('goal_id', $goal_id)
                        ->where('region_id', $region_id)
                        ->first();
    }

    public static function getData($id) {
        return GoalMission::where('id', $id)
                        ->with('goalMissionAvg')
                        ->with('goalMissionHistory')
                        ->first();
    }

    public static function getAvgList($goal_mission_id) {
        return GoalMissionAvg::where('goal_mission_id', $goal_mission_id)
                        ->orderBy('id', 'ASC')
                        ->get()
                        ->toArray();
    }

    public static function getAvgMonth($goal_mission_id, $avgDate) {
        return GoalMissionAvg::where('goal_mission_id', $goal_mission_id)
                        ->where('avg_date', $avgDate)
                        ->first();
    }

    public static function updateData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = GoalMission::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = GoalMission::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDataEditable($id, $editable) {

        $model = GoalMission::find($id);
        $model->editable = $editable;
        return $model->save();
    }

    public static function updateDataApprove($id, $obj) {

        return GoalMission::where('id', $id)->update($obj);
    }

    public static function updateAvg($obj) {
        if (empty($obj['id'])) {
            $model = GoalMissionAvg::create($obj);
            return $model->id;
        } else {
            $model = GoalMissionAvg::where('id', $obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function addHistory($obj) {

        $obj['change_date'] = date('Y-m-d H:i:s');
        $model = GoalMissionHistory::create($obj);
        return $model->id;
    }

    public static function removeData($id) {
        return AccountRole::find($id)->delete();
    }

    public static function getGoaltravel($goalid, $year) {
        $ckid = null;
        return GoalMission::where('years', $year)
                        ->where('goal_id', $goalid)
                        ->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })
                        ->get()
                        ->toArray();
    }

    public static function getMission($goalid, $regid, $year) {
        
        $ckid = null;
        return GoalMission::where('years', $year)
                        ->where('region_id', $regid)
                        ->where('goal_id', $goalid)
                        ->where('office_approve_id', !($ckid))
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', ($ckid));
                            $query->orWhere('office_approve_comment', '');
                        })
                        ->get()
                        ->toArray();
    }

    public static function getMissionforinsem($goalid, $year) {
        $ckid = null;
        return GoalMission::where('years', $year)
                        ->where('goal_id', $goalid)
                        ->where('office_approve_id', !($ckid))
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', ($ckid));
                            $query->orWhere('office_approve_comment', '');
                        })
                        ->join("region", 'goal_mission.region_id', '=', 'region.RegionID')
                        ->get()
                        ->toArray();
    }

    public static function getMissionavg($goal_mission_id, $year, $month) {
        $date = $year . '-' . $month . '-01';
       
        return GoalMissionAvg::where('goal_mission_id', $goal_mission_id)
                        ->where('avg_date', $date)
                        ->get()
                        ->toArray();
    }

    public static function getMissionavgquar($goal_mission_id, $year, $quar) {

        $month = [];
        $years = $year;

        $result = ['amount' => 0, 'price_value' => 0];
        if ($quar == 1) {

            $month = [10, 11, 12];
        } else if ($quar == 2) {
            $month = [1, 2, 3];
        } else if ($quar == 3) {
            $month = [4, 5, 6];
        } else {
            $month = [7, 8, 9];
        }
        foreach ($month as $value) {
            $date = $years . '-' . $value . '-01';
            $missionM = GoalMissionService::getMissionavg($goal_mission_id, $years, $value);

            $result['amount'] += $missionM[0]['amount'];
            $result['price_value'] += $missionM[0]['price_value'];
        }

        return $result;
    }

}
