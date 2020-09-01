<?php
namespace App\Controller;

use App\Entity\Restaurants;
use App\Entity\RestaurantTables;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\RestaurantsType;
use App\Form\RestaurantTableType;
use App\Service\Table;
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
            $gathering['tables'] =  $tables;
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
        if($form->isSubmitted() && $form->isValid()) {
            $restaurant = $form->getData();
            //handling photo upload start
            $photoFile = $form->get('photoFile')->getData();
            if($photoFile) {
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
        $restaurant = $this->getDoctrine()->getRepository('App:Restaurants')->findOneBy(['id' => $id]);
        $resId = $restaurant->getId();
        $tables = $this->getDoctrine()->getRepository('App:RestaurantTables')->findBy(['restaurantId' => $restaurant->getId()]);
        $activeTables = $this->getDoctrine()->getRepository('App:RestaurantTables')->findActiveById($restaurant->getId());
        $form = $this->createForm(RestaurantsType::class, $restaurant);
        
        $form->handleRequest($request);
        $file = $restaurant->getPhoto();
        if($form->isSubmitted() && $form->isValid()) {
            $restaurant->setTitle($form->get('title')->getData());
            //handling photo upload start
            $photoFile = $form->get('photoFile')->getData();
            if($photoFile instanceof UploadedFile) {
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
            if($form->get('maxTable')->getData() >= $activeTables){
                $restaurant->setMaxTable($form->get('maxTable')->getData());
                $restaurant->setStatus($form->get('status')->getData());
                $em = $this->getDoctrine()->getManager();
                $em->persist($restaurant);
                $em->flush();
        
                return $this->redirectToRoute('restaurant_list');
            }else{
                echo('You cannot reduce max table count lower than the tables are set.');
            }
            
        }
        return $this->render('restaurantEdit.html.twig', [
            'tables' => $tables,
            'resId' => $resId,
            'form' => $form->createView(),
        ]);
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
       if($this->getParameter('photos_directory').'/'.$restaurant->getPhoto()){
           try{
            unlink($this->getParameter('photos_directory').'/'.$restaurant->getPhoto());
           } catch(\Exception $e){
            error_log($e->getMessage());
           }
       } 
       $em->remove($restaurant);
       $em->flush();
       return $this->redirectToRoute('restaurant_list');
    }

    /**
     * @Route("/table/create/{id}", name="table_create")
     */
    public function tableCreate(Request $request, $id)
    {   
        // picking restaurant id and assigning to table - restaurantId
        $resId = $id;
        $activeTables = $this->getDoctrine()->getRepository('App:RestaurantTables')->findActiveById($resId);
        $maxTables = $this->getDoctrine()->getRepository('App:Restaurants')->findOneBy(['id' => $resId]);
        $maxTables = $maxTables->getMaxTable();
        $table = new RestaurantTables();
        $form = $this->createForm(RestaurantTableType::class, $table);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $table = $form->getData();
            $table->setRestaurantId($resId);
            $em = $this->getDoctrine()->getManager();
            if($maxTables > $activeTables or $form->get('status')->getData() == false){
                $em->persist($table);
                $em->flush();
        
                return $this->redirect($this->generateUrl('restaurant_edit', array('id' => $resId)));

            }else{
                $this->addFlash('success', 'Warning: status will be set to "Inactive" - Max active talbe count reached');
                $table->setStatus(0);
                $em->persist($table);
                $em->flush();
            }
        }
        return $this->render('tableCreate.html.twig', [
            'resId' => $resId,
            'form' => $form->createView(),
        ]);
    }

     /**
     * @Route("/table/delete/{id}", name="table_delete")
     */
    public function tableDelete($id)
    {   
        $em = $this->getDoctrine()->getManager();
        $table = $this->getDoctrine()->getRepository('App:RestaurantTables')->findOneBy(['id' => $id]);
        $resId = $table->getRestaurantId();
        $em->remove($table);
        $em->flush();

        return $this->redirect($this->generateUrl('restaurant_edit', array('id' => $resId)));
    }

     /**
     * @Route("/table/edit/{id}", name="table_edit")
     */
    public function tableEdit(Request $request, $id)
    {   
        $table = $this->getDoctrine()->getRepository('App:RestaurantTables')->findOneBy(['id' => $id]);
        $resId = $table->getRestaurantId();
        $activeTables = $this->getDoctrine()->getRepository('App:RestaurantTables')->findActiveById($resId);
        $maxTables = $this->getDoctrine()->getRepository('App:Restaurants')->findOneBy(['id' => $resId]);
        $maxTables = $maxTables->getMaxTable();
        $form = $this->createForm(RestaurantTableType::class, $table);
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $table->setCapacity($form->get('capacity')->getData());
            $table->setNumber($form->get('number')->getData());
            $table->setStatus($form->get('status')->getData());
            $em = $this->getDoctrine()->getManager();
            if($maxTables > $activeTables or $form->get('status')->getData() == false){
                $em->persist($table);
                $em->flush();
        
                return $this->redirect($this->generateUrl('restaurant_edit', array('id' => $resId)));

            }else{
                $this->addFlash('success', 'Warning: status will be set to "Inactive" - Max active talbe count reached');
                $table->setStatus(0);
                $em->persist($table);
                $em->flush();
            }
        }
        return $this->render('tableEdit.html.twig', [
            'resId' => $resId,
            'form' => $form->createView(),
        ]);
    }
}