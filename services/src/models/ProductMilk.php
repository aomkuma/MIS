<?php

namespace App\Model;

class ProductMilk extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'product_milk';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = array('id'
        , 'name'
        , 'editable'
        , 'create_date'
        , 'update_date'
        , 'create_by'
        , 'update_by'
    );

}
