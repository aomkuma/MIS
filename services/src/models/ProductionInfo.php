<?php  

namespace App\Model;
class ProductionInfo extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'production_info';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'factory_id'
  								, 'months'
  								, 'years'
  								, 'create_date'
  								, 'update_date'
                  , 'dep_approve_id'
                  , 'dep_approve_date'
                  , 'dep_approve_comment'
                  , 'division_approve_id'
                  , 'division_approve_date'
                  , 'division_approve_comment'
                  , 'office_approve_id'
                  , 'office_approve_date'
                  , 'office_approve_comment'
                  , 'dep_approve_name'
                  , 'division_approve_name'
                  , 'office_approve_name'
  							);

    public function productionInfoDetail()
    {
        return $this->hasMany('App\Model\ProductionInfoDetail', 'production_info_id');
    }
  	
}