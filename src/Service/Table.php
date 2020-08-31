<?php

namespace App\Service;

use App\Entity\RestaurantTables as TableEntity;
use App\Entity\Restaurants as RestaurantEntity;
use Symfony\Component\HttpFoundation\Response;

class Table
{
    public function validatingMaxTables(TableEntity $table, RestaurantEntity $restaurant)
    {
        // getting order information
        $id = $table->getId();
        $tableMax = $restaurant->getMaxTable();
        
        dump($id, $tableMax);die;
        
        // $client = new Client();
        // try{
  // check for shipping method
        //     if($shipping == OrderEntity::UPS){
        //         $client->request('POST', 'http://upsfake.com/register', [
        //             'form_params' => [
        //                 'order_id' => $id,
        //                 'country' => $country,
        //                 'street' => $street,
        //                 'city' => $city,
        //                 'post_code' => $postCode
        //             ]
        //         ]);
        //         return 'ups 200';
        //     }
        //     elseif($shipping == OrderEntity::OMNIVA){
        //         // getter
        //         $pickUp = $client->request('GET', 'http://omnivafake.com/pickup/find', [
        //             'country' => $country,
        //             'post_code' => $postCode
        //             ]);
        //         // setter
        //         $client->request('POST', 'http://omnivafake.com/register', [
        //             'form_params' => [
        //                 'pick_point_id' => $pickUp,
        //                 'order_id' => $id
        //             ]
        //         ]);
        //         return 'omniva 200';
        //     }
        //     elseif($shipping == OrderEntity::DHL){
        //         $client->request('POST', 'http://dhlfake.com/register', [
        //             'form_params' => [
        //                 'order_id' => $id,
        //                 'country' => $country,
        //                 'address' => $street,
        //                 'town' => $city,
        //                 'zip_code' => $postCode
        //             ]
        //         ]);
        //         return 'dhl 200';            
        //     }else{
        //         throw new \Exception('Carrier not found');
                
        //         return false;
        //     }
        // } catch(\Exception $e){
        //     $errorMessage = $e->getMessage();
        //     $response = New Response();
        //     $response -> setContent($errorMessage);
        //     $response -> setStatusCode(Response::HTTP_BAD_REQUEST);

        //     return $response;
        // }
    }
}