<?php

    namespace App\Controller;
    
    use App\Service\SpermService;
    use App\Service\CooperativeService;

    class SpermController extends Controller {
        
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

        public function loadDataApprove($request, $response, $args) {
        try {
           
            $params = $request->getParsedBody();
            $user_session = $params['user_session'];
            
            $Data = SpermService::loadDataApprove($user_session['UserID']);
            
            $this->data_result['DATA']['DataList'] = $Data;
            
            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
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

                    $data['CreateDate'] = $Current['update_date'];
                    $data['ApproveDate'] = $Current['office_approve_date'];
                    if(!empty($Current['office_approve_id'])){
                        if(empty($Current['office_approve_comment'])){
                            $data['Status'] = 'อนุมัติ';        
                        }else{
                            $data['Status'] = 'ไม่อนุมัติ';        
                        }
                    }
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

        public function getQuarterDataList($condition, $regions){

            // get loop to query
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
                foreach ($regions as $key => $value) {
                    $region_id = $value['RegionID'];

                    $SumCurrentAmount = 0;
                    $SumCurrentBaht = 0;
                    $SumBeforeAmount = 0;
                    $SumBeforeBaht = 0;
                    $UpdateDate = '';
                    $ApproveDate = '';
                    $ApproveComment = '';
                     // loop get quarter sum data
                    for($j = 0; $j < count($monthList); $j++){
                        $curMonth = $monthList[$j];
                        
                        $Current = SpermService::getMainList($curYear, $curMonth, $region_id);
                        $SumCurrentAmount += floatval($Current['sum_amount']);
                        $SumCurrentBaht += floatval($Current['sum_baht']);

                        $Before = SpermService::getMainList($beforeYear, $curMonth, $region_id); 
                        $SumBeforeAmount += floatval($Before['sum_amount']);
                        $SumBeforeBaht += floatval($Before['sum_baht']);

                        if (!empty($Current['update_date'])) {
                            $UpdateDate = $Current['update_date'];
                        }
                        if (!empty($Current['office_approve_id'])) {
                            $ApproveDate = $Current['office_approve_date'];
                        }
                        if (!empty($Current['office_approve_comment'])) {
                            $ApproveComment = $Current['office_approve_comment'];
                        }
                    }

                    $data = [];
                    $data['RegionName'] = $value['RegionName'];
                    $data['Quarter'] = $curQuarter;

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

                    $data['CreateDate'] = $UpdateDate;
                    $data['ApproveDate'] = $ApproveDate;
                    if(!empty($ApproveDate)){
                        if(empty($ApproveComment)){
                            $data['Status'] = 'อนุมัติ';        
                        }else{
                            $data['Status'] = 'ไม่อนุมัติ';        
                        }
                    }
                    $data['Description'] = ['months' => $curMonth
                                            ,'years' => $curYear
                                            ,'quarter' => $curQuarter
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

            for($i = 0; $i < $loop; $i++){
                
                // Loop User Regions
                foreach ($regions as $key => $value) {
                    $region_id = $value['RegionID'];

                    $curYear = $condition['YearFrom'];
                    $calcYear = intval($curYear) - 1;

                    $SumCurrentAmount = 0;
                    $SumCurrentBaht = 0;
                    $SumBeforeAmount = 0;
                    $SumBeforeBaht = 0;
                    $UpdateDate = '';
                    $ApproveDate = '';
                    $ApproveComment = '';
                     // loop get quarter sum data
                    for($j = 0; $j < 12; $j++){
                        $curMonth = $monthList[$j];
                        
                        $Current = SpermService::getMainList($curYear, $curMonth, $region_id);
                        $SumCurrentAmount += floatval($Current['sum_amount']);
                        $SumCurrentBaht += floatval($Current['sum_baht']);

                        $Before = SpermService::getMainList($beforeYear, $curMonth, $region_id); 
                        $SumBeforeAmount += floatval($Before['sum_amount']);
                        $SumBeforeBaht += floatval($Before['sum_baht']);

                        if (!empty($Current['update_date'])) {
                            $UpdateDate = $Current['update_date'];
                        }
                        if (!empty($Current['office_approve_id'])) {
                            $ApproveDate = $Current['office_approve_date'];
                        }
                        if (!empty($Current['office_approve_comment'])) {
                            $ApproveComment = $Current['office_approve_comment'];
                        }
                    }

                    $data = [];
                    $data['RegionName'] = $value['RegionName'];
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

                    $data['CreateDate'] = $UpdateDate;
                    $data['ApproveDate'] = $ApproveDate;
                    if(!empty($ApproveDate)){
                        if(empty($ApproveComment)){
                            $data['Status'] = 'อนุมัติ';        
                        }else{
                            $data['Status'] = 'ไม่อนุมัติ';        
                        }
                    }
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
                
                $curYear++;
            }

            return ['DataList' => $DataList, 'Summary' => $DataSummary];
        }

        public function getDetailList($request, $response, $args){
            try{
                // error_reporting(E_ERROR);
                // error_reporting(E_ALL);
                // ini_set('display_errors','On');
                $params = $request->getParsedBody();
                $condition = $params['obj']['condition'];
                $regions = $params['obj']['region'];
                $description = $params['obj']['description'];
                
                if($condition['DisplayType'] == 'monthly'){
                    $Result = $this->getMonthDetailList($condition, $regions, $description);
                }else if($condition['DisplayType'] == 'quarter'){
                    $Result = $this->getQuarterDetailList($condition, $regions, $description);
                }else if($condition['DisplayType'] == 'annually'){
                    $Result = $this->getAnnuallyDetailList($condition, $regions, $description);
                }
                $DataList = $Result['DataList'];
                $Item = $Result['Item'];
                $ItemUnit = $Result['ItemUnit'];
                $SummaryList = $Result['SummaryList'];
                // print_r($DataList);
                // exit;

                $this->data_result['DATA']['DetailList'] = $DataList;
                $this->data_result['DATA']['Item'] = $Item;
                $this->data_result['DATA']['ItemUnit'] = $ItemUnit;
                $this->data_result['DATA']['SummaryList'] = $SummaryList;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        private function getMonthDetailList($condition, $regions, $description){
            $years = $description['years']; 
            $months = $description['months']; 
            $region_id = $description['region_id'];

            $FoodList = SpermService::getFoodList();
            $CooperativeList = CooperativeService::getListByRegion($region_id);

            $DataList = [];
            $SummaryList = [];

            foreach ($CooperativeList as $key => $value) {
                
                $cooperative_id = $value['id'];

                $data = [];
                $data['RegionName'] = $value['cooperative_name'];
                $data['ValueList'] = [];
                $sub_data = [];

                $cnt = 0;
                foreach ($FoodList as $_key => $_value) {

                    $food_id = $_value['id'];

                    $Total = SpermService::getDetailList($years, $months, $cooperative_id, $food_id);
                    
                    $sub_data[]['values'] = floatval($Total['sum_amount']);
                    $SummaryList[$cnt]['values'] = floatval($SummaryList[$cnt]['values']) + floatval($Total['sum_amount']);
                    $cnt++;

                    $sub_data[]['values'] = floatval($Total['sum_baht']);
                    $SummaryList[$cnt]['values'] = floatval($SummaryList[$cnt]['values']) + floatval($Total['sum_baht']);
                    $cnt++;

                }
                $data['ValueList'] = $sub_data;

                array_push($DataList, $data);
            }

            // gen food header list
            $Item = [];
            $ItemUnit = [];
            foreach ($FoodList as $_key => $_value) {
                $data = [];
                $data['label'] = $_value['name'];
                $unit = [];
                $unit[]['label'] = '';
                $unit[]['label'] = '';
                $data['unit'] = $unit;
                array_push($Item, $data);

                $ItemUnit[]['label'] = 'กิโลกรัม';
                $ItemUnit[]['label'] = 'มูลค่า (บาท)';
            }

            return ['DataList' => $DataList, 'Item' => $Item, 'ItemUnit' => $ItemUnit, 'SummaryList' =>$SummaryList];
        }

        public function getData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                
                $id = $params['obj']['id'];

                $cooperative_id = $params['obj']['cooperative_id'];
                $months = $params['obj']['months'];
                $years = $params['obj']['years'];

                if(!empty($id)){
                    $_Data = SpermService::getDataByID($id);
                }else{
                    $_Data = SpermService::getData($cooperative_id, $months, $years);
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
            $URL = '127.0.0.1';
            try{
                $params = $request->getParsedBody();
                $_Data = $params['obj']['Data'];

                $user_session = $params['user_session'];
            
                $OrgID = $user_session['OrgID'];

                $HeaderData = $this->do_post_request('http://' . $URL . '/dportal/dpo/public/mis/get/org/header/', "POST", ['OrgID' => $OrgID, 'Type' => 'OWNER']);
                $HeaderData = json_decode(trim($HeaderData), TRUE);
                // print_r($HeaderData);exit;
                if($HeaderData['data']['DATA']['Header']['OrgType'] == 'DEPARTMENT'){
                    $_Data['dep_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                    $data['dep_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];

                }else if($HeaderData['data']['DATA']['Header']['OrgType'] == 'DIVISION'){
                    $_Data['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                    $data['division_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];

                }else if($HeaderData['data']['DATA']['Header']['OrgType'] == 'OFFICE'){
                    $_Data['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                    $data['office_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];
                }
                
                $_Detail = $params['obj']['Detail'];

                 // get region from cooperative id
                $Cooperative = CooperativeService::getData($_Data['cooperative_id']);
                $_Data['region_id'] = $Cooperative['region_id'];
                // print_r($_Data);
                // exit();

                $id = SpermService::updateData($_Data);

 				foreach ($_Detail as $key => $value) {
 					$value['sperm_id'] = $id;
                	SpermService::updateDetailData($value);
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
                $result = SpermService::removeDetailData($id);

                $this->data_result['DATA']['result'] = $result;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function updateDataApprove($request, $response, $args){
            // $URL = '172.23.10.224';
            $URL = '127.0.0.1';
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $id = $params['obj']['id'];
                $ApproveStatus = $params['obj']['ApproveStatus'];
                $ApproveComment = $params['obj']['ApproveComment'];
                $OrgType = $params['obj']['OrgType'];
                $approval_id = $user_session['UserID'];
                $OrgID = $user_session['OrgID'];

                if($ApproveStatus == 'approve'){
                    // http post to dpo database to retrieve division's header
                    $HeaderData = $this->do_post_request('http://' . $URL . '/dportal/dpo/public/mis/get/org/header/', "POST", ['UserID' => $approval_id, 'OrgID' => $OrgID]);
                    $HeaderData = json_decode(trim($HeaderData), TRUE);
                    
                    $data = [];
                    $ApproveComment = '';

                    if($OrgType == 'dep'){
                        $data['dep_approve_date'] = date('Y-m-d H:i:s');
                        $data['dep_approve_comment'] = $ApproveComment;
                        $data['dep_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];

                        $data['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                    }else if($OrgType == 'division'){
                        $data['division_approve_date'] = date('Y-m-d H:i:s');
                        $data['division_approve_comment'] = $ApproveComment;
                        $data['division_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];

                        $data['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                    }else if($OrgType == 'office'){
                        $data['office_approve_date'] = date('Y-m-d H:i:s');
                        $data['office_approve_comment'] = $ApproveComment;
                        $data['office_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                        
                    }
                }else if($ApproveStatus == 'reject'){

                    if($OrgType == 'dep'){
                        $data['dep_approve_date'] = date('Y-m-d H:i:s');                  
                        $data['dep_approve_comment'] = $ApproveComment;
                        $data['dep_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                    }else if($OrgType == 'division'){
                        $data['dep_approve_date'] = NULL;                  
                        $data['dep_approve_comment'] = NULL;
                        
                        $data['division_approve_id'] = NULL;
                        $data['division_approve_date'] = date('Y-m-d H:i:s');
                        $data['division_approve_comment'] = $ApproveComment;
                        $data['division_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                    }else if($OrgType == 'office'){

                        $data['dep_approve_date'] = NULL;                  
                        $data['dep_approve_comment'] = NULL;
                        
                        $data['division_approve_id'] = NULL;
                        $data['division_approve_date'] = NULL;
                        $data['division_approve_comment'] = NULL;

                        $data['office_approve_id'] = NULL;    
                        $data['office_approve_date'] = date('Y-m-d H:i:s');                        
                        $data['office_approve_comment'] = $ApproveComment;
                        $data['office_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                    }
                }

                // print_r($data );
                // exit;
                $result = SpermService::updateDataApprove($id, $data);

                $this->data_result['DATA']['result'] = $result;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }   
        }

        private function do_post_request($url, $method, $data = [], $optional_headers = null)
        {
              $params = array('http' => array(
                          'method' => $method,
                          'content' => http_build_query($data)
                        ));
              if ($optional_headers !== null) {
                $params['http']['header'] = $optional_headers;
              }
              $ctx = stream_context_create($params);
              $fp = @fopen($url, 'rb', false, $ctx);
               if (!$fp) {
                print_r($fp);
                    return array("STATUS"=>'ERROR',"MSG"=>"ERROR :: Problem with $url");
                //throw new Exception("Problem with $url, $php_errormsg");
              }
              $response = @stream_get_contents($fp);
              if ($response === false) {
                print_r($response);
                    return array("STATUS"=>'ERROR',"MSG"=>"ERROR :: Problem reading data from $url");
    //            throw new Exception("Problem reading data from $url");
              }

              return $response;
              
        }

    }