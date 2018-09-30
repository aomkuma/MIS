<?php
    
    namespace App\Service;
    
    use App\Model\CowBreed;
    use App\Model\CowBreedDetail;
    

    use Illuminate\Database\Capsule\Manager as DB;
    
    class CowBreedService {

        public static function getDataByID($id){
            return CowBreed::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('cowbreedDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function getData($cooperative_id, $months, $years){
            return CowBreed::where('cooperative_id', $cooperative_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('cowbreedDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

		public static function updateData($obj){
			
			if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = CowBreed::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = CowBreed::find($obj['id'])->update($obj);
                return $obj['id'];
            }
		}
		public static function updateDetailData($obj){

			if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = CowBreedDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = CowBreedDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
		}

		public static function removeDetailData($id){
           
            return CowBreedDetail::find($id)->delete();
        }

		public static function removeData($id){

		}
    }
