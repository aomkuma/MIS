<?php  

namespace App\Model;
class LostWaitSaleDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'lost_wait_sale_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'lost_wait_sale_id'
  								, 'lost_wait_sale_type'
  								, 'amount'
  								, 'price_value'
                  , 'create_date'
                  , 'update_date'
  							);
  }