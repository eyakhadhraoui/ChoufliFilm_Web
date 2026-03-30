<?php

namespace App\Controller;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AssociationType;
use App\Form\RessourceType;
use App\Repository\UserRepository;
use App\Repository\RessourceRepository;
use App\Repository\AssociationRepository;
use App\Entity\Ressource;
final class RessourceController extends AbstractController
{
    #[Route('/ressource', name: 'app_ressource')]
    public function index(): Response
    {
        return $this->render('ressource/index.html.twig', [
            'controller_name' => 'RessourceController',
        ]);
    }

    #[Route('/ressource/create/{user_id}/{association_id}', name: 'createressource')]
    public function createressouurce(Request $request,EntityManagerInterface $em,AssociationRepository $repo,
    RessourceRepository $repo1,UserRepository $repo2
    ,int $user_id,int $association_id)
{
  
$ressource= new Ressource();
$user = $repo2->find($user_id);
$association = $repo->find($association_id);

$form =$this->createForm(RessourceType::class, $ressource);
$form->handleRequest($request);

 if($form->isSubmitted()&& $form->isValid())
 {
    $ressource->setAssociation($association);
$ressource->setUser($user);
$notification = new Notification();
$notification->setMessage('Nouvelle Ressource par Mr : ' . $user->getNom() . ' ' . $user->getPrenom(). "     a l'association    "  . $association->getNom() .' avec montant ' .$ressource->getBesoinSpecifique());
$notification->setType('ressource');
$em->persist($notification);
 $em->persist($ressource);
 $em->flush();
 return $this->redirectToRoute('showallressourcefront', ['email' => $user->getEmail()]);
 
}
 else 
 {
 return $this->render('ressource/create.html.twig',[
'form' => $form->createView(),
]);
 }
}


#[Route('/ressource/showall/{email}', name: 'showallressourcefront')]
public function showallreclamation(RessourceRepository $repo,UserRepository $repo1,String $email): Response
{
    $user = $repo1->findOneBy(['email' => $email]);
    $ressource = $repo->findBy(['user' => $user]); 

    return $this->render('ressource/showallfront.html.twig', [
        'ressource' => $ressource,
    ]);
    
}
#[Route('/ressource/backressource/{id}', name: 'backressource')]
public function backassociation(RessourceRepository $repo,AssociationRepository $repo1,int $id): Response
{
    $association = $repo1->findOneBy(['id' => $id]);
    $ressources = $repo->findBy(['association' => $association]); 
    
    return $this->render('ressource/showallback.html.twig',[
        'ressource' =>$ressources,
        'aso'=>$association,
    
        ]); 
}


#[Route('/ressource/createback/{user_id}/{association_id}', name: 'createressourceback')]
public function createressouurceback(Request $request,EntityManagerInterface $em,AssociationRepository $repo,
RessourceRepository $repo1,UserRepository $repo2
,int $user_id,int $association_id)
{

$ressource= new Ressource();
$user = $repo2->find($user_id);
$association = $repo->find($association_id);

$form =$this->createForm(RessourceType::class, $ressource);
$form->handleRequest($request);

if($form->isSubmitted()&& $form->isValid())
{
$ressource->setAssociation($association);
$ressource->setUser($user);

$em->persist($ressource);
$em->flush();
return $this->redirectToRoute('backressource', ['id' => $association->getId()]);

}
else 
{
return $this->render('ressource/createback.html.twig',[
'form' => $form->createView(),
]);
}
}


}
