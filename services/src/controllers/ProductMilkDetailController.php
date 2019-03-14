<?php

namespace App\Controller;

use App\Service\ProductMilkDetailService;

class ProductMilkDetailController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function getList($request, $response, $args) {
        try {
            $params = $request->getParsedBody();
//                $actives = $params['obj']['actives'];
//                $menu_type = $params['obj']['menu_type'];
//                $condition = $params['obj']['condition'];

            $_List = ProductMilkDetailService::getList();
//                print_r($_List);
//                die();
            $this->data_result['DATA']['List'] = $_List;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getListByParent($request, $response, $args) {
        try {
            $params = $request->getParsedBody();
            $sub_product_milk_id = $params['obj']['sub_product_milk_id'];
//                $menu_type = $params['obj']['menu_type'];
//                $condition = $params['obj']['condition'];

            $_List = ProductMilkDetailService::getListByParent($sub_product_milk_id);
//                print_r($_List);
//                die();
            $this->data_result['DATA']['List'] = $_List;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getListByParent2($request, $response, $args) {
        try {
            $params = $request->getParsedBody();
            $sub_product_milk_id = $params['obj']['id'];
//                $menu_type = $params['obj']['menu_type'];
//                $condition = $params['obj']['condition'];

            $_List = ProductMilkDetailService::getListByParent2($sub_product_milk_id);
//                print_r($_List);
//                die();
            $this->data_result['DATA']['List'] = $_List;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getData($request, $response, $args) {
        try {
            $params = $request->getParsedBody();
            $id = $params['obj']['id'];

            $_Data = ProductMilkDetailService::getData($id);


            $this->data_result['DATA']['Data'] = $_Data;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function updateData($request, $response, $args) {

        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();
            $_Data = $params['obj']['Subdata'];
            //  print_r($_Data);die();
            // // Update to none role
            $result = ProductMilkDetailService::checkDuplicate($_Data['id'], $_Data['name'], $_Data['sub_product_milk_id']);

            if (empty($result)) {
                $id = ProductMilkDetailService::updateData($_Data);
                $this->data_result['DATA']['id'] = $id;
            } else {
                // print_r($result);exit;
                $this->data_result['STATUS'] = 'ERROR';
                $this->data_result['DATA'] = 'บันทึกข้อมูลซ้ำ';
            }



            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

}
