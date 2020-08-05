<?php
    
    namespace App\Service;
    
    use App\Model\LostInProcess;
    use App\Model\LostInProcessDetail;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class LostInProcessService {

        public static function loadDataApprove($UserID){
            return LostInProcess::select("lost_in_process.*", 'factory.factory_name')
                            ->join('factory', 'factory.id', '=', 'lost_in_process.factory_id')
                            ->where(function($query) use ($UserID){
                                $query->where('dep_approve_id' , $UserID);    
                                $query->whereNull('dep_approve_date');    
                            })
                            ->orWhere(function($query) use ($UserID){
                                $query->where('division_approve_id' , $UserID);    
                                $query->whereNull('division_approve_date');    
                            })
                            ->orWhere(function($query) use ($UserID){
                                $query->where('office_approve_id' , $UserID);    
                                $query->whereNull('office_approve_date');    
                            })
                            ->get();
        }

        public static function getMainList($years, $months, $factory_id, $master_type_id){
            return LostInProcess::select(DB::raw("SUM(mis_lost_in_process_detail.amount) AS sum_amount")
                                        ,DB::raw("SUM(mis_lost_in_process_detail.price_value) AS sum_baht")
                                    
                                    ,"lost_in_process.update_date"
                                    ,"office_approve_id"
                                    ,"office_approve_date"
                                    ,"office_approve_comment"
                                )
                            ->join("lost_in_process_detail", 'lost_in_process_detail.lost_in_process_id', '=', 'lost_in_process.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("factory_id", $factory_id)
                            ->where("lost_in_process_type", $master_type_id)
                            ->orderBy('update_date', 'DESC')
                            ->first()
                            ->toArray();
        }
        public static function getMainListreport($years, $months, $factory_id){
            return LostInProcess::select(DB::raw("SUM(mis_lost_in_process_detail.amount) AS sum_amount")
                                        ,DB::raw("SUM(mis_lost_in_process_detail.price_value) AS sum_baht")
                                    
                                    ,"lost_in_process.update_date"
                                    ,"office_approve_id"
                                    ,"office_approve_date"
                                    ,"office_approve_comment"
                                )
                            ->join("lost_in_process_detail", 'lost_in_process_detail.lost_in_process_id', '=', 'lost_in_process.id')
                            ->join("master_goal", 'lost_in_process_detail.lost_in_process_type', '=', 'master_goal.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("lost_in_process.factory_id", $factory_id)
                            ->where("master_goal.factory_id", $factory_id)
                            ->where("master_goal.actives", 'Y')
                            ->where('master_goal.menu_type','การสูญเสียในกระบวนการ')
                            ->orderBy('update_date', 'DESC')
                            ->first()
                            ->toArray();
        }

        public static function getDataByID($id){
            return LostInProcess::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('lostInProcessDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function getData($factory_id, $months, $years){
            return LostInProcess::
                    where(function($query) use ($factory_id){
                        if(!empty($factory_id)){
                            $query->where('factory_id' , $factory_id);
                        }  
                    })
                    // where('factory_id', $factory_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('lostInProcessDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }
        
        public static function updateData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = LostInProcess::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = LostInProcess::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function updateDetailData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = LostInProcessDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = LostInProcessDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeData($id){
            
            LostInProcessDetail::where('lost_in_process_id', $id)->delete();
            return LostInProcess::find($id)->delete();
        }

        public static function removeDetailData($id){
            
            return LostInProcessDetail::find($id)->delete();
        }

        public static function updateDataApprove($id, $obj) {

            return LostInProcess::where('id', $id)->update($obj);
        }

        public static function removeDetailDataByParent($id){
            
            return LostInProcessDetail::where('lost_in_process_id', $id)->delete();
        }
        
    }