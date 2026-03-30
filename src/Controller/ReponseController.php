<?php

namespace App\Controller;
use App\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Form\ReponseautreType;
use App\Repository\ReclamationRepository;
use App\Repository\ReponseRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ReclamationType;
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
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
final class ReponseController extends AbstractController
{
    #[Route('/reponse', name: 'app_reponse')]
    public function index(): Response
    {
        return $this->render('reponse/index.html.twig', [
            'controller_name' => 'ReponseController',
        ]);
    }

   
    #[Route('/reponse/create/{id}', name: 'create_reponse')]
    public function create(Request $request,ReclamationRepository $repo,MailerInterface $mailer,UserRepository $repo1,EntityManagerInterface $em,SessionInterface $session,int $id): Response
    {

           
          
           
           $test = $repo->find($id); 
           $reclamation = $repo->find($id);
           $reponse= new Reponse();
           $user =$test->getUser();
          $email=$session->get('email');
           $form =$this->createForm(ReponseType::class, $reponse);
           $form->handleRequest($request);
        
         if($form->isSubmitted()&& $form->isValid())
         {
            
            $email = (new Email())
            ->from('alpha2025group@gmail.com')
            ->to($reclamation->getUser()->getEmail())  
            ->subject('Repondre Réclamation')
            ->text('Sending emails is fun again!')
            ->html('Nous avons Repondu  votre reclamation  mr (mme) ' . $reclamation->getUser()->getNom() .'  '.    $reclamation->getUser()->getPrenom(). '<br>   Reponse :           ' .$reponse->getReponse() );       
            $mailer->send($email);
            $reponse->setReclamation($test);
            $reponse->setReclamationStatus("Résolu");
           
            $em->persist($reponse);  
       
      
         $em->flush();
 
    
        
         return $this->redirectToRoute('showallreclamationback');

        
        }
         else
         {
        
         return $this->render('reponse/create.html.twig',[
        'form' => $form->createView(),
        'user' => $user,
        ]);
         }
        }

    #[Route('/reponse/create2/{id}', name: 'create_reponse2')]
    public function create2(Request $request,ReclamationRepository $repo,MailerInterface $mailer,UserRepository $repo1,EntityManagerInterface $em,SessionInterface $session,int $id): Response
    {           
           $test = $repo->find($id);  
           $reponse= new Reponse();
           $user =$test->getUser();
           $reclamation = $repo->find($id); 
           $form =$this->createForm(ReponseautreType::class, $reponse);
           $form->handleRequest($request);
           
         if($form->isSubmitted()&& $form->isValid())
         {
    
          if ($reponse->getReponse() == "Autre" ){

            return $this->redirectToRoute('create_reponse', ['id' => $id]);

          }
            $reponse->setReclamation($test);
            $reponse->setReclamationStatus("Résolu");
            $email = (new Email())
            ->from('alpha2025group@gmail.com')
            ->to($reclamation->getUser()->getEmail())  
            ->subject('Repondre Réclamation')
            ->text('Sending emails is fun again!')
            ->html('Nous avons Repondu  votre reclamation  mr (mme) ' . $reclamation->getUser()->getNom() .'  '.    $reclamation->getUser()->getPrenom(). '<br>   Reponse :           ' .$reponse->getReponse() );       
            $mailer->send($email);  
        
            $em->persist($reponse);  
       
      
         $em->flush();
 
    
        
         return $this->redirectToRoute('showallreclamationback');

        
        }
         else
         {
        
         return $this->render('reponse/create2.html.twig',[
        'form' => $form->createView(),
        'user' => $user,
        ]);
         }

    }


    #[Route('/reponse/rejeter/{id}', name: 'rejeter_reponse')]
public function rejetr(Request $request, ReclamationRepository $repo,MailerInterface $mailer,UserRepository $repo1, EntityManagerInterface $em, SessionInterface $session, int $id): Response
{
    $reponse = new Reponse();
    $reclamation = $repo->find($id);

    
       
    $reclamation->setStatus("Rejeté");

    if ($reclamation->getReponse() == null) {
        
        $reponse->setReclamation($reclamation);
        $reclamation->setStatus("Rejeté");
        $reponse->setReponse("Réclamation rejetée"); 

        $em->persist($reponse);
    } else {
        $reclamation->setReponseTexte("Réclamation rejetée"); 
    }
    $email = (new Email())
    ->from('alpha2025group@gmail.com')
    ->to($reclamation->getUser()->getEmail())  
    ->subject('Repondre Réclamation')
    ->text('Sending emails is fun again!')
    ->html('Nous avons Rejeté votre reclamation  mr (mme) ' . $reclamation->getUser()->getNom() .'  '.    $reclamation->getUser()->getPrenom());       
    $mailer->send($email);
    $em->flush();

    return $this->redirectToRoute('showallreclamationback');
}

         
  
     
    }











