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
  								, 'create_date'
  								, 'update_date'
  							);

    public function cowGroupDetail()
    {
        return $this->hasMany('App\Model\CowGroupDetail', 'cow_group_id');
    }
  }