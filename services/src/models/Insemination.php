<?php  

namespace App\Model;
class Insemination extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'insemination';
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

    public function inseminationDetail()
    {
        return $this->hasMany('App\Model\InseminationDetail', 'insemination_id');
    }
  }