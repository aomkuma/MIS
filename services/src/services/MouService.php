<?php
    
    namespace App\Service;
    
    use App\Model\Mou;
    use App\Model\MouHistory;
    use App\Model\Cooperative;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class MouService {

        public static function getRegionList(){
            return Cooperative::select('region_id')
                    ->groupBy('region_id')
                    ->orderBy("region_id", 'ASC')
                    ->get()->toArray();      
        }

    	public static function getList($region_id, $actives = ''){
            return Mou::where(function($query) use ($actives){
                        if(!empty($actives)){
                            $query->where('actives' , $actives);
                        }
                    })
                    ->where('cooperative.region_id', $region_id)
                    ->join('cooperative', 'cooperative.id', '=', 'mou.cooperative_id')
                    // ->with('cooperative')
                    ->orderBy("update_date", 'DESC')
                    ->get();      
        }

        public static function getData($id){
            return Mou::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('mouHistories' => function($query){
                        $query->orderBy('update_date', 'DESC');
                        $query->with('cooperative');
                    }))
                    ->first()->toArray();      
        }
        
        public static function updateData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Mou::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Mou::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function addHistoryData($obj){
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = MouHistory::create($obj);
            return $model->id;
        }

        public static function removeData($id){
            return Mou::find($id)->delete();
        }

    }