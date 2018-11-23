<?php
    
    namespace App\Service;
    
    use App\Model\Sperm;
    use App\Model\SpermDetail;
    

    use Illuminate\Database\Capsule\Manager as DB;
    
    class SpermService {

        public static function getMainList($years, $months, $region_id){
            return Sperm::select(DB::raw("SUM(amount) AS sum_amount")
                                        ,DB::raw("SUM(`price`) AS sum_baht")
                                        ,"sperm.update_date")
                            ->join("sperm_detail", 'sperm_detail.sperm_id', '=', 'sperm.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("region_id", $region_id)
                            ->first()
                            ->toArray();
        }

        public static function getDetailList($years, $months, $cooperative_id, $sperm_item_id){
            return Sperm::select(DB::raw("SUM(amount) AS sum_amount")
                                        ,DB::raw("SUM(`price`) AS sum_baht"))
                            ->join("sperm_detail", 'sperm_detail.sperm_id', '=', 'sperm.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("cooperative_id", $cooperative_id)
                            ->where("sperm_item_id", $sperm_item_id)
                            ->first()
                            ->toArray();
        }

        public static function getDataByID($id){
            return Sperm::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('spermDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function getData($cooperative_id, $months, $years){
            return Sperm::where('cooperative_id', $cooperative_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('spermDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function updateData($obj){
            
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Sperm::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Sperm::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function updateDetailData($obj){

            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = SpermDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = SpermDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeDetailData($id){
           
            return SpermDetail::find($id)->delete();
        }

        public static function removeData($id){

        }
    }
