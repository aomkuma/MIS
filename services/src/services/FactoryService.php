<?php
    
    namespace App\Service;
    
    use App\Model\Factory;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class FactoryService {

    	public static function getList(){
            return Factory::all();      
        }

        public static function getData($id){
            return Factory::where('id', $id)->get();      
        }

    }