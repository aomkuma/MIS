<?php
    
    namespace App\Service;
    
    use App\Model\TrainingCowBreed;
    use App\Model\TrainingCowBreedDetail;
    

    use Illuminate\Database\Capsule\Manager as DB;
    
    class TrainingCowBreedService {

        public static function getDataByID($id){
            return TrainingCowBreed::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('trainingCowBreedDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function getData($cooperative_id, $months, $years){
            return TrainingCowBreed::where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('trainingCowBreedDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

		public static function updateData($obj){
			
			if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = TrainingCowBreed::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = TrainingCowBreed::find($obj['id'])->update($obj);
                return $obj['id'];
            }
		}
		public static function updateDetailData($obj){

			if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = TrainingCowBreedDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = TrainingCowBreedDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
		}

		public static function removeDetailData($id){
           
            return TrainingCowBreedDetail::find($id)->delete();
        }

		public static function removeData($id){

		}
    }
