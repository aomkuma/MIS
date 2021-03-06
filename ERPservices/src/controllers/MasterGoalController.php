<?php

    namespace App\Controller;
    
    use App\Service\MasterGoalService;

    class MasterGoalController extends Controller {
        
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
                $menu_type = $params['obj']['menu_type'];
                $condition = $params['obj']['condition'];

                $_List = MasterGoalService::getList($actives, $menu_type, $condition);

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
                
                $_Data = MasterGoalService::getData($id);

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
                $result = MasterGoalService::checkDuplicate($_Data['id'], $_Data['menu_type'], trim($_Data['goal_name']));

                if(empty($result)){
                    $id = MasterGoalService::updateData($_Data);
                    $this->data_result['DATA']['id'] = $id;
                }else{
                    // print_r($result);exit;
                    $this->data_result['STATUS'] = 'ERROR';
                    $this->data_result['DATA'] = 'บันทึกข้อมูลซ้ำ';
                }

                
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }