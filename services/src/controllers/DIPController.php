<?php

    namespace App\Controller;
    
    use App\Service\DIPService;

    class DIPController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getList($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $years = $params['obj']['condition']['years'];
                $_List = DIPService::getList($years);

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
                
                $_Data = DIPService::getData($id);

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
                // print_r($_Data);exit;
                // // Update to none role
                $id = DIPService::updateData($_Data);

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
                
                $result = DIPService::removeData($id);

                $this->data_result['DATA']['result'] = $result;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }