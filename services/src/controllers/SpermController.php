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
            try{
                $params = $request->getParsedBody();
                $_Data = $params['obj']['Data'];
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

    }