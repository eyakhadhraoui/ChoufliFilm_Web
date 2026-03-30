<?php

namespace App\Controller;
use App\Entity\Association;
use App\Entity\Notification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AssociationType;
use App\Repository\AssociationRepository;
use App\Repository\RessourceRepository;
use Knp\Component\Pager\PaginatorInterface;
final class AssociationController extends AbstractController
{
    #[Route('/association', name: 'app_association')]
    public function index(): Response
    {
        return $this->render('association/index.html.twig', [
            'controller_name' => 'AssociationController',
        ]);
    }

    #[Route('/association/create', name: 'createassociation1')]

    public function createAssociation(Request $request,EntityManagerInterface $em,AssociationRepository $repo)
{
  
    $association= new Association();

$form =$this->createForm(AssociationType::class, $association);

$form->handleRequest($request);

 if($form->isSubmitted()&& $form->isValid())
 {
    
   
    $imageFile = $form->get('image')->getData();
    if ($imageFile = $form->get('image')->getData() != null){
        $imageFile = $form->get('image')->getData();
        $filename= uniqid() . '-' . $imageFile->getClientOriginalName();
        $imageFile->move(
        $this->getParameter('upload_directory'),
        $filename
        );
    
    $association->setImage($filename);
    }
    else {
 return $this->render('association/create.html.twig',[
    'form' => $form->createView(),
    ]);
    }  

  
   
    
    $em->persist($association);
    $em->flush();

 return $this->redirectToRoute('showallassociationnback');

}
 else 
 {

 return $this->render('association/create.html.twig',[
'form' => $form->createView(),
]);
 }
}

   
    #[Route('/association/showallback', name: 'showallassociationnback')]
public function showAllAssociationBack(
    AssociationRepository $repo,
    PaginatorInterface $paginator,
    Request $request
): Response {
    $query = $repo->createQueryBuilder('a')->getQuery();

    $pagination = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        2
    );

    return $this->render('association/showallback.html.twig', [
        'pagination' => $pagination
    ]);
}


#[Route('/association/back/{id}/one_association', name: 'one_associationback')]
    public function one_association_back(AssociationRepository $repo, Request $request,int $id): Response
    {
       
        $association =$repo->find($id);
        if($association){
            return $this->render('association/detail.html.twig', [
                'us' => $association
            ]);
        }
        else{
            return $this->redirectToRoute('showallassociationnback');
        }
       
    }
    #[Route('/association/{id}/removeback',name:'removeassociationback')]
public function deletreclamationback(AssociationRepository $repo,RessourceRepository $repo1,EntityManagerInterface $em,int $id){
 $association =$repo->find($id);
 $ressources = $repo1->findBy(['association' => $association]); 
 if($association){
    $em->remove($association);
    foreach ($ressources as $ressource) {
        $em->remove($ressource);
    }
    $em->flush();
    return $this->redirectToRoute('showallassociationnback');
 }
}




#[Route('/association/{id}/edit', name: 'editassociation')]

    public function EditAssociation(Request $request,EntityManagerInterface $em,AssociationRepository $repo,int $id)
{
$association =$repo->find($id); 
$image=$association->getImage();  
$form =$this->createForm(AssociationType::class, $association);
$form->handleRequest($request);
 if($form->isSubmitted()&& $form->isValid())
 {  
    $imageFile = $form->get('image')->getData();
    if ($imageFile = $form->get('image')->getData() != null){
        $imageFile = $form->get('image')->getData();
        $filename= uniqid() . '-' . $imageFile->getClientOriginalName();
        $imageFile->move(
        $this->getParameter('upload_directory'),
        $filename
        );
    
    $association->setImage($filename);
    }
    else{
        $association->setImage($image); 
    } 

 $em->persist($association);
 $em->flush();

 return $this->redirectToRoute('showallassociationnback');

}
 else 
 {

 return $this->render('association/edit.html.twig',[
'form' => $form->createView(),
]);
 }
}


#[Route('/association/frontassociation', name: 'frontassociation')]
public function frontassoication(AssociationRepository $repo): Response
{
    $associations = $repo->findall();
    return $this->render('association/showallfront.html.twig',[
        'associations' =>$associations,
    
        ]); 
}






}
