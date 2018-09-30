<?php  

namespace App\Model;
class Sperm extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'sperm';
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

    public function spermDetail()
    {
        return $this->hasMany('App\Model\SpermDetail', 'sperm_id');
    }
  }