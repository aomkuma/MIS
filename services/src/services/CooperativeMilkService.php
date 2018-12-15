<?php
    
    namespace App\Service;
    
    use App\Model\CooperativeMilk;
    use App\Model\CooperativeMilkDetail;
    

    use Illuminate\Database\Capsule\Manager as DB;
    
    class CooperativeMilkService {

        public static function getMainList($years, $months, $field_amount, $field_price) {
        return CooperativeMilk::select(DB::raw("SUM(".$field_amount.") AS sum_amount")
                                , DB::raw("SUM(".$field_price.") AS sum_baht")
                                , "cooperative_milk.update_date")
                        ->join("cooperative_milk_detail", 'cooperative_milk_detail.cooperative_milk_id', '=', 'cooperative_milk.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        // ->where("region_id", $region_id)
                        ->first()
                        ->toArray();
    }

        public static function getDataByID($id){
            return CooperativeMilk::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('cooperativeMilkDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function getData($cooperative_id, $months, $years){
            return CooperativeMilk::where('cooperative_id', $cooperative_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('cooperativeMilkDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function updateData($obj){
            
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = CooperativeMilk::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = CooperativeMilk::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function updateDetailData($obj){

            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = CooperativeMilkDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = CooperativeMilkDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeDetailData($id){
           
            return CooperativeMilkDetail::find($id)->delete();
        }

        public static function removeData($id){

        }
    }
