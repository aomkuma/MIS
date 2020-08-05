<?php
    
    namespace App\Service;
    
    use App\Model\Veterinary;
    use App\Model\Insemination;
    use App\Model\Mineral;
    use App\Model\Sperm;
    use App\Model\SpermSale;
    use App\Model\Material;
    use App\Model\CowBreed;
    use App\Model\TrainingCowBreed;
    use App\Model\Travel;
    use App\Model\CooperativeMilk;
    use App\Model\CowGroup;
    use App\Model\GoalMission;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class ChartService {

        public static function getGoalMissionData($years, $menu_type){
            return GoalMission::where('menu_type', $menu_type)
                    ->where('years', $years)
                    ->orderBy('id', 'DESC')
                    ->first();      
        }

    	public static function getVeterinaryData($years){
            return Veterinary::select(DB::raw("SUM(mis_veterinary_item.item_amount) AS sum_amount"))
                            ->join("veterinary_detail", 'veterinary_detail.veterinary_id', '=', 'veterinary.id')
                            ->join("veterinary_item", 'veterinary_detail.id', '=', 'veterinary_item.veterinary_detail_id')
                            ->where("years", $years)
                            ->first();
        }

        public static function getInseminationData($years) {
            return Insemination::select(DB::raw("SUM(cow_amount) AS sum_amount")
                                , DB::raw("SUM(service_cost + sperm_cost + material_cost) AS sum_value"))
                        ->join("insemination_detail", 'insemination_detail.insemination_id', '=', 'insemination.id')
                        ->where("years", $years)
                        ->first();
        }

        public static function getMineralData($years) {
            return Mineral::select(DB::raw("SUM(amount) AS sum_amount")
                            ,DB::raw("SUM(`values`) AS sum_value"))
                        ->join("mineral_detail", 'mineral_detail.mineral_id', '=', 'mineral.id')
                        ->where("years", $years)
                        ->first();
        }

        public static function getSpermData($years) {
            return Sperm::select(DB::raw("SUM(amount) AS sum_amount")
                                , DB::raw("SUM(`price`) AS sum_value"))
                        ->join("sperm_detail", 'sperm_detail.sperm_id', '=', 'sperm.id')
                        ->where("years", $years)
                        ->first();
        }

        public static function getSpermSaleData($years) {
            return SpermSale::select(DB::raw("SUM(amount) AS sum_amount")
                                , DB::raw("SUM(`values`) AS sum_value"))
                        ->join("sperm_sale_detail", 'sperm_sale_detail.sperm_sale_id', '=', 'sperm_sale.id')
                        ->where("years", $years)
                        ->first();
        }

        public static function getMaterialData($years) {
            return Material::select(DB::raw("SUM(amount) AS sum_amount")
                                        ,DB::raw("SUM(`price`) AS sum_value")
                                        ,"material.update_date")
                            ->join("material_detail", 'material_detail.material_id', '=', 'material.id')
                            ->where("years", $years)
                            ->first();
        }

        public static function getCowBreedData($years) {
            return CowBreed::select(DB::raw("SUM(amount) AS sum_amount")
                                        ,DB::raw("SUM(`price`) AS sum_value"))
                            ->join("cow_breed_detail", 'cow_breed_detail.cow_breed_id', '=', 'cow_breed.id')
                            ->where("years", $years)
                            ->first();
        }

        public static function getTrainingCowBreedData($years) {
            return TrainingCowBreed::select(DB::raw("SUM(amount) AS sum_amount")
                                        ,DB::raw("SUM(`values`) AS sum_value"))
                            ->join("training_cowbreed_detail", 'training_cowbreed_detail.training_cowbreed_id', '=', 'training_cowbreed.id')
                            ->where("years", $years)
                            ->first();
        }

        public static function getTravelData($years) {
            return Travel::select(DB::raw("SUM(total_amount) AS sum_amount")
                                , DB::raw("SUM(total_prices) AS sum_value"))
                            ->join("travel_detail", 'travel_detail.travel_id', '=', 'travel.id')
                            ->where("years", $years)
                            ->first();
        }

        public static function getCooperativeMilkData($years) {
            return CooperativeMilk::select(DB::raw("SUM(milk_amount) AS sum_amount")
                                , DB::raw("SUM(total_values) AS sum_value"))
                            ->join("cooperative_milk_detail", 'cooperative_milk_detail.cooperative_milk_id', '=', 'cooperative_milk.id')
                            ->where("years", $years)
                            ->first();
        }

        public static function getCowGroupData($years) {
            return CowGroup::select(DB::raw("SUM(go_factory_weight) AS sum_amount")
                                , DB::raw("SUM(go_factory_values) AS sum_value"))
                            // ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group.id')
                            ->where("years", $years)
                            ->first();
        }

    }