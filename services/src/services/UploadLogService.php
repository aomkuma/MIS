<?php

namespace App\Service;

use App\Model\UploadLog;

class UploadLogService {

    public static function loadList($menu_type) {
        return UploadLog::where('menu_type', $menu_type)
                        ->get();
    }

    public static function updateLog($obj){
    	$obj['create_date'] = date('Y-m-d H:i:s');
        $model = UploadLog::create($obj);
        return $model->id;
    }

}
