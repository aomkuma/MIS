<?php
    
    namespace App\Service;
    
    use App\Model\Material;
    use App\Model\MaterialDetail;
    

    use Illuminate\Database\Capsule\Manager as DB;
    
    class MaterialService {

        public static function getMainList($years, $months, $region_id, $material_type_id){
            return Material::select(DB::raw("SUM(amount) AS sum_amount")
                                        ,DB::raw("SUM(`price`) AS sum_baht")
                                        ,"material.update_date")
                            ->join("material_detail", 'material_detail.material_id', '=', 'material.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("region_id", $region_id)
                            ->where('material_type_id', $material_type_id)
                            ->first()
                            ->toArray();
        }

        public static function getDataByID($id){
            return Material::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('materialDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function getData($cooperative_id, $months, $years){
            return Material::where('cooperative_id', $cooperative_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('materialDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }
        
		public static function updateData($obj){
			
			if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Material::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Material::find($obj['id'])->update($obj);
                return $obj['id'];
            }
		}
		public static function updateDetailData($obj){

			if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = MaterialDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = MaterialDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
		}

		public static function removeDetailData($id){
           
            return MaterialDetail::find($id)->delete();
        }

		public static function removeData($id){

		}

        public static function updateDataApprove($id, $obj) {

            return Material::where('id', $id)->update($obj);
        }
    }
