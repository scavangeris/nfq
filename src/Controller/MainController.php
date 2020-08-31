<?php
namespace App\Controller;

use App\Entity\Restaurants;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\RestaurantsType;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/", name="restaurant_list")
     */
    public function restaurantList()
    {
        $restaurants = $this->getDoctrine()->getRepository('App:Restaurants')->findAll();
        $result = [];
        $gathering = [];
        foreach($restaurants as $restaurant){
            $tables = $this->getDoctrine()->getRepository('App:RestaurantTables')->findActiveById($restaurant->getId());
            // dump($tables);
            $gathering['tables'] =  $tables[1];
            $gathering['title'] = $restaurant->getTitle();
            $gathering['photo'] = $restaurant->getPhoto();
            $gathering['id'] = $restaurant->getId();
            $gathering['status'] = $restaurant->getStatus();
            array_push($result, $gathering);
            
        }

        if($result !== null){
            return $this->render('listRestaurants.html.twig',
             [
                 'restaurants' => $result
                 ]);
        }
    }

    /**
     * @Route("/restaurant/create", name="restaurant_create")
     */
    public function restaurantCreate(Request $request)
    {
        $restaurant = new Restaurants();
        $form = $this->createForm(RestaurantsType::class, $restaurant);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $restaurant = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($restaurant);
            $entityManager->flush();
    
            return $this->redirectToRoute('restaurant_list');
        }
        return $this->render('restaurantCreate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/restaurant/edit/{id}", name="restaurant_edit")
     */
    public function restaurantEdit(Request $request, $id)
    {   
        $restaurant = $this->getDoctrine()->getRepository('App:Restaurants')->findOneById($id);

        $form = $this->createForm(RestaurantsType::class, $restaurant);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $restaurant->setTitle($form->get('title')->getData());
            $restaurant->setPhoto($form->get('photo')->getData());
            $restaurant->setMaxTable($form->get('maxTable')->getData());
            $restaurant->setStatus($form->get('status')->getData());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($restaurant);
            $entityManager->flush();
    
            return $this->redirectToRoute('restaurant_list');
        }
        return $this->render('restaurantEdit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}