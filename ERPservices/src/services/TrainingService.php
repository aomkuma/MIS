<?php
    
    namespace App\Service;
    
    use App\Model\Training;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class TrainingService {

    	public static function getList($actives = ''){
            return Training::where(function($query) use ($actives){
                        if(!empty($actives)){
                            $query->where('actives' , $actives);
                        }
                    })
                    ->orderBy("update_date", 'DESC')
                    ->get();      
        }

        public static function getData($id){
            return Training::find($id);      
        }
        
        public static function updateData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Training::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Training::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeData($id){
            return Training::find($id)->delete();
        }

    }