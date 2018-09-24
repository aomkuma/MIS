<?php
    
    namespace App\Service;
    
    use App\Model\Cooperative;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class CooperativeService {

    	public static function getList($actives = ''){
            return Cooperative::where(function($query) use ($actives){
                        if(!empty($actives)){
                            $query->where('actives' , $actives);
                        }
                    })
                    ->orderBy("region_id", 'ASC')
                    ->get();      
        }

        public static function getData($id){
            return Cooperative::find($id);      
        }
        
        public static function updateData($obj){
            if(empty($obj['id'])){
                $model = Cooperative::create($obj);
                return $model->id;
            }else{
                $model = Cooperative::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeData($id){
            return Cooperative::find($id)->delete();
        }

    }