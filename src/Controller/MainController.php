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

        if($restaurants !== null){
            return $this->render('listRestaurants.html.twig', ['restaurants' => $restaurants]);
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
}