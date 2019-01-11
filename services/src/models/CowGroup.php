<?php  

namespace App\Model;
class CowGroup extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'cow_group';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'cooperative_id'
  								, 'region_id'
  								, 'months'
  								, 'years'
                  , 'go_factory_weight'
                  , 'go_factory_price'
                  , 'go_factory_values'
                  , 'cow_weight'
                  , 'cow_price'
                  , 'cow_values'
                  , 'decline_weight'
                  , 'decline_price'
                  , 'decline_values'
                  , 'cow_group_avg'
  								, 'create_date'
  								, 'update_date'
  							);

    public function cowGroupDetail()
    {
        return $this->hasMany('App\Model\CowGroupDetail', 'cow_group_id');
    }
  }