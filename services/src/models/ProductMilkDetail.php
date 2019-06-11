<?php

namespace App\Model;

class ProductMilkDetail extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'product_milk_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = array('id'
        , 'sub_product_milk_id'
        , 'name'
        , 'agent'
        , 'number_of_package'
        , 'amount'
        , 'actives'
        , 'create_date'
        , 'update_date'
        , 'create_by'
        , 'update_by'
    );

}
