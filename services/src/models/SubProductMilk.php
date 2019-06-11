<?php

namespace App\Model;

class SubProductMilk extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'subproduct_milk';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = array('id'
        ,'product_milk_id'
        , 'name'
        , 'agent'
        , 'actives'
        , 'create_date'
        , 'update_date'
        , 'create_by'
        , 'update_by'
    );

}
