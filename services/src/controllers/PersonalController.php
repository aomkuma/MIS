<?php

namespace App\Controller;

use App\Service\PersonalService;

class PersonalController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public static function getMonthName($month) {
        switch ($month) {
            case 1 : $monthTxt = 'มกราคม';
                break;
            case 2 : $monthTxt = 'กุมภาพันธ์';
                break;
            case 3 : $monthTxt = 'มีนาคม';
                break;
            case 4 : $monthTxt = 'เมษายน';
                break;
            case 5 : $monthTxt = 'พฤษภาคม';
                break;
            case 6 : $monthTxt = 'มิถุนายน';
                break;
            case 7 : $monthTxt = 'กรกฎาคม';
                break;
            case 8 : $monthTxt = 'สิงหาคม';
                break;
            case 9 : $monthTxt = 'กันยายน';
                break;
            case 10 : $monthTxt = 'ตุลาคม';
                break;
            case 11 : $monthTxt = 'พฤษจิกายน';
                break;
            case 12 : $monthTxt = 'ธันวาคม';
                break;
        }
        return $monthTxt;
    }

    public function getMainList($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $condition = $params['obj']['condition'];


            if ($condition['DisplayType'] == 'monthly') {
                $Result = $this->getMonthDataList($condition);
            } else if ($condition['DisplayType'] == 'quarter') {
                $Result = $this->getQuarterDataList($condition);
            } else if ($condition['DisplayType'] == 'annually') {
                $Result = $this->getAnnuallyDataList($condition);
            }

            $DataList = $Result['DataList'];
            $Summary = $Result['Summary'];
            // print_r($DataList);
            // exit;

            $this->data_result['DATA']['DataList'] = $DataList;
            $this->data_result['DATA']['Summary'] = $Summary;
//            print_r($this->data_result);
//            die();
            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getMonthDataList($condition) {
        $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28'; // . VeterinaryController::getLastDayOfMonth($ymTo);
        //exit;
        $fromTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);

        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);

        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        // exit;
        // if($ymFrom != $ymTo){
        //     $diffMonth += 1;
        // }
        $curMonth = $condition['MonthFrom'];
        $DataList = [];
        $DataSummary = [];
//        $DataSummary['SummaryCurrentsum'] = 0;
//        $DataSummary['SummaryBeforesum'] = 0;
//        $DataSummary['SummaryCurrentdirector'] = 0;
//        $DataSummary['SummaryBeforedirector'] = 0;
//        $DataSummary['SummaryCurrent'] = 0;
//        $DataSummary['SummaryBefore'] = 0;
//        $DataSummary['SummaryCurrentPercentage'] = 0;
//        $DataSummary['SummaryBeforePercentage'] = 0;
        $positiontype = PersonalService::getPositiontype();

//        die();
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }
        for ($i = 0; $i < $diffMonth; $i++) {

            // Prepare condition
            $curYear = $condition['YearTo'];
            $beforeYear = $condition['YearTo'] - 1;
            // Loop User Regions
            foreach ($positiontype as $key => $value) {

                //     $region_id = $value['RegionID'];
                $monthName = PersonalController::getMonthName($curMonth);

                $data = [];
                $data['Position'] = $value['positiontype'];
                $data['Month'] = $monthName;
                $data['Quarter'] = ($i + 1);
                $data['Year'] = ($curYear);
                $data['SummaryCurrentsum'] = 0;
                $data['SummaryBeforesum'] = 0;
                $data['SummaryCurrentdirector'] = 0;
                $data['SummaryBeforedirector'] = 0;
                $data['SummaryCurrent'] = 0;
                $data['SummaryBefore'] = 0;
                $data['SummaryPercentage'] = 0;
                $data['SummarysumPercentage'] = 0;
                // get cooperative type

                $data['CurrentEmployee'] = PersonalService::getMainList($curYear, $curMonth, $value['positiontype']);
//                $data['CurrentEmployee'] = floatval($Currentdata);
                $data['BeforeEmployee'] = PersonalService::getMainList($beforeYear, $curMonth, $value['positiontype']);
                //     $data['BeforeEmployee'] = floatval($Beforedata);
//                $diffData = $data['CurrentCowData'] - $data['BeforeCowData'];
//                $data['DiffCowData'] = $diffCowData;
//
                foreach ($data['CurrentEmployee'] as $key => $current) {
                    $data['CurrentEmployee'][$key]['percent'] = 0;
                    $data['SummaryCurrentsum'] += $current['summary'];
                    $data['SummaryCurrentdirector'] = $current['director'];
                }

                if ($data['BeforeEmployee'] != '' && !is_null($data['BeforeEmployee'])) {

                    foreach ($data['CurrentEmployee'] as $keyitem => $item) {
//                          

                        $sumcurrent = $item['summary'] + $item['director'];
                        $sumbefore = $data['BeforeEmployee'][$keyitem]['summary'] + $data['BeforeEmployee'][$keyitem]['director'];


                        $data['SummaryCurrent'] += $sumcurrent;
//                        
                        $data['SummaryBeforesum'] += $data['BeforeEmployee'][$keyitem]['summary'];
                        $data['SummaryBeforedirector'] += $data['BeforeEmployee'][$keyitem]['director'];
                        $data['SummaryBefore'] += $sumbefore;
                        if ($sumbefore != 0) {
                            $data['CurrentEmployee'][$keyitem]['percent'] = (($sumcurrent - $sumbefore) * 100) / $sumbefore;
                        }

                        $data['SummaryPercentage'] += ($sumcurrent + $sumbefore);
                    }
                    if ($data['SummaryBefore'] != 0) {
                        $data['SummarysumPercentage'] = (($data['SummaryCurrent'] - $data['SummaryBefore']) * 100) / $data['SummaryBefore'];
                    }
                }

                $data['Status'] = '';
                $data['Description'] = [
                    'months' => $curMonth
                    , 'years' => $curYear
                ];


                array_push($DataList, $data);

                #### End of cooperative 
            }

            //tb2
            $DataSummary['current'] = PersonalService::getMainListsheet3($curYear, $curMonth);
            $DataSummary['before'] = PersonalService::getMainListsheet3($beforeYear, $curMonth);
            foreach ($DataSummary['current'] as $key => $t) {
                $DataSummary['current'][$key]['Monthname'] = $monthName;
                if($DataSummary['before'][$key]['summary']!=0&&$DataSummary['before'][$key]['summary']!=''){
                         $DataSummary['current'][$key]['percent']=(($t['summary']- $DataSummary['before'][$key]['summary'])*100)/$DataSummary['before'][$key]['summary'];
           
                }else{
                    $DataSummary['current'][$key]['percent']=0;
                }
            }

            $curMonth++;
        }

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

}
