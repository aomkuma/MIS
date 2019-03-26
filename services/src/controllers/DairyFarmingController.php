<?php

    namespace App\Controller;
    
    use App\Service\DairyFarmingService;

    class DairyFarmingController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getList($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $actives = $params['obj']['actives'];
                $_ParentList = DairyFarmingService::getList($actives);

                $_List = [];
                
                foreach ($_ParentList as $key => $value) {
                    
                    $_List[] = $value;
                    $_ChildList = DairyFarmingService::getChildList($value['id'], $actives);
                    
                    foreach ($_ChildList as $_key => $_value) {
                        $_value['dairy_farming_name'] = $value['dairy_farming_name'];
                        array_push($_List, $_value);
                    }
                }

                $this->data_result['DATA']['List'] = $_List;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getListForVeterinary($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $type = $params['obj']['type'];
                $parent_id = $params['obj']['parent_id'];
                $data_arr = $params['obj']['data_arr'];
                
                $_List = DairyFarmingService::getListForVeterinary($type, $parent_id, $data_arr);

                $this->data_result['DATA']['List'] = $_List;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getParentList($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $_List = DairyFarmingService::getList('Y');

                $this->data_result['DATA']['List'] = $_List;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $id = $params['obj']['id'];
                
                $_Data = DairyFarmingService::getData($id);

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
                $_Data = $params['obj']['Data'];
                
                $dairy_farming_type = $_Data['dairy_farming_type'];
                $id = DairyFarmingService::updateData($_Data);
                // Update child farm type 
                DairyFarmingService::updateDairyFarmTypeData($id, $dairy_farming_type);
                
                $this->data_result['DATA']['id'] = $id;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }