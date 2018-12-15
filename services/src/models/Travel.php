<?php  

namespace App\Model;
class Travel extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'travel';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'days'
  								, 'months'
  								, 'years'
  								, 'create_date'
  								, 'update_date'
  							);

    public function travelDetail()
    {
        return $this->hasMany('App\Model\TravelDetail', 'travel_id');
    }
  	
}