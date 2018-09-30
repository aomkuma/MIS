<?php
    
    namespace App\Service;
    
    use App\Model\SpermSale;
    use App\Model\SpermSaleDetail;
    

    use Illuminate\Database\Capsule\Manager as DB;
    
    class SpermSaleService {

        public static function getDataByID($id){
            return SpermSale::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('spermSaleDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function getData($cooperative_id, $months, $years){
            return SpermSale::where('cooperative_id', $cooperative_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('spermSaleDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

		public static function updateData($obj){
			
			if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = SpermSale::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = SpermSale::find($obj['id'])->update($obj);
                return $obj['id'];
            }
		}
		public static function updateDetailData($obj){

			if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = SpermSaleDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = SpermSaleDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
		}

		public static function removeDetailData($id){
           
            return SpermSaleDetail::find($id)->delete();
        }

		public static function removeData($id){

		}
    }
