<?php

namespace App\Controller;
use App\Entity\Salle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\SalleType;
use App\Form\SalleditType;

use App\Repository\SalleRepository;

final class SalleController extends AbstractController
{
    #[Route('/salle', name: 'app_salle')]
    public function index(): Response
    {
        return $this->render('salle/index.html.twig', [
            'controller_name' => 'SalleController',
        ]);
    }


    #[Route('/salle/create', name: 'createsalle')]

    public function createSalle(Request $request,EntityManagerInterface $em,SalleRepository $repo)
{
  
    $salle= new Salle();

$form =$this->createForm(SalleType::class, $salle);

$form->handleRequest($request);

 if($form->isSubmitted()&& $form->isValid())
 {
    $imageFile = $form->get('image_salle')->getData();
    if ($imageFile = $form->get('image_salle')->getData() != null){
        $imageFile = $form->get('image_salle')->getData();
        $filename= uniqid() . '-' . $imageFile->getClientOriginalName();
        $imageFile->move(
        $this->getParameter('upload_directory'),
        $filename
        );

    $salle-> setImageSalle($filename);
    }
    else {
 return $this->render('salle/create.html.twig',[
    'form' => $form->createView(),
    ]);
    } 
    

 $em->persist($salle);
 $em->flush();

 return $this->redirectToRoute('showallsalles');

}
 else 
 {

 return $this->render('salle/create.html.twig',[
'form' => $form->createView(),
]);
 }
}





#[Route('/salle/showallback', name: 'showallsalles')]
public function showallreclamationback(SalleRepository $repo): Response
{

    
    $salles = $repo->findall();
return $this->render('salle/showallsalles.html.twig',[
    'salles' =>$salles,

    ]);

}


#[Route('/salle/{id}/removeback',name:'removesalleback')]
public function deletesalleback(SalleRepository $repo,EntityManagerInterface $em,int $id){
 $salle =$repo->find($id);
 
 if($salle){
    $em->remove($salle);
    $em->flush();
    return $this->redirectToRoute('showallsalles');
 }
}


#[Route('/salle/edit/{id}', name: 'app_salle_edit')]
    public function edit(Request $request,EntityManagerInterface $em ,Salle $salle): Response
    {
        $form = $this->createForm(SalleditType::class, $salle);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image_salle')->getData();
            if ($imageFile = $form->get('image_salle')->getData() != null){
                $imageFile = $form->get('image_salle')->getData();
                $filename= uniqid() . '-' . $imageFile->getClientOriginalName();
                $imageFile->move(
                $this->getParameter('upload_directory'),
                $filename
                );
            $salle-> setImageSalle($filename);
            }
            

            $em->flush();
            return $this->redirectToRoute('showallsalles');
        }

        return $this->render('salle/edit.html.twig', [
            'form' => $form->createView(),
            'film' => $salle,
        ]);
    }


}

