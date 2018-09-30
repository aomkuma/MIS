<?php  

namespace App\Model;
class SpermSale extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'sperm_sale';
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

    public function spermSaleDetail()
    {
        return $this->hasMany('App\Model\SpermSaleDetail', 'sperm_sale_id');
    }
  }