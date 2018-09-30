<?php  

namespace App\Model;
class Mineral extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'mineral';
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

    public function mineralDetail()
    {
        return $this->hasMany('App\Model\MineralDetail', 'mineral_id');
    }
  }