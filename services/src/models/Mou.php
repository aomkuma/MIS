<?php  

namespace App\Model;
class Mou extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'mou';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'cooperative_id'
  								, 'years'
  								, 'mou_amount'
  								, 'start_date'
  								, 'end_date'
  								, 'create_date'
  								, 'update_date'
  							);

    public function cooperative()
    {
        return $this->hasOne('App\Model\Cooperative', 'id', 'cooperative_id');
    }

    public function mouHistories()
    {
        return $this->hasMany('App\Model\MouHistory', 'mou_id');
    }
  	
}