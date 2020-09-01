<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class apiController extends AbstractController
{
    /**
     * @Route("/api")
     */
    public function showTables(Request $request)
    {

        $token = $this->getDoctrine()->getRepository('App:ApiToken')->findOneBy(['id' => 1]);
        $token = $token->getToken();

        if($token !== $request->headers->get('X-Auth-Token')) {
        return new JsonResponse(["Unauthorized"]);
        }else{

            $restaurants = $this->getDoctrine()->getRepository('App:Restaurants')->findAll();
            $result = []; 
            $gathering = [];
            $gathering2 = [];
            $tableArray = [];
            foreach($restaurants as $restaurant){
                $tables = $this->getDoctrine()->getRepository('App:RestaurantTables')->findAllById($restaurant->getId());
                $tablesActive = $this->getDoctrine()->getRepository('App:RestaurantTables')->findActiveById($restaurant->getId());
                foreach($tables as $table){
                    // gathering table info start
                    $tableId = $table->getId();
                    $tableCap = $table->getCapacity();
                    $tableNumber = $table->getNumber();
                    $tableStatus = $table->getStatus();
                    $tableAsso = $table->getRestaurantId();
                    // gathering information end
                    if($tableAsso == $restaurant->getId()){

                        $tableArray['id'] = $tableId;
                        $tableArray['capacity'] = $tableCap;
                        $tableArray['table_number'] = $tableNumber;
                        $tableArray['table_status'] = $tableStatus;

                        array_push($gathering2, $tableArray);
                    }
                    
                }
                $gathering['active_tables'] = $tablesActive;
                $gathering['restaurant_title'] = $restaurant->getTitle();
                $gathering['photo'] = $restaurant->getPhoto();
                $gathering['restaurant_id'] = $restaurant->getId();
                $gathering['resetaurant_status'] = $restaurant->getStatus();
               
                $gathering['tables'] = $gathering2;

                array_push($result, $gathering); 
                $gathering2 = [];  
                    
            }   
            if($result !== null){
                return new JsonResponse ($result);
            }
        }
    }
}