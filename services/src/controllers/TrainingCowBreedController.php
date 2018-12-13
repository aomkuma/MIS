<?php

    namespace App\Controller;

    use App\Service\TrainingCowBreedService;
    use App\Service\CooperativeService;
    use App\Service\MasterGoalService;
    

    class TrainingCowBreedController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getLastDayOfMonth($time){
            return $date = date("t", strtotime($time . '-' . '01'));

            // return date("t", $last_day_timestamp);
        }

        public function getMonthName($month){
            switch($month){
                case 1 : $monthTxt = 'มกราคม';break;
                case 2 : $monthTxt = 'กุมภาพันธ์';break;
                case 3 : $monthTxt = 'มีนาคม';break;
                case 4 : $monthTxt = 'เมษายน';break;
                case 5 : $monthTxt = 'พฤษภาคม';break;
                case 6 : $monthTxt = 'มิถุนายน';break;
                case 7 : $monthTxt = 'กรกฎาคม';break;
                case 8 : $monthTxt = 'สิงหาคม';break;
                case 9 : $monthTxt = 'กันยายน';break;
                case 10 : $monthTxt = 'ตุลาคม';break;
                case 11 : $monthTxt = 'พฤษจิกายน';break;
                case 12 : $monthTxt = 'ธันวาคม';break;
            }
            return $monthTxt;
        }

        public function getMainList($request, $response, $args){
            try{
                // error_reporting(E_ERROR);
                // error_reporting(E_ALL);
                // ini_set('display_errors','On');
                $params = $request->getParsedBody();
                $condition = $params['obj']['condition'];
                $region_selected = $condition['Region'];
                $regions = $params['obj']['region'];
                $RegionList = [];
                if(!empty($region_selected)){
                    for($i =0; $i < count($regions); $i++){
                        if($regions[$i]['RegionID'] == $region_selected){
                            array_push($RegionList, $regions[$i]);
                            break;
                        }
                    }
                }else{
                    $RegionList = $regions;
                }
                // print_r($RegionList);
                // exit;
                if($condition['DisplayType'] == 'monthly'){
                    $Result = $this->getMonthDataList($condition, $RegionList);
                }else if($condition['DisplayType'] == 'quarter'){
                    $Result = $this->getQuarterDataList($condition, $RegionList);
                }else if($condition['DisplayType'] == 'annually'){
                    $Result = $this->getAnnuallyDataList($condition, $RegionList);
                }
                $DataList = $Result['DataList'];
                $Summary = $Result['Summary'];
                // print_r($DataList);
                // exit;

                $this->data_result['DATA']['DataList'] = $DataList;
                $this->data_result['DATA']['Summary'] = $Summary;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getMonthDataList($condition, $regions){

            $ymFrom = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
            $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
            $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-' .TrainingCowBreedController::getLastDayOfMonth($ymTo);
            //exit;
            $fromTime = $condition['YearFrom']  . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) .'-01';
            
            $date1 = new \DateTime($toTime);
            $date2 = new \DateTime($fromTime);
            $diff = $date1->diff($date2);
            $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
            if($diffMonth == 0){
                $diffMonth = 1;
            }
            $curMonth = $condition['MonthFrom'];
            $DataList = [];
            $DataSummary = [];

            // get master goal
            $MasterGoalList = MasterGoalService::getList('Y', 'ฝึกอบรม');

            for($i = 0; $i < $diffMonth; $i++){

                // Prepare condition
                $curYear = $condition['YearTo'];
                $beforeYear = $condition['YearTo'] - 1;
                
                // Loop User Regions
                // foreach ($regions as $key => $value) {

                    foreach ($MasterGoalList as $k => $v) {
                            
                        $training_cowbreed_type_id = $v['id'];
                        
                        $region_id = $value['RegionID'];
                        $monthName = TrainingCowBreedController::getMonthName($curMonth);

                        $data = [];
                        $data['RegionName'] = $value['RegionName'];
                        $data['CowBreedName'] = $v['goal_name'];
                        $data['Month'] = $monthName;
                        
                        // get cooperative type

                        $Current = TrainingCowBreedService::getMainList($curYear, $curMonth, $region_id, $training_cowbreed_type_id);
                        $data['CurrentAmount'] = floatval($Current['sum_amount']);
                        $data['CurrentBaht'] = floatval($Current['sum_baht']);

                        $Before = TrainingCowBreedService::getMainList($beforeYear, $curMonth, $region_id, $training_cowbreed_type_id); 
                        $data['BeforeAmount'] = floatval($Before['sum_amount']);
                        $data['BeforeBaht'] = floatval($Before['sum_baht']);

                        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                        $data['DiffAmount'] = $DiffAmount;
                        $data['DiffAmountPercentage'] = 0;

                        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                        $data['DiffBaht'] = $DiffBaht;
                        $data['DiffBahtPercentage'] = 0;

                        $data['CreateDate'] = $CurrentCowService['update_date'];
                        $data['ApproveDate'] = '';
                        $data['Status'] = '';
                        $data['Description'] = ['months' => $curMonth
                                                ,'years' => $curYear
                                                ,'region_id' => $region_id
                                                ];

                        array_push($DataList, $data);

                        $DataSummary['SummaryCurrentCowBreedAmount'] = $DataSummary['SummaryCurrentCowBreedAmount'] + $data['CurrentAmount'];
                        $DataSummary['SummaryBeforeCowBreedAmount'] = $DataSummary['SummaryBeforeCowBreedAmount'] + $data['BeforeAmount'];
                        
                        $DataSummary['SummaryCurrentCowBreedIncome'] = $DataSummary['SummaryCurrentCowBreedIncome'] + $data['CurrentBaht'];
                        $DataSummary['SummaryBeforeCowBreedIncome'] = $DataSummary['SummaryBeforeCowBreedIncome'] + $data['BeforeBaht'];
                        
                        $DataSummary['SummaryCowBreedAmountPercentage'] = $DataSummary['SummaryCowBreedAmountPercentage'] + $DataSummary['SummaryCurrentCowBreedAmount'] + $DataSummary['SummaryBeforeCowBreedAmount'];
                        $DataSummary['SummaryCowBreedIncomePercentage'] = $DataSummary['SummaryCowBreedIncomePercentage'] + $DataSummary['SummaryCurrentCowBreedIncome'] + $DataSummary['SummaryBeforeCowBreedIncome'];
                    }

                // }

                $curMonth++;
            }

            return ['DataList' => $DataList, 'Summary' => $DataSummary];                
        }

        public function getQuarterDataList($condition, $regions){

            // get loop to query
            // $loop = intval($condition['YearTo'] . $condition['QuarterTo']) - intval($condition['YearFrom'] . $condition['QuarterFrom']) + 1;
            $diffYear = ($condition['YearTo'] - $condition['YearFrom']) + 1;
            $cnt = 0;
            $loop = 0;
            $j = $condition['QuarterFrom'];

            for($i = 0; $i < $diffYear; $i++){
                if($cnt == $diffYear){
                    for($k = 0; $k < $condition['QuarterTo']; $k++){
                        $loop++;
                    }
                }else{

                    if($i > 0){
                        $j = 0;
                    }

                    if($diffYear == 1){
                        $length = $condition['QuarterTo'];
                    }else{
                        $length = 4;
                    }
                    for(; $j < $length; $j++){
                        $loop++;
                    }
                }
                $cnt++;
            }
            $loop++;
            $curQuarter = intval($condition['QuarterFrom']);

            if(intval($curQuarter) == 1){
                $curYear = intval($condition['YearFrom']) - 1;  
                $beforeYear = $curYear - 1;
            }else{
                $curYear = intval($condition['YearFrom']);
                $beforeYear = $curYear - 1;
            }

            $DataList = [];
            $DataSummary = [];

            // get master goal
            $MasterGoalList = MasterGoalService::getList('Y', 'ฝึกอบรม');

            for($i = 0; $i < $loop; $i++){

                if($i > 0 && $curQuarter == 2){
                    $curYear++;
                    $beforeYear = $curYear - 1;
                }

                // find month in quarter
                if($curQuarter == 1){
                    $monthList = [10, 11, 12];
                }else if($curQuarter == 2){
                    $monthList = [1, 2, 3];
                }else if($curQuarter == 3){
                    $monthList = [4, 5, 6];
                }else if($curQuarter == 4){
                    $monthList = [7, 8, 9];
                }

                // Loop User Regions
                // foreach ($regions as $key => $value) {

                    foreach ($MasterGoalList as $k => $v) {

                        $training_cowbreed_type_id = $v['id'];
                        $region_id = $value['RegionID'];

                        $SumCurrentAmount = 0;
                        $SumCurrentBaht = 0;
                        $SumBeforeAmount = 0;
                        $SumBeforeBaht = 0;
                         // loop get quarter sum data
                        for($j = 0; $j < count($monthList); $j++){
                            $curMonth = $monthList[$j];
                            
                            $Current = TrainingCowBreedService::getMainList($curYear, $curMonth, $region_id, $training_cowbreed_type_id);
                            $SumCurrentAmount += floatval($Current['sum_amount']);
                            $SumCurrentBaht += floatval($Current['sum_baht']);

                            $Before = TrainingCowBreedService::getMainList($beforeYear, $curMonth, $region_id, $training_cowbreed_type_id); 
                            $SumBeforeAmount += floatval($Before['sum_amount']);
                            $SumBeforeBaht += floatval($Before['sum_baht']);
                        }

                        $data = [];
                        $data['RegionName'] = $value['RegionName'];
                        $data['CowBreedName'] = $v['goal_name'];
                        $data['Quarter'] = $curQuarter . ' (' . (($curQuarter == 1?$curYear + 543 + 1:$curYear + 543)) . ')';

                        $data['CurrentAmount'] = $SumCurrentAmount;
                        $data['CurrentBaht'] = $SumCurrentBaht;
     
                        $data['BeforeAmount'] = $SumBeforeAmount;
                        $data['BeforeBaht'] = $SumBeforeBaht;

                        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                        $data['DiffAmount'] = $DiffAmount;
                        $data['DiffAmountPercentage'] = 0;

                        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                        $data['DiffBaht'] = $DiffBaht;
                        $data['DiffBahtPercentage'] = 0;

                        $data['CreateDate'] = $CurrentCowService['update_date'];
                        $data['ApproveDate'] = '';
                        $data['Status'] = '';
                        $data['Description'] = ['months' => $curMonth
                                                ,'years' => $curYear
                                                ,'region_id' => $region_id
                                                ];

                        array_push($DataList, $data);

                        $DataSummary['SummaryCurrentCowBreedAmount'] = $DataSummary['SummaryCurrentCowBreedAmount'] + $data['CurrentAmount'];
                        $DataSummary['SummaryBeforeCowBreedAmount'] = $DataSummary['SummaryBeforeCowBreedAmount'] + $data['BeforeAmount'];
                        
                        $DataSummary['SummaryCurrentCowBreedIncome'] = $DataSummary['SummaryCurrentCowBreedIncome'] + $data['CurrentBaht'];
                        $DataSummary['SummaryBeforeCowBreedIncome'] = $DataSummary['SummaryBeforeCowBreedIncome'] + $data['BeforeBaht'];
                        
                        $DataSummary['SummaryCowBreedAmountPercentage'] = $DataSummary['SummaryCowBreedAmountPercentage'] + $DataSummary['SummaryCurrentCowBreedAmount'] + $DataSummary['SummaryBeforeCowBreedAmount'];
                        $DataSummary['SummaryCowBreedIncomePercentage'] = $DataSummary['SummaryCowBreedIncomePercentage'] + $DataSummary['SummaryCurrentCowBreedIncome'] + $DataSummary['SummaryBeforeCowBreedIncome'];
                    }
                // }
                
                $curQuarter++;
                if($curQuarter > 4){
                    $curQuarter = 1;
                }
            }

            return ['DataList' => $DataList, 'Summary' => $DataSummary];
        }

        public function getAnnuallyDataList($condition, $regions){
            
            $loop = intval($condition['YearTo']) - intval($condition['YearFrom']) + 1;
            $curYear = $condition['YearFrom'];
            
            $beforeYear = $calcYear - 1;
            $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

            $DataList = [];
            $DataSummary = [];
            $curYear = $condition['YearFrom'];

            // get master goal
            $MasterGoalList = MasterGoalService::getList('Y', 'ฝึกอบรม');

            for($i = 0; $i < $loop; $i++){
                
                // Loop User Regions
                // foreach ($regions as $key => $value) {

                    foreach ($MasterGoalList as $k => $v) {

                        $training_cowbreed_type_id = $v['id'];
                        $region_id = $value['RegionID'];

                        
                        $calcYear = intval($curYear) - 1;

                        $SumCurrentAmount = 0;
                        $SumCurrentBaht = 0;
                        $SumBeforeAmount = 0;
                        $SumBeforeBaht = 0;
                         // loop get quarter sum data
                        for($j = 0; $j < 12; $j++){
                            $curMonth = $monthList[$j];
                            
                            $Current = TrainingCowBreedService::getMainList($curYear, $curMonth, $region_id, $training_cowbreed_type_id);
                            $SumCurrentAmount += floatval($Current['sum_amount']);
                            $SumCurrentBaht += floatval($Current['sum_baht']);

                            $Before = TrainingCowBreedService::getMainList($beforeYear, $curMonth, $region_id, $training_cowbreed_type_id); 
                            $SumBeforeAmount += floatval($Before['sum_amount']);
                            $SumBeforeBaht += floatval($Before['sum_baht']);
                        }

                        $data = [];
                        $data['RegionName'] = $value['RegionName'];
                        $data['CowBreedName'] = $v['goal_name'];
                        $data['Year'] = $curYear + 543;

                        $data['CurrentAmount'] = $SumCurrentAmount;
                        $data['CurrentBaht'] = $SumCurrentBaht;
     
                        $data['BeforeAmount'] = $SumBeforeAmount;
                        $data['BeforeBaht'] = $SumBeforeBaht;

                        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                        $data['DiffAmount'] = $DiffAmount;
                        $data['DiffAmountPercentage'] = 0;

                        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                        $data['DiffBaht'] = $DiffBaht;
                        $data['DiffBahtPercentage'] = 0;

                        $data['CreateDate'] = $CurrentCowService['update_date'];
                        $data['ApproveDate'] = '';
                        $data['Status'] = '';
                        $data['Description'] = ['months' => $curMonth
                                                ,'years' => $curYear
                                                ,'region_id' => $region_id
                                                ];

                        array_push($DataList, $data);

                        $DataSummary['SummaryCurrentCowBreedAmount'] = $DataSummary['SummaryCurrentCowBreedAmount'] + $data['CurrentAmount'];
                        $DataSummary['SummaryBeforeCowBreedAmount'] = $DataSummary['SummaryBeforeCowBreedAmount'] + $data['BeforeAmount'];
                        
                        $DataSummary['SummaryCurrentCowBreedIncome'] = $DataSummary['SummaryCurrentCowBreedIncome'] + $data['CurrentBaht'];
                        $DataSummary['SummaryBeforeCowBreedIncome'] = $DataSummary['SummaryBeforeCowBreedIncome'] + $data['BeforeBaht'];
                        
                        $DataSummary['SummaryCowBreedAmountPercentage'] = $DataSummary['SummaryCowBreedAmountPercentage'] + $DataSummary['SummaryCurrentCowBreedAmount'] + $DataSummary['SummaryBeforeCowBreedAmount'];
                        $DataSummary['SummaryCowBreedIncomePercentage'] = $DataSummary['SummaryCowBreedIncomePercentage'] + $DataSummary['SummaryCurrentCowBreedIncome'] + $DataSummary['SummaryBeforeCowBreedIncome'];
                    }
                // }
                
                $curYear++;
            }

            return ['DataList' => $DataList, 'Summary' => $DataSummary];
        }        

        public function getData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                
                $id = $params['obj']['id'];

                
                $months = $params['obj']['months'];
                $years = $params['obj']['years'];

                if(!empty($id)){
                    $_Data = TrainingCowBreedService::getDataByID($id);
                }else{
                    $_Data = TrainingCowBreedService::getData($months, $years);
                }
                
                $this->data_result['DATA']['Data'] = $_Data;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function updateData($request, $response, $args){
            // error_reporting(E_ERROR);
            //     error_reporting(E_ALL);
            //     ini_set('display_errors','On');
            try{
                $params = $request->getParsedBody();
                $_Data = $params['obj']['Data'];
                 $_Detail = $params['obj']['Detail'];

                 // get region from cooperative id
                // $Cooperative = CooperativeService::getData($_Data['cooperative_id']);
                // $_Data['region_id'] = $Cooperative['region_id'];
                // print_r($_Data);
                // exit();

                $id = TrainingCowBreedService::updateData($_Data);
             
 				foreach ($_Detail as $key => $value) {
 					$value['training_cowbreed_id'] = $id;
                	TrainingCowBreedService::updateDetailData($value);
                }

     //           
                $this->data_result['DATA']['id'] = $id;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
        public function removeDetailData($request, $response, $args){
            try{

                $params = $request->getParsedBody();
                $id = $params['obj']['id'];
                $result = TrainingCowBreedService::removeDetailData($id);

                $this->data_result['DATA']['result'] = $result;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

    }