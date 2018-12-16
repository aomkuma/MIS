<?php

    namespace App\Controller;
    
    use App\Service\CowGroupService;
    use App\Service\CooperativeService;

    class CowGroupController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
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
                $regions = $params['obj']['region'];
                
                if($condition['DisplayType'] == 'monthly'){
                    $Result = $this->getMonthDataList($condition, $regions);
                }else if($condition['DisplayType'] == 'quarter'){
                    $Result = $this->getQuarterDataList($condition, $regions);
                }else if($condition['DisplayType'] == 'annually'){
                    $Result = $this->getAnnuallyDataList($condition, $regions);
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

            $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
            $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
            $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28';// .SpermController::getLastDayOfMonth($ym);
            //exit;
            $fromTime = $condition['YearTo']  . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) .'-01';
            
            $date1 = new \DateTime($toTime);
            $date2 = new \DateTime($fromTime);
            $diff = $date1->diff($date2);
            $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
            if ($diffMonth == 0) {
                $diffMonth = 1;
            }else{
                $diffMonth += 1;
            }
            $curMonth = $condition['MonthFrom'];
            $DataList = [];
            $DataSummary = [];
            for($i = 0; $i < $diffMonth; $i++){

                // Prepare condition
                $curYear = $condition['YearTo'];
                $beforeYear = $condition['YearTo'] - 1;
                
                // Loop User Regions
                foreach ($regions as $key => $value) {
                    
                    $region_id = $value['RegionID'];
                    $monthName = SpermController::getMonthName($curMonth);

                    $data = [];
                    $data['RegionName'] = $value['RegionName'];
                    $data['Month'] = $monthName;
                    
                    // get cooperative type

                    $Current = SpermService::getMainList($curYear, $curMonth, $region_id);
                    $data['CurrentAmount'] = floatval($Current['sum_amount']);
                    $data['CurrentBaht'] = floatval($Current['sum_baht']);

                    $Before = SpermService::getMainList($beforeYear, $curMonth, $region_id); 
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

                    $DataSummary['SummaryCurrentSpermAmount'] = $DataSummary['SummaryCurrentSpermAmount'] + $data['CurrentAmount'];
                    $DataSummary['SummaryBeforSpermAmount'] = $DataSummary['SummaryBeforSpermAmount'] + $data['BeforeAmount'];
                    $DataSummary['SummarySpermAmountPercentage'] = 0;
                    $DataSummary['SummaryCurrentSpermIncome'] = $DataSummary['SummaryCurrentSpermIncome'] + $data['CurrentBaht'];
                    $DataSummary['SummaryBeforeSpermIncome'] = $DataSummary['SummaryBeforeSpermIncome'] + $data['BeforeBaht'];
                    $DataSummary['SummarySpermIncomePercentage'] = 0;

                }

                $curMonth++;
            }

            return ['DataList' => $DataList, 'Summary' => $DataSummary];                
        }

        public function getData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                
                $id = $params['obj']['id'];

                $cooperative_id = $params['obj']['cooperative_id'];
                $months = $params['obj']['months'];
                $years = $params['obj']['years'];

                if(!empty($id)){
                    $_Data = CowGroupService::getDataByID($id);
                }else{
                    $_Data = CowGroupService::getData($cooperative_id, $months, $years);
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
                $Cooperative = CooperativeService::getData($_Data['cooperative_id']);
                $_Data['region_id'] = $Cooperative['region_id'];
                // print_r($_Data);
                // exit();

                $id = CowGroupService::updateData($_Data);

 				foreach ($_Detail as $key => $value) {
 					$value['cow_group_id'] = $id;
                	CowGroupService::updateDetailData($value);
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
                $result = CowGroupService::removeDetailData($id);

                $this->data_result['DATA']['result'] = $result;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

    }