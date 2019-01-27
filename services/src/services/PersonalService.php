<?php

namespace App\Service;

use App\Model\AttachFile;
use App\Model\DataRows;
use App\Model\DataSheets;
use App\Model\DataRowsheet3;
use Illuminate\Database\Capsule\Manager as DB;

class PersonalService {

    public static function getList($years, $months, $positiontype) {
        return DataRows::where("years", $years)
                        ->where("months", $months)
                        ->where("positiontype", $positiontype)
                        ->orderBy("id", 'ASC')
                        ->get();
    }

    public static function getPositiontype() {
        return DataRows::select(DB::raw("positiontype"))
                        ->groupBy("positiontype")
                        ->get()
                        ->toArray();
    }

    public static function getMainList($years, $months, $positiontype) {
        return DataRows::where("year", $years)
                        ->where("month", $months)
                        ->where("seq", 1)
                        ->where("positiontype", $positiontype)
                        ->orderBy("id", 'ASC')
                        ->get()
                        ->toArray();
    }
      public static function getMainListsheet3($years, $months) {
        return DataRowsheet3::where("year", $years)
                        ->where("month", $months)
                        
                        
                        ->orderBy("id", 'ASC')
                        ->get()
                        ->toArray();
    }

}
