<?php
namespace App\Controller;

use App\Entity\Restaurants;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\RestaurantsType;
use App\Service\Table;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
            //handling photo upload start
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                    // moving file to directory
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                $restaurant->setPhoto($newFilename);
            }else{
                $restaurant->setPhoto('n/a');
            }
            //handling photo upload end

            $em = $this->getDoctrine()->getManager();
            $em->persist($restaurant);
            $em->flush();
    
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
        $file = $restaurant->getPhoto();
        if ($form->isSubmitted() && $form->isValid()) {
            $restaurant->setTitle($form->get('title')->getData());
            //handling photo upload start
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile instanceof UploadedFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                    // moving file to directory
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                $restaurant->setPhoto($newFilename);
            }else{
                $restaurant->setPhoto($file);
            }
            //handling photo upload end
            
            $restaurant->setMaxTable($form->get('maxTable')->getData());
            $restaurant->setStatus($form->get('status')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($restaurant);
            $em->flush();
    
            return $this->redirectToRoute('restaurant_list');
        }
        return $this->render('restaurantEdit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/testavimas", name="test")
     */
    public function testavimas(Table $table)
    {
        $tables = $this->getDoctrine()->getRepository('App:RestaurantTables')->findById(2);
        $restaurant = $this->getDoctrine()->getRepository('App:Restaurants')->findById(5);

        $table->validatingMaxTables($tables[0], $restaurant[0]);
    }

     /**
     * @Route("/restaurant/delete/{id}", name="restaurant_delete")
     */
    public function restaurantDelete($id)
    {   
       $em = $this->getDoctrine()->getManager();
       $restaurant = $this->getDoctrine()->getRepository('App:Restaurants')->findOneBy(['id' => $id]);
       $tables = $this->getDoctrine()->getRepository('App:RestaurantTables')->findBy(['restaurantId' => $id]);
       foreach($tables as $table){
          $em->remove($table);
       }
       unlink($this->getParameter('photos_directory').'/'.$restaurant->getPhoto());
       $em->remove($restaurant);
       $em->flush();
       return $this->redirectToRoute('restaurant_list');
    }
}