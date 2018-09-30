<?php  

namespace App\Model;
class TrainingCowBreed extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'training_cowbreed';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'months'
  								, 'years'
  								, 'create_date'
  								, 'update_date'
  							);

    public function trainingCowBreedDetail()
    {
        return $this->hasMany('App\Model\TrainingCowBreedDetail', 'training_cowbreed_id');
    }
  }