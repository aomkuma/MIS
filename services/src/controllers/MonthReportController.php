<?php

namespace App\Controller;

use App\Controller\VeterinaryController;
use PHPExcel;

class MonthReportController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function exportmonthreportExcel($request, $response) {
        try {
            $obj = $request->getParsedBody();
         
            $condition['YearFrom'] = $obj['obj']['condition']['Year'];
            $condition['YearTo'] = $obj['obj']['condition']['Year'];
            $condition['MonthFrom'] = $obj['obj']['condition']['Month'];
            $condition['MonthTo'] = $obj['obj']['condition']['Month'];
            $region=$obj['obj']['region'];
            
            $data=VeterinaryController::getMonthDataList($condition, $region);
               print_r($data);
            die();
//            $condition = $obj['obj']['condition'];
//            $cooperative = $obj['obj']['CooperativeList'];
//            $data = $obj['obj']['DetailList'];
//            $description = $obj['obj']['data_description'];
            //$summary = $obj['summary'];
//            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
//
//            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
//
//            $objPHPExcel = new PHPExcel();
//
//            switch ($condition['DisplayType']) {
//                case 'annually' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม ปี ' . ($condition['YearFrom'] + 543);
//                    $objPHPExcel = $this->generateVeterinaryExcel($objPHPExcel, $condition, $data, $cooperative, $header);
//                    break;
//                case 'monthly' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม เดือน ' . $this->getMonthName($description['months']) . ' ปี ' . ($description['years'] + 543);
//                    $objPHPExcel = $this->generateVeterinaryExcel($objPHPExcel, $condition, $data, $cooperative, $header);
//                    break;
//                case 'quarter' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม ไตรมาสที่ ' . $description['quarter'] . ' ปี ' . ($condition['YearFrom'] + 543);
//                    $objPHPExcel = $this->generateVeterinaryExcel($objPHPExcel, $condition, $data, $cooperative, $header);
//                    break;
//
//                default : $result = null;
//            }
//
//            $filename = 'Monthly-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
//            $filepath = '../../files/files/download/' . $filename;
//
//            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//
//            $objWriter->setPreCalculateFormulas();
//
//
//            $objWriter->save($filepath);
//
//            $this->data_result['DATA'] = 'files/files/download/' . $filename;

            return $this->returnResponse(200, $this->data_result, $response);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

}
