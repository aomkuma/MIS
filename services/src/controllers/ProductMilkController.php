<?php

    namespace App\Controller;
    
    use App\Service\ProductMilkService;
    use App\Service\SubProductMilkService;
    use App\Service\ProductMilkDetailService;
    use App\Service\MasterGoalService;

    class ProductMilkController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getList($request, $response, $args){
            try{
                $params = $request->getParsedBody();
//                $actives = $params['obj']['actives'];
//                $menu_type = $params['obj']['menu_type'];
//                $condition = $params['obj']['condition'];
                $facid=$params['obj']['facid'];
//print_r($params);
//        die();
                $_List = ProductMilkService::getList('','','',$facid);

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
                
                $_Data = ProductMilkService::getData($id);

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
                $result = ProductMilkService::checkDuplicate($_Data['id'], $_Data['name'],$_Data['factory_id']);

                if(empty($result)){

                    // Get old data
                    $OldData = ProductMilkService::getData($_Data['id']);

                    $id = ProductMilkService::updateData($_Data);
                    $this->data_result['DATA']['id'] = $id;

                    $SubProductMilk = SubProductMilkService::getListByProductMilk($_Data['id']);

                    foreach ($SubProductMilk as $key => $value) {
                        $ProductMilkDetail = ProductMilkDetailService::getListByParent($value['id']);

                        foreach ($ProductMilkDetail as $key1 => $value1) {
                            
                            // find master goal by name
                            $old_goal_name = $OldData['name'] . ' - ' . $value['name'] . ' - ' . $value1['name'];
                            $MasterGoal = MasterGoalService::getDataByName($old_goal_name);
                            // Add master goal
                            if(!empty($MasterGoal)){
                                $MasterGoal['goal_name'] = $_Data['name'] . ' - ' . $value['name'] . ' - ' . $value1['name'];
                                MasterGoalService::updateData($MasterGoal);
                            }

                        }
                    }

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