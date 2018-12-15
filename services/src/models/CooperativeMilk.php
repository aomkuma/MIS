<?php  

namespace App\Model;
class CooperativeMilk extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'cooperative_milk';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'region_id'
                  , 'months'
                  , 'years'
  								, 'create_date'
  								, 'update_date'
  							);

    public function cooperativeMilkDetail()
    {
        return $this->hasMany('App\Model\CooperativeMilkDetail', 'cooperative_milk_id');
    }
  	
}