<?php

namespace App\Service;

use App\Model\GoalMission;
use App\Model\GoalMissionAvg;
use App\Model\GoalMissionHistory;
use Illuminate\Database\Capsule\Manager as DB;

class GoalMissionService {

    public static function getList($condition, $UserID) {
        return GoalMission::where(function($query) use ($condition) {
                            if (!empty($condition['Year']['yearText'])) {
                                $query->where('years', $condition['Year']['yearText']);
                            }
                            if (!empty($condition['Region']['RegionID'])) {
                                $query->where('region_id', $condition['Region']['RegionID']);
                            }
                            if (!empty($condition['Goal']['id'])) {
                                $query->where('goal_id', $condition['Goal']['id']);
                            }
                        })
                        ->where(function($query) use ($UserID) {

                            $query->where('create_by', $UserID);
                            $query->orWhere('update_by', $UserID);
                            $query->orWhere('dep_approve_id', $UserID);
                            $query->orWhere('division_approve_id', $UserID);
                            $query->orWhere('office_approve_id', $UserID);
                        })
                        ->join('region', 'region.RegionID', '=', 'goal_mission.region_id')
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
                        ->where('office_approve_id', !is_null($ckid))
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', is_null($ckid));
                            $query->orWhere('office_approve_comment', '');
                        })
                        ->get()
                        ->toArray();
    }

    public static function getMissionforinsem($goalid, $year) {
        $ckid = null;
        return GoalMission::where('years', $year)
                        ->where('goal_id', $goalid)
                        ->where('office_approve_id', !is_null($ckid))
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', is_null($ckid));
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
