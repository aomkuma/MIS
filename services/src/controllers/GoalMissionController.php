<?php

    namespace App\Controller;
    
    use App\Service\GoalMissionService;

    class GoalMissionController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getList($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $condition = $params['obj']['condition'];
                $_List = GoalMissionService::getList($condition);

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
                
                $_Data = GoalMissionService::getData($id);

                $this->data_result['DATA']['Data'] = $_Data;
                
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
                        $data['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                    }else if($OrgType == 'division'){
                        $data['division_approve_date'] = date('Y-m-d H:i:s');
                        $data['division_approve_comment'] = $ApproveComment;
                        $data['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                    }else if($OrgType == 'office'){
                        $data['office_approve_date'] = date('Y-m-d H:i:s');
                        $data['office_approve_comment'] = $ApproveComment;
                        
                    }
                }else if($ApproveStatus == 'reject'){

                    if($OrgType == 'dep'){
                        $data['dep_approve_date'] = NULL;                  
                        $data['dep_approve_comment'] = $ApproveComment;
                    }else if($OrgType == 'division'){
                        $data['dep_approve_date'] = NULL;                  
                        $data['dep_approve_comment'] = NULL;
                        
                        $data['division_approve_id'] = NULL;
                        $data['division_approve_date'] = NULL;
                        $data['division_approve_comment'] = $ApproveComment;
                    }else if($OrgType == 'office'){

                        $data['dep_approve_date'] = NULL;                  
                        $data['dep_approve_comment'] = NULL;
                        
                        $data['division_approve_id'] = NULL;
                        $data['division_approve_date'] = NULL;
                        $data['division_approve_comment'] = NULL;

                        $data['office_approve_id'] = NULL;    
                        $data['office_approve_date'] = NULL;                        
                        $data['office_approve_comment'] = $ApproveComment;
                    }
                }

                // print_r($data );
                // exit;
                $result = GoalMissionService::updateDataApprove($id, $data);

                GoalMissionService::addHistory($arr_history);

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

        public function updateDataEditable($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $id = $params['obj']['id'];
                $editable = $params['obj']['editable'];
                $user_session = $params['user_session'];
                // print_r($user_session);
                $unlock_name = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                // exit;

                $change_date = date('Y-m-d H:i:s');
                $arr_history = ['goal_mission_id' => $id, 'unlock_name' => $unlock_name, 'change_date' => $change_date];

                $result = GoalMissionService::updateDataEditable($id, $editable);

                GoalMissionService::addHistory($arr_history);

                $this->data_result['DATA']['result'] = $result;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }   
        }

        public function updateData($request, $response, $args){
            // $URL = '172.23.10.224';
            $URL = '127.0.0.1';
            try{
                // error_reporting(E_ERROR);
                // error_reporting(E_ALL);
                // ini_set('display_errors','On');
                $params = $request->getParsedBody();
                $_Data = $params['obj']['Data'];

                foreach ($_Data as $key => $value) {
                    if($value == 'null'){
                        $_Data[$key] = NULL;
                    }
                }

                $_AvgData = $params['obj']['AvgList'];
                $user_session = $params['user_session'];
                $OrgID = $user_session['OrgID'];
                $edit_name = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                unset($_Data['goal_mission_avg']);
                unset($_Data['goal_mission_history']);
                
                $_Data['update_by'] = $user_session['UserID'];
                $_Data['editable'] = 'N';

                $HeaderData = $this->do_post_request('http://' . $URL . '/dportal/dpo/public/mis/get/org/header/', "POST", ['OrgID' => $OrgID, 'Type' => 'OWNER']);
                $HeaderData = json_decode(trim($HeaderData), TRUE);
                // print_r($HeaderData);exit;
                if($HeaderData['data']['DATA']['Header']['OrgType'] == 'DEPARTMENT'){
                    $_Data['dep_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                }else if($HeaderData['data']['DATA']['Header']['OrgType'] == 'DIVISION'){
                    $_Data['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                }else if($HeaderData['data']['DATA']['Header']['OrgType'] == 'OFFICE'){
                    $_Data['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                }

                $id = GoalMissionService::updateData($_Data);

                // check what update in avg data then save to history
                $AvgList = GoalMissionService::getAvgList($id);
                $cnt = 0;
                foreach ($AvgList as $key => $value) {
                    if($value['amount'] != $_AvgData[$cnt]['amount']){
                        // Save to history
                        $HistoryData = $_AvgData[$cnt];
                        unset($HistoryData['id']);
                        $HistoryData['goal_mission_id'] = $id;
                        $HistoryData['edit_name'] = $edit_name;
                        GoalMissionService::addHistory($HistoryData);
                    }
                    $cnt++;
                }

                // update avg data
                foreach ($_AvgData as $key => $value) {
                    $value['goal_mission_id'] = $id;
                    GoalMissionService::updateAvg($value);
                }

                $this->data_result['DATA']['id'] = $id;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }