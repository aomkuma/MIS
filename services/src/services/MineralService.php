<?php
    
    namespace App\Service;
    
    use App\Model\Mineral;
    use App\Model\MineralDetail;
    use App\Model\Food;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class MineralService {

        public static function getMainList($years, $months, $region_id){
            return Mineral::select(DB::raw("SUM(amount) AS sum_weight")
                                        ,DB::raw("SUM(`values`) AS sum_baht")
                                        ,"mineral.update_date")
                            ->join("mineral_detail", 'mineral_detail.mineral_id', '=', 'mineral.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("region_id", $region_id)
                            ->first()
                            ->toArray();
        }

        public static function getMainListByMaster($years, $months, $master_id, $RegionList){
            return Mineral::select(DB::raw("SUM(amount) AS sum_weight")
                                        ,DB::raw("SUM(`values`) AS sum_baht")
                                        ,"mineral.update_date")
                            ->join("mineral_detail", 'mineral_detail.mineral_id', '=', 'mineral.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("food_id", $master_id)
                            ->whereIn("region_id", $RegionList)
                            ->first()
                            ->toArray();
        }

        public static function getDetailList($years, $months, $cooperative_id, $food_id){
            return Mineral::select(DB::raw("SUM(amount) AS sum_weight")
                                        ,DB::raw("SUM(`values`) AS sum_baht"))
                            ->join("mineral_detail", 'mineral_detail.mineral_id', '=', 'mineral.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("cooperative_id", $cooperative_id)
                            ->where("food_id", $food_id)
                            ->first()
                            ->toArray();
        }

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

        public static function getFoodList(){
            return Food::where('actives' , 'Y')
                    ->orderBy("id", 'DESC')
                    ->get();   
        }
		
    }
