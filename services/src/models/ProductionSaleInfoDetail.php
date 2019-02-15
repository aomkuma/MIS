<?php  

namespace App\Model;
class ProductionSaleInfoDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'production_sale_info_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'production_sale_info_id'
                  , 'production_sale_info_type1'
                  , 'production_sale_info_type2'
                  , 'production_sale_info_type3'
                  , 'amount'
                  , 'price_value'
                  , 'create_date'
                  , 'update_date'
                );
  	
}