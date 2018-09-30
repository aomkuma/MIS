<?php
    
    namespace App\Service;
    
    use App\Model\Mineral;
    use App\Model\MineralDetail;
    

    use Illuminate\Database\Capsule\Manager as DB;
    
    class MineralService {

        public static function getDataByID($id){
            return Mineral::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('mineralDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function getData($cooperative_id, $months, $years){
            return Mineral::where('cooperative_id', $cooperative_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('mineralDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

		public static function updateData($obj){
			
			if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Mineral::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Mineral::find($obj['id'])->update($obj);
                return $obj['id'];
            }
		}
		public static function updateDetailData($obj){

			if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = MineralDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = MineralDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
		}

		public static function removeDetailData($id){
           
            return MineralDetail::find($id)->delete();
        }

		public static function removeData($id){

		}
		
    }
