<?php
    
    namespace App\Service;
    
    use App\Model\Veterinary;
    use App\Model\VeterinaryDetail;
    use App\Model\VeterinaryItem;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class VeterinaryService {



        public static function getMainList($years, $months, $region_id, $farm_type, $item_type){
            return Veterinary::select(DB::raw("SUM(mis_veterinary_item.item_amount) AS sum_amount")
                                    ,"veterinary.update_date")
                            ->join("veterinary_detail", 'veterinary_detail.veterinary_id', '=', 'veterinary.id')
                            ->join("veterinary_item", 'veterinary_detail.id', '=', 'veterinary_item.veterinary_detail_id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("region_id", $region_id)
                            ->where("farm_type", $farm_type)
                            ->where('veterinary_item.item_type', $item_type)
                            ->first()
                            ->toArray();
        }

        public static function getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id, $sub_dairy_farming_id = ''){
            return Veterinary::select(DB::raw("SUM(mis_veterinary_item.item_amount) AS sum_amount")
                                    ,"veterinary.update_date")
                            ->join("veterinary_detail", 'veterinary_detail.veterinary_id', '=', 'veterinary.id')
                            ->join("veterinary_item", 'veterinary_detail.id', '=', 'veterinary_item.veterinary_detail_id')
                            // ->leftJoin("cooperative", 'cooperative.id', '=', 'veterinary.cooperative_id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("veterinary.region_id", $region_id)
                            ->where('veterinary_item.item_type', $item_type)
                            ->where('veterinary.cooperative_id', $cooperative_id)
                            ->where(function($query) use ($dairy_farming_id, $sub_dairy_farming_id){
                                if(!empty($dairy_farming_id)){
                                    $query->where('dairy_farming_id' , $dairy_farming_id);
                                }
                                if(!empty($sub_dairy_farming_id)){
                                    $query->where('sub_dairy_farming_id' , $sub_dairy_farming_id);
                                }
                            })
                            // ->groupBy('cooperative.id')
                            // ->orderBy('cooperative_id', 'ASC')
                            ->first()
                            ->toArray();
        }

        public static function getDataByID($id){
            return Veterinary::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('veterinaryDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                        $query->with('dairyFarming');
                        $query->with('subDairyFarming');
                        $query->with('veterinaryItem');
                    }))
                    ->first();      
        }

        public static function getData($cooperative_id, $months, $years){
            return Veterinary::where('cooperative_id', $cooperative_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('veterinaryDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                        $query->with('dairyFarming');
                        $query->with('subDairyFarming');
                        $query->with('veterinaryItem');
                    }))
                    ->first();      
        }
        
        public static function updateData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Veterinary::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Veterinary::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function updateDetailData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = VeterinaryDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = VeterinaryDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function updateItemData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = VeterinaryItem::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = VeterinaryItem::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeData($id){
            VeterinaryItem::where('veterinary_id', $id)->delete();
            VeterinaryDetail::where('veterinary_id', $id)->delete();
            return Veterinary::find($id)->delete();
        }

        public static function removeDetailData($id){
            VeterinaryItem::where('veterinary_detail_id', $id)->delete();
            return VeterinaryDetail::find($id)->delete();
        }

        public static function removeItemData($id){
            return VeterinaryItem::find($id)->delete();
        }

    }