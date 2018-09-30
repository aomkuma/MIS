<?php  

namespace App\Model;
class TravelDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'travel_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'travel_id'
  								, 'organize'
  								, 'travel_date'
  								, 'adult_pay'
                  , 'child_pay'
                  , 'student_pay'
                  , 'adult_price'
                  , 'child_price'
                  , 'student_price'
                  , 'adult_except'
                  , 'child_except'
                  , 'student_except'
  								, 'create_date'
  								, 'update_date'
  							);


  	
}