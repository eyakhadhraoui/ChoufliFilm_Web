<?php

namespace App\Controller;
use App\Entity\Reclamation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ReclamationRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ReclamationType;
use App\Form\ReclamationeditType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use App\Form\UserType;
use App\Form\User1Type;
use App\Form\User3Type;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Password\PasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use App\Entity\User;
use App\Entity\Reponse;
use App\Service\SentimentService;
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
final class ReclamationController extends AbstractController
{
    #[Route('/reclamation', name: 'app_reclamation')]
    public function index(): Response
    {
        return $this->render('reclamation/index.html.twig', [
            'controller_name' => 'ReclamationController',
        ]);
    }
    #[Route('/reclamation/create/{id}', name: 'create_reclamation')]
    public function create(
        Request $request,
        ReclamationRepository $repo,
        UserRepository $repo1,
        EntityManagerInterface $em,
        SessionInterface $session,
        int $id,
        SentimentService $sentimentService
    ): Response {
        $reclamation = new Reclamation();
        $test = $repo1->find($id);
        $email = $test->getEmail();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile !== null) {
                $filename = uniqid() . '-' . $imageFile->getClientOriginalName();
                $imageFile->move(
                    $this->getParameter('upload_directory'),
                    $filename
                );
                $reclamation->setImage($filename);
            } else {
                $reclamation->setImage(null);
            }
    
            $reclamation->setStatus("En cours");
            $reclamation->setUser($test);
            $sentimentService->setReclamationPriority($reclamation);
    
           
            $api_user = "637714805";
            $api_secret = "8buD5VVoRw4bZe4sZWoBXD4W8s3dTBwv";
            $text = $reclamation->getDescription();
    
            $url = "https://api.sightengine.com/1.0/text/check.json";
            $data = [
                'text' => $text,
                'lang' => 'fr',
                'mode' => 'standard',
                'api_user' => $api_user,
                'api_secret' => $api_secret
            ];
    
            $options = [
                'http' => [
                    'header'  => "Content-Type: application/x-www-form-urlencoded",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                ]
            ];
    
            $context  = stream_context_create($options);
            $response = file_get_contents($url, false, $context);
            $responseData = json_decode($response, true);
    
