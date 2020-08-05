<?php  

namespace App\Model;
class MasterGoal extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'master_goal';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'goal_type'
                  , 'menu_type'
  								, 'goal_name'
                  , 'actives'
  								, 'create_date'
  								, 'update_date'
  							);
  	
}