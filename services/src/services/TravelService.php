<?php
    
    namespace App\Service;
    
    use App\Model\Travel;
    use App\Model\TravelDetail;
    

    use Illuminate\Database\Capsule\Manager as DB;
    
    class TravelService {

        public static function getDataByID($id){
            return Travel::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('travelDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function getData($months, $years){
            return Travel::where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('travelDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function updateData($obj){
            
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Travel::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Travel::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function updateDetailData($obj){

            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = TravelDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = TravelDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeDetailData($id){
           
            return TravelDetail::find($id)->delete();
        }

        public static function removeData($id){

        }
    }