            if (isset($responseData['profanity']['matches']) && count($responseData['profanity']['matches']) > 0) {
                $this->addFlash('error', "Bad Words Detected ");
                return $this->render('reclamation/create.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
    
          
            $em->persist($reclamation);
            $em->flush();
    
            return $this->redirectToRoute('showallreclamation', ['email' => $email]);
        }
    
        return $this->render('reclamation/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    




     
    

    #[Route('/reclamation/showallback', name: 'showallreclamationback')]
    public function showallreclamationback(ReclamationRepository $repo): Response
    {

        
        $reclamations = $repo->findall();
    return $this->render('reclamation/showback.html.twig',[
        'reclamations' =>$reclamations,

        ]);
       
    }

    #[Route('/reclamation/showallback/typetechnique', name: 'showallreclamationbacktechnique')]
    public function showallreclamationbacktechnique(ReclamationRepository $repo): Response
    {
        $reclamations = $repo->findBy(['type' =>'Type Technique']); 
    
    return $this->render('reclamation/showback.html.twig',[
        'reclamations' =>$reclamations,

        ]);
       
    }
    #[Route('/reclamation/showallback/typeReservation', name: 'showallreclamationresercation')]
    public function showallreclamationbackreservation(ReclamationRepository $repo): Response
    {
        $reclamations = $repo->findBy(['type' =>'Type lié de Reservation']); 
    
    return $this->render('reclamation/showback.html.twig',[
        'reclamations' =>$reclamations,

        ]);
       
    }
    #[Route('/reclamation/showallback/typeautre', name: 'showallreclamationautre')]
    public function showallAutre(ReclamationRepository $repo): Response
    {
        $reclamations = $repo->findBy(['type' =>'Autre']); 
    
    return $this->render('reclamation/showback.html.twig',[
        'reclamations' =>$reclamations,

        ]);
       
    }


    

    #[Route('/reclamation/showallback/typePaiement', name: 'showallPaiement')]
    public function showallreclamationbackpaiement(ReclamationRepository $repo): Response
    {
        $reclamations = $repo->findBy(['type' =>'type lié de Paiement']); 
    
    return $this->render('reclamation/showback.html.twig',[
        'reclamations' =>$reclamations,

        ]);
       
    }





    #[Route('/reclamation/showall/{email}', name: 'showallreclamation')]
    public function showallreclamation(ReclamationRepository $repo,UserRepository $repo1,String $email): Response
    {
        $user = $repo1->findOneBy(['email' => $email]);
        $reclamations = $repo->findBy(['user' => $user]); 

        return $this->render('reclamation/show.html.twig', [
            'reclamations' => $reclamations,
        ]);
        
    }

  

#[Route('/reclamation/{id}/remove',name:'removereclamation')]
public function deleteetudiant(ReclamationRepository $repo,UserRepository $repo1,EntityManagerInterface $em,int $id){
 $reclamation =$repo->find($id);
 
  $email =$reclamation->getUserEmail();
 if($reclamation){
    $em->remove($reclamation);
    $em->flush();
    return $this->redirectToRoute('showallreclamation', ['email' => $email]);
 }
}
#[Route('/reclamation/{id}/removeback',name:'removereclamationback')]
public function deletreclamationback(ReclamationRepository $repo,UserRepository $repo1,EntityManagerInterface $em,int $id){
 $reclamation =$repo->find($id);
 
  $email =$reclamation->getUserEmail();
 if($reclamation){
    $em->remove($reclamation);
    $em->flush();
    return $this->redirectToRoute('showallreclamationback');
 }
}
#[Route('/reclamation/{id}/edit/{user_id}',name:'editreclamation')]

public function edit(ReclamationRepository $repo,UserRepository $repo1 ,Request $request, EntityManagerInterface $em,int $id,int $user_id,SessionInterface $session)
{
    $user = $repo1->find($user_id);
    $reclamation =$repo->find($id);
    if ($reclamation->getUser() !== $user) 
    {
        return $this->redirectToRoute('logout'); 
    }
    if($session->get('id') != $user_id)
    {
        return $this->redirectToRoute('logout');   
    }
    $reclamation =$repo->find($id);
    $form = $this->createForm(ReclamationeditType::class, $reclamation);
    $form->handleRequest($request);
   
    $email=$reclamation->getUserEmail();
    $image=$reclamation->getImage();
  $statu=$reclamation->getStatus();


    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();
        if ($imageFile = $form->get('image')->getData() != null){
            $imageFile = $form->get('image')->getData();
            $filename= uniqid() . '-' . $imageFile->getClientOriginalName();
            $imageFile->move(
                $this->getParameter('upload_directory'),
                $filename
            );
        
        $reclamation->setImage($filename);
        }
        else{
            $reclamation->setImage($image); 
        }
           
            $reclamation->setStatus($statu);  
            

        $em->persist($reclamation);
        $em->flush();      
        return $this->redirectToRoute('showallreclamation', ['email' => $email]);
    }
else{
    return $this->render('reclamation/edit.html.twig', [
        'form' => $form,
        'us' => $reclamation,
       
    ]);
}
}
#[Route('/reclamation/back/{id}/one_reclamation', name: 'one_reclamation')]
    public function one_user_back(ReclamationRepository $repo, Request $request,int $id): Response
    {
       
        $reclamation =$repo->find($id);
        if($reclamation){
            return $this->render('reclamation/detail.html.twig', [
                'us' => $reclamation
            ]);
        }
        else{
            return $this->redirectToRoute('showallreclamation');
        }
       
    }

}
