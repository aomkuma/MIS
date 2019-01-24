<?php  

namespace App\Model;
class GoalMission extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'goal_mission';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'region_id'
  								, 'years'
                  , 'goal_type'
                  , 'menu_type'
  								, 'goal_id'
  								, 'amount'
  								, 'unit'
  								, 'price_value'
  								, 'editable'
                  , 'dep_approve_id'
                  , 'dep_approve_date'
                  , 'sep_approve_comment'
                  , 'division_approve_id'
                  , 'division_approve_date'
                  , 'division_approve_comment'
                  , 'editable'
                  , 'editable'
                  , 'editable'
  								, 'create_date'
  								, 'update_date'
  								, 'create_by'
  								, 'update_by'
  							);

  	public function goalMissionAvg()
    {
  		return $this->hasMany('App\Model\GoalMissionAvg', 'goal_mission_id');
    }

    public function goalMissionHistory()
    {
  		return $this->hasMany('App\Model\GoalMissionHistory', 'goal_mission_id');
    }

  	
}