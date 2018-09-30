<?php  

namespace App\Model;
class CowBreed extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'cow_breed';
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

    public function cowbreedDetail()
    {
        return $this->hasMany('App\Model\CowBreedDetail', 'cow_breed_id');
    }
  }