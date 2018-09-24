<?php

    namespace App\Controller;
    
    use App\Service\VeterinaryService;
    use App\Service\CooperativeService;
    use App\Service\DairyFarmingService;

    class VeterinaryController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        private function getLastDayOfMonth($time){
            return $date = date("t", strtotime($time . '-' . '01'));

            // return date("t", $last_day_timestamp);
        }

        private function getMonthName($month){
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
                
                $Result = $this->getMonthDataList($condition, $regions);
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

        private function getMonthDataList($condition, $regions){
            $ym = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
            $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-' .$this->getLastDayOfMonth($ym);
            //exit;
            $fromTime = $condition['YearFrom']  . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) .'-01';
            
            $date1 = new \DateTime($toTime);
            $date2 = new \DateTime($fromTime);
            $diff = $date1->diff($date2);
            $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));

            $curMonth = $condition['MonthFrom'];
            $DataList = [];
            $DataSummary = [];
            for($i = 0; $i <= $diffMonth; $i++){

                // Prepare condition
                $curYear = $condition['YearTo'];
                $beforeYear = $condition['YearTo'] - 1;
                // Loop User Regions
                foreach ($regions as $key => $value) {

                    $region_id = $value['RegionID'];
                    $monthName = $this->getMonthName($curMonth);

                    $data = [];
                    $data['RegionName'] = $value['RegionName'] . ' (สหกรณ์)';
                    $data['Month'] = $monthName;
                    $data['Quarter'] = ($i + 1);
                    $data['Year'] = ($curYear);
                    // get cooperative type
                    $farm_type = 'Cooperative';
                    $item_type = 'โคนม';
                    $CurrentCowData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                    $data['CurrentCowData'] = floatval($CurrentCowData['sum_amount']);
                    $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type); 
                    $data['BeforeCowData'] = floatval($BeforeCowData['sum_amount']);
                    
                    $item_type = 'ค่าบริการ';
                    $CurrentServiceData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                    $data['CurrentServiceData'] = floatval($CurrentServiceData['sum_amount']);
                    $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type); 
                    $data['BeforeServiceData'] = floatval($BeforeServiceData['sum_amount']);
                    
                    $diffCowData = $data['CurrentCowData'] - $data['BeforeCowData'];
                    $data['DiffCowData'] = $diffCowData;

                    $data['DiffCowDataPercentage'] = 0;

                    $diffServiceData = $data['CurrentServiceData'] - $data['BeforeServiceData'];
                    $data['DiffServiceData'] = $diffServiceData;
                    $data['DiffServiceDataPercentage'] = 0;
                    $data['CreateDate'] = $CurrentCowData['update_date'];
                    $data['ApproveDate'] = '';
                    $data['Status'] = '';
                    $data['Description'] = ['farm_type' => $farm_type
                                            ,'item_type' => $item_type
                                            ,'months' => $curMonth
                                            ,'years' => $curYear
                                            ,'region_id' => $region_id
                                            ];

                    array_push($DataList, $data);

                    #### End of cooperative 

                    // get lab type
                    $data = [];
                    $data['RegionName'] = $value['RegionName'] . ' (ห้องปฏิบัติการ)';
                    $data['Month'] = $monthName;
                    $data['Quarter'] = ($i + 1);
                    $data['Year'] = ($curYear);
                    $farm_type = 'lab';
                    $item_type = 'โคนม';
                    $CurrentCowData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                    $data['CurrentCowData'] = floatval($CurrentCowData['sum_amount']);
                    $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type); 
                    $data['BeforeCowData'] = floatval($BeforeCowData['sum_amount']);
                    
                    $item_type = 'ค่าบริการ';
                    $CurrentServiceData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                    $data['CurrentServiceData'] = floatval($CurrentServiceData['sum_amount']);
                    $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type); 
                    $data['BeforeServiceData'] = floatval($BeforeServiceData['sum_amount']);
                    
                    $diffCowData = $data['CurrentCowData'] - $data['BeforeCowData'];
                    $data['DiffCowData'] = $diffCowData;

                    $data['DiffCowDataPercentage'] = 0;

                    $diffServiceData = $data['CurrentServiceData'] - $data['BeforeServiceData'];
                    $data['DiffServiceData'] = $diffServiceData;
                    $data['DiffServiceDataPercentage'] = 0;
                    $data['CreateDate'] = $CurrentCowData['update_date'];
                    $data['ApproveDate'] = '';
                    $data['Status'] = '';
                    $data['Description'] = ['farm_type' => $farm_type
                                            ,'item_type' => $item_type
                                            ,'months' => $curMonth
                                            ,'years' => $curYear
                                            ,'region_id' => $region_id
                                        ];
                    array_push($DataList, $data);

                    $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'];
                    $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'];
                    $DataSummary['SummaryCowPercentage'] = 0;
                    $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'];
                    $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'];
                    $DataSummary['SummaryServicePercentage'] = 0;

                }
                $curMonth++;
            }

            return ['DataList' => $DataList, 'Summary' => $DataSummary];                
        }

        public function getList($request, $response, $args){
            try{
                // error_reporting(E_ERROR);
                // error_reporting(E_ALL);
                // ini_set('display_errors','On');
                $params = $request->getParsedBody();
                $actives = $params['obj']['actives'];

                // group by region first
                $Regions = VeterinaryService::getRegionList();
                // Loop get by region
                $DataList = [];
                foreach ($Regions as $key => $value) {
                    $_List = VeterinaryService::getList($value['region_id'], $actives);
                    $value['Data'] = $_List;
                    array_push($DataList, $value);
                    // $Regions['Data'][] = $_List;
                }
                

                $this->data_result['DATA']['DataList'] = $DataList;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                
                $id = $params['obj']['id'];

                $cooperative_id = $params['obj']['cooperative_id'];
                $months = $params['obj']['months'];
                $years = $params['obj']['years'];

                if(!empty($id)){
                    $_Data = VeterinaryService::getDataByID($id);
                }else{
                    $_Data = VeterinaryService::getData($cooperative_id, $months, $years);
                }
                
                $this->data_result['DATA']['Data'] = $_Data;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function updateData($request, $response, $args){
            
            try{
                // error_reporting(E_ERROR);
                // error_reporting(E_ALL);
                // ini_set('display_errors','On');
                $params = $request->getParsedBody();
                $_Veterinary = $params['obj']['Veterinary'];
                $_VeterinaryDetailList = $params['obj']['VeterinaryDetailList'];
                unset($_Veterinary['veterinary_detail']);
                
                // get region from cooperative id
                $Cooperative = CooperativeService::getData($_Veterinary['cooperative_id']);
                $_Veterinary['region_id'] = $Cooperative['region_id'];

                // print_r($Veterinary);exit;
                $id = VeterinaryService::updateData($_Veterinary);

                // update veterinary detail & item
                foreach ($_VeterinaryDetailList as $key => $value) {
                    $_VeterinaryItemList = $value['veterinary_item'];

                    $DairyFarming = DairyFarmingService::getData($value['dairy_farming_id']);
                    $value['farm_type'] = $DairyFarming['dairy_farming_type'];
                    $value['veterinary_id'] = $id;
                    unset($value['veterinary_item']);
                    $veterinary_detail_id = VeterinaryService::updateDetailData($value);

                    // update item
                    foreach ($_VeterinaryItemList as $_key => $_value) {
                        $_value['veterinary_id'] = $id;
                        $_value['veterinary_detail_id'] = $veterinary_detail_id;
                        $veterinary_item_id = VeterinaryService::updateItemData($_value);
                    }
                }

                $this->data_result['DATA']['id'] = $id;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function removeData($request, $response, $args){
            try{

                $params = $request->getParsedBody();
                $id = $params['obj']['id'];
                $result = VeterinaryService::removeData($id);

                $this->data_result['DATA']['result'] = $result;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function removeDetailData($request, $response, $args){
            try{

                $params = $request->getParsedBody();
                $id = $params['obj']['id'];
                $result = VeterinaryService::removeDetailData($id);

                $this->data_result['DATA']['result'] = $result;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function removeItemData($request, $response, $args){
            try{

                $params = $request->getParsedBody();
                $id = $params['obj']['id'];
                $result = VeterinaryService::removeItemData($id);

                $this->data_result['DATA']['result'] = $result;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }