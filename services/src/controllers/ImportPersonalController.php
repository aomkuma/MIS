<?php

namespace App\Controller;

use App\Service\AttachFileService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ImportPersonalController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function import($request, $response) {
        $_WEB_FILE_PATH = 'files/files';
        try {
            $params = $request->getParsedBody();
            $Data = $params['obj']['Data'];

            $files = $request->getUploadedFiles();

            if ($files != null) {
                $name = $files['obj']['AttachFile']->getClientFilename();
                $ext = pathinfo($name, 4);
                $FileName = date('YmdHis') . '_' . rand(100000, 999999) . '.' . $ext;
                $FilePath = $_WEB_FILE_PATH . '/import/' . $FileName;
                $AttachFile = ['file_name' => $name
                    , 'file_path' => $FilePath
                ];
                AttachFileService::updateAttachFiles($AttachFile, $Data);
                $files['obj']['AttachFile']->moveTo('../../' . $FilePath);
                $this->readExcelFile('../../' .$FilePath, $name);
            }
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    private function readExcelFile($file, $name) {

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

//        $inputFileType = PHPExcel\IOFactory::identify($name);
//       $objReader = PHPExcel\IOFactory::createReader('excel2007');
//        $objPHPExcel = $objReader->load($file);

        print_r($sheetData);
        die();
//        $field_array = ['item', 'staff', 'operating', 'investing', 'subsidy', 'other', 'subtotal'];
//        $cnt_row = 1;
//        $ItemList = [];
//        foreach ($sheetData as $key => $value) {
//
//            if ($cnt_row >= 4) {
//
//                $cnt_col = 0;
//                $cnt_field = 0;
//                $Item = [];
//                $Item['budget_id'] = $budget_id;
//
//                foreach ($value as $k => $v) {
//                    if ($cnt_col >= 1 && $cnt_col <= 7) {
//
//                        $Item[$field_array[$cnt_field]] = $v;
//                        $cnt_field++;
//                    }
//                    $cnt_col++;
//                }
//
//                array_push($ItemList, $Item);
//            }
//
//            $cnt_row++;
//        }
//
//        return $ItemList;
    }

    public function getMainList($request, $response, $args) {
        try {

            $list = AttachFileService::getList();
//            print_r($list);
//            print_r('in');
            return $this->returnResponse(200, $list, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $list, $e, $response);
        }
    }

}
