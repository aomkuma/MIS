<?php  

namespace App\Model;
class Veterinary extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'veterinary';
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

    public function veterinaryDetail()
    {
        return $this->hasMany('App\Model\veterinaryDetail', 'veterinary_id');
    }

    public function cooperative()
    {
        return $this->hasOne('App\Model\Cooperative', 'id', 'cooperative_id');
    }

    public function region()
    {
        return $this->hasOne('App\Model\Region', 'RegionID', 'region_id');
    }
  	
}