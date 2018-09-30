<?php  

namespace App\Model;
class Material extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'material';
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

    public function materialDetail()
    {
        return $this->hasMany('App\Model\materialDetail', 'material_id');
    }
  }