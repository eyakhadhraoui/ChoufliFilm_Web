<?php

namespace App\Controller;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use App\Form\UserType;
use App\Form\ReclamationType;
use App\Form\User1Type;
use App\Form\User3Type;
use App\Form\UserType2;
use Twilio\Rest\Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Password\PasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use App\Entity\User;
use App\Entity\Reclamation;
use App\Repository\UserRepository;
use App\Repository\ReclamationRepository;
use App\Repository\FilmRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Service\GeocodingService;




 class UserController extends AbstractController
{
  
    #[Route('/user/resetpassword', name: 'reset')]
    public function nomaya(): Response
    {
        return $this->render('user/resetpassword.html.twig');
    }
    
    #[Route('/user/{id}/modifierpassword', name: 'modifierpassword', methods: ['GET', 'POST'])]
    public function modifpassword(Request $request,UserRepository $repo,UserPasswordHasherInterface $passwordHasher,EntityManagerInterface $em  ,SessionInterface $session,AuthenticationUtils $authenticationUtils,int $id): Response
    {

        $user = $repo->findOneBy(['id' => $id]);
        if ($request->isMethod('POST')) {
        
            $confirmpassword = $request->request->get('confirmpassword');
            $password = $request->request->get('password');
           
            
              if ($confirmpassword != $password ) {
                $this->addFlash('error', " Mot de pass différents");
                return $this->render('user/modifierpassword.html.twig',[
                    'id'=>$id,
                   ]);
               }
         
            if ($confirmpassword == $password ) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $user->setPassword($hashedPassword);
                $user->setConfirmPassword($hashedPassword);
                $user->setVerificationCode(null);
                $em->flush();
                return $this->redirectToRoute('app_login');
            }
          
        }
        return $this->render('user/modifierpassword.html.twig',[
         'id'=>$id,
        ]);
    }

    #[Route('/user/sendverificationcode', name: 'verificationcode', methods: ['GET', 'POST'])]
    public function verifcode(Request $request,MailerInterface $mailer,UserRepository $repo,EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
       
            $email = $request->request->get('email');
            $user = $repo->findOneBy(['email' => $email]);
            if (!$user ) {
                $this->addFlash('error', "Email n'existe pas ");
                return $this->redirectToRoute('reset');
             }
             if ($user ->getDeleted() ==1 ) {
                $this->addFlash('error', "Utilisateur a été Supprimé ");
                return $this->redirectToRoute('reset');
             
            }
            if ($user ->getBanned() ==1 ) {
                $this->addFlash('error', "Utilisateur a été banni ");
                return $this->redirectToRoute('reset');
             
            }
            else{
                $verification_code =$repo->generateVerificationCode();
                $email = (new Email())
                ->from('alpha2025group@gmail.com')
                ->to($email)  
                ->subject('Reset Password')
                ->text('Sending emails is fun again!')
                ->html('<p>this is your verification code</p>'  . $verification_code);       
            $mailer->send($email);
            $user->setVerificationCode($verification_code);
            $em->flush();
            return $this->redirectToRoute('page_verification', ['id' => $user->getId()]);
           }
        }
        return $this->render('user/resetpassword.html.twig');
    }




        #[Route('/user/{id}/verifiercode', name: 'page_verification', methods: ['GET', 'POST'])]
        public function verifiercode(Request $request,MailerInterface $mailer,UserRepository $repo,EntityManagerInterface $em,int $id): Response
        {

            if ($request->isMethod('POST')) {
                $compteur = (int) $request->request->get('compteur', 0);

                
                $code = $request->request->get('code');
                $user = $repo->findOneBy(['id' => $id]);
                if ($user->getVerificationCode() != $code ) {
                    $compteur=$compteur+1;
                    $this->addFlash('error', " Code Incorrecte ");
                    return $this->render('user/verificationcode.html.twig',[
                        'id' => $user->getId(),
                    ]);
                
                }
                else{
                    return $this->redirectToRoute('modifierpassword', ['id' => $user->getId()]); }
            }
            return $this->render('user/verificationcode.html.twig',[
        'id'=>$id,

            ]);
        }






#[Route('/', name: 'app_login', methods: ['GET', 'POST'])]
public function login(Request $request,UserRepository $repo,UserPasswordHasherInterface $passwordHasher,EntityManagerInterface $em , SessionInterface $session,AuthenticationUtils $authenticationUtils): Response
{
  
 
  
    if ($request->isMethod('POST')) {
       
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $user = $repo->findOneBy(['email' => $email]);
        
          if (!$user ) {
            $this->addFlash('error', "Email n'existe pas ");
            return $this->redirectToRoute('app_login');
         
        }
        if ($user ->getnblogin() ==5 ) {
            $user->setBanned(1);
                $em->flush();
         
        }
        
        if ($user ->getDeleted() ==1 ) {
            $this->addFlash('error', "Utilisateur a été Supprimé ");
            return $this->redirectToRoute('app_login');
         
        }
        if ($user ->getBanned() ==1 ) {
            $this->addFlash('error', "Utilisateur a été banni ");
            return $this->redirectToRoute('app_login');
         
        }
        else {
            
        $hashpass=$user->getPassword();
      
        if ($isValid = password_verify($password, $hashpass)) {
            $session->set('email', $user->getEmail());
            $session->set('id', $user->getId());
            $session->set('test', $user->getEmail());
            $session->set('user_nom', $user->getNom());
            $session->set('user_prenom', $user->getPrenom());
            $session->set('localisation', $user->getLocalisation());
            $session->set('image', $user->getImage());
            $session->set('roles', $user->getRoles());
            $session->set('naissance', $user->getDateNaissance());

            if ( ["ROLE_USER"]== $user->getRoles()) {
                $user->setnblogin(0);
                $em->flush();
            return $this->redirectToRoute('front'); 
         }
         else{
            $user->setnblogin(0);
            $em->flush();
            return $this->redirectToRoute('back');
        }
        }
        $user->setnblogin($user->getnblogin()+1);
        $em->flush();
        $this->addFlash('error', "Mot de pass Incorrect ");

        return $this->redirectToRoute('app_login');
    }
    }
    return $this->render('user/login.html.twig');
}
















    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
    #[Route('/user/film', name: 'gestion_film')]
    public function film(): Response
    {
        return $this->render('user/film.html.twig');
    }
    #[Route('/user/nodata', name: 'nodata')]
    public function nodata(): Response
    {
        return $this->render('user/nodata.html.twig');
    }
    #[Route('/user/nodatafront', name: 'nodatafront')]
    public function nodatafront(): Response
    {
        return $this->render('user/nodatafront.html.twig');
    }
    #[Route('/user/nodatafront2', name: 'nodatafront2')]
    public function nodatafront2(): Response
    {
        return $this->render('user/nodatafront.html.twig');
    }
    #[Route('/user/back', name: 'back')]
    public function back(FilmRepository $repo, UserRepository $userRepository, ReclamationRepository $reclamationRepository): Response
    {
        // Récupération des données des utilisateurs bannis
        $databanned = $userRepository->getBannedUsersByMonth();
        $months = [];
        $counts1 = [];
        foreach ($databanned as $row) {
            $months[] = $row['month']; 
            $counts1[] = $row['count'];
        }
    
        // Récupération des données des utilisateurs par rôle
        $data = $userRepository->countUsersByRole();
        $roles = [];
        $counts = [];
        foreach ($data as $row) {
            $roles[] = $row['roles']; 
            $counts[] = $row['count']; 
        }
    
        // Récupération des données des réclamations par statut
        $reclamationData = $reclamationRepository->countReclamationsByStatus();
        $statuses = [];
        $reclamationCounts = [];
        foreach ($reclamationData as $row) {
            $statuses[] = $row['status']; 
            $reclamationCounts[] = $row['count']; 
        }
    
        // Récupération des données des réclamations par type (nouvelle statistique)
        $reclamationDataByType = $reclamationRepository->countReclamationsByType(); // Méthode à définir dans le repository
        $types = [];
        $reclamationCountsByType = [];
        foreach ($reclamationDataByType as $row) {
            $types[] = $row['type'];
            $reclamationCountsByType[] = $row['count'];
        }
    
        // Récupération des films
        $films = $repo->findAll();
        
        // Transmission des données à la vue
        return $this->render('user/back.html.twig', [
            'films' => $films,
            'roles' => json_encode($roles),
            'counts' => json_encode($counts),
            'months' => json_encode($months),
            'counts1' => json_encode($counts1),
            'statuses' => json_encode($statuses), // Ajout des statuts
            'reclamationCounts' => json_encode($reclamationCounts), // Ajout des counts des réclamations
            'types' => json_encode($types), // Ajout des types de réclamation
            'reclamationCountsByType' => json_encode($reclamationCountsByType), // Ajout des counts des réclamations par type
        ]);
    }
    


   
    #[Route('/user/back/gestion_user', name: 'back_user', methods: ['GET', 'POST'])]
    public function gestion_user_back(UserRepository $repo, Request $request): Response
    {
        $page = $request->query->getInt('page', 1); 
        $users = $repo->findActiveUsers($page,1);
        return $this->render('user/gestion_user_back.html.twig', [
            'users' => $users['data'], 
            'page' => $page,    
            'limit'=>$users['limit'],
            'pages'=>$users['pages'],
        ]);
       
    }
    #[Route('/user/back/gestion_user1', name: 'back_user1', methods: ['GET', 'POST'])]
    public function gestion_user_back1(UserRepository $repo, Request $request): Response
    {
        $page = $request->query->getInt('page', 1); 
    
        if ($request->isMethod('POST')) {
            $te = $request->request->get('nombre_pagination');
            
         
            if ($te != null && $te > 0) {
                $request->getSession()->set('nombre_pagination', $te);
            }
        } 
        else {
           
            $te = $request->getSession()->get('nombre_pagination'); 
        }
    
       
        if ($te <= 0) {
            return $this->redirectToRoute('showall');
        } else {
            $users = $repo->findActiveUsers($page, $te);
            return $this->render('user/gestion_user_back.html.twig', [
                'users' => $users['data'],
                'page' => $page,
                'limit' => $users['limit'],
                'pages' => $users['pages'],
            ]);
        }
    }
    


    #[Route('/user/back/showall', name: 'showall')]
    public function showall(UserRepository $repo): Response
    {
    $users = $repo->showall();
    return $this->render('user/gestion_user_back.html.twig',[
        'users' =>$users,
        'page' => 0,    
        'limit'=> 0,
        'pages'=>0
        ]);
       
    }
    
    #[Route('/user/back/{id}/one_user', name: 'one_user')]
    public function one_user_back(UserRepository $repo, Request $request,int $id): Response
    {
       
        $user =$repo->find($id);
        if($user){
            return $this->render('user/detail_user.html.twig', [
                'us' => $user 
            ]);
        }
        else{
            return $this->redirectToRoute('showall'); 
        }
       
    }
    #[Route('/user/front', name: 'front')]
    public function front(): Response
    {
         return $this->render('user/front.html.twig');  
    }
  
    #[Route('/user/inscription', name: 'inscription')]
  
    public function inscription(GeocodingService $geocodingService , Request $request,EntityManagerInterface $em,UserPasswordHasherInterface $passwordHasher,UserRepository $repo)
{
  
    $user= new User();
    $user->setRoles(["ROLE_USER"]);
    $user->setDeleted(0);
    $user->setBanned(0);
            
$form =$this->createForm(UserType::class, $user);
$form->handleRequest($request);

 if($form->isSubmitted()&& $form->isValid())
 {
    $test = $repo->findOneBy(['email' => $user->getEmail()]);
    if ($user->getPassword() !== $user->getConfirmPassword() ) { 
        return $this->render('user/inscription.html.twig',[
            'form' => $form->createView(),
            ]);
     
    }
    if($test != null ){
         return $this->render('user/inscription.html.twig',[
            'form' => $form->createView(),
            ]);
    }
    $imageFile = $form->get('image')->getData();
    if ($imageFile = $form->get('image')->getData() != null){
        $imageFile = $form->get('image')->getData();
        $filename= uniqid() . '-' . $imageFile->getClientOriginalName();
        $imageFile->move(
            $this->getParameter('upload_directory'),
            $filename
        );
    $user->setImage($filename);
    }  
    else {
        $user->setImage('inconnu.jpg');  
    }  
$pass = $user->getPassword();

$hashedPassword = password_hash($pass, PASSWORD_BCRYPT);
$user->setPassword($hashedPassword);
$user->setConfirmPassword($hashedPassword);
$latitude = $request->request->get('latitude');
$longitude = $request->request->get('longitude');
if($latitude ==null){
return $this->render('user/inscription.html.twig',[
    'form' => $form->createView(),
    ]);}
    if($longitude ==null){
        return $this->render('user/inscription.html.twig',[
            'form' => $form->createView(),
            ]);}


$adress=$geocodingService->getFormattedAddress($latitude,$longitude);
$user->setLocalisation($adress);
$notification = new Notification();
$notification->setMessage('Nouveau user ajouté  '.   $user->getNom() . '  ' . $user->getPrenom());
$notification->setImage($user->getImage());
$notification->setType('user');
$em->persist($notification);
 $em->persist($user);
 $em->flush();

 return $this->redirectToRoute('app_login');

}
 else 
 {

 return $this->render('user/inscription.html.twig',[
'form' => $form->createView(),
]);
 }
}

#[Route('/user/logout', name: 'logout')]
public function logout(SessionInterface $session)
{
    $session->invalidate(); 
   
    return $this->redirectToRoute('app_login'); 
}



#[Route('/user/back/deleted', name: 'corbeille')]
public function corbeille(UserRepository $repo): Response
{
    $users = $repo->findInactiveUsers();
return $this->render('user/deleted.html.twig',[
  
    'users' =>$users
    ]);
}
#[Route('/user/back/{id}/ban', name: 'ban')]
public function ban(UserRepository $repo, Request $request, EntityManagerInterface $em,int $id)
{
    $user =$repo->find($id);

    $user->setBanned(1); 
    $moisActuel = (new \DateTime())->format('F'); // "February" par défaut en anglais

    $moisFrancais = [
        'January' => 'Janvier', 'February' => 'Février', 'March' => 'Mars',
        'April' => 'Avril', 'May' => 'Mai', 'June' => 'Juin',
        'July' => 'Juillet', 'August' => 'Août', 'September' => 'Septembre',
        'October' => 'Octobre', 'November' => 'Novembre', 'December' => 'Décembre'
    ];

    $user->setBannedAt($moisFrancais[$moisActuel]); 
    $em->flush();
    return $this->redirectToRoute('showall');
}
#[Route('/user/back/{id}/unban', name: 'unban')]
public function unban(UserRepository $repo, Request $request, EntityManagerInterface $em,int $id)
{
    $user =$repo->find($id);
    $user->setBanned(0); 
    $em->flush();
    return $this->redirectToRoute('showall');
}

#[Route('/user/back/{id}/restaurer', name: 'restaurer')]
public function restaurer(UserRepository $repo, Request $request, EntityManagerInterface $em,int $id)
{
    $user =$repo->find($id);

    $user->setDeleted(0); 
    $em->flush();
    return $this->redirectToRoute('showall');
}

#[Route('/user/back/{id}/remove',name:'removeuser')]
public function deleteetudiant(UserRepository $repo,EntityManagerInterface $em,int $id){
 $user =$repo->find($id);
 if($user){
    $em->remove($user);
    $em->flush();
   
    return $this->redirectToRoute('corbeille');
 }
}
#[Route('/user/back/{id}/corbeille', name: 'envoyerCorbeille')]
public function envoyercorbeiiille(UserRepository $repo, Request $request, EntityManagerInterface $em,int $id,SessionInterface $session)
{
    $user =$repo->find($id);
    $user->setDeleted(1); 
    $em->flush();
    if($session->get('id') == $id){
        $session->set('user_nom', null);     
    }
    return $this->redirectToRoute('showall');

}
#[Route('/user/back/restaure-all', name: 'restaureall')]
public function restaureall(UserRepository $repo,EntityManagerInterface $em): Response
{
    $users = $repo->findInactiveUsers();

    if (!empty($users)) {

        foreach ($users as $user) {
            $user->setDeleted(0); 
        }
        $em->flush();
        return $this->redirectToRoute('showall');
    }
    else{
        return $this->redirectToRoute('corbeille');
    }
}


#[Route('/user/back/remove-multiple', name: 'remove_multiple_users')]
public function deleteMultipleUsers(UserRepository $repo,EntityManagerInterface $em): Response
{
    $users = $repo->findInactiveUsers();

    if (!empty($users)) {

        foreach ($users as $user) {
            $em->remove($user);
        }
        $em->flush();
        return $this->redirectToRoute('corbeille');
    }
    else{
        return $this->redirectToRoute('corbeille');
    }
}
#[Route('/user/back/ajoutback', name: 'ajoutback')]
  
public function ajoutback(Request $request,EntityManagerInterface $em,UserPasswordHasherInterface $passwordHasher,UserRepository $repo)
{

$user= new User();

$user->setDeleted(0);
$user->setBanned(0);
        
$form =$this->createForm(User3Type::class, $user);
$form->handleRequest($request);

if($form->isSubmitted()&& $form->isValid())
{

$test = $repo->findOneBy(['email' => $user->getEmail()]);
if($test != null){
     return $this->render('user/ajoutuserback.html.twig',[
        'form' => $form->createView(),
        ]);
}

if ($user->getPassword() == $user->getConfirmPassword() ) { 

$imageFile = $form->get('image')->getData();
if ($imageFile = $form->get('image')->getData() != null){
    $imageFile = $form->get('image')->getData();
    $filename= uniqid() . '-' . $imageFile->getClientOriginalName();
    $imageFile->move(
        $this->getParameter('upload_directory'),
        $filename
    );
$user->setImage($filename);
}  
else {
    $user->setImage('inconnu.jpg');  
} 

$pass = $user->getPassword();
$hashedPassword = password_hash($pass, PASSWORD_BCRYPT);
$user->setPassword($hashedPassword);
$user->setConfirmPassword($hashedPassword);
$em->persist($user);
$em->flush();
return $this->redirectToRoute('back');
}
else{
    return $this->render('user/ajoutuserback.html.twig',[
        'form' => $form->createView(),
        ]);

}
}
else 
{
return $this->render('user/ajoutuserback.html.twig',[
'form' => $form->createView(),
]);
}
}



#[Route('/user/front/{id}/edit', name: 'edit_user')]
public function editfront(UserRepository $repo, Request $request, EntityManagerInterface $em,String $id,UserPasswordHasherInterface $passwordHasher,SessionInterface $session)
{
    
    if($session->get('id') != $id){
        return $this->redirectToRoute('logout');   
    }
    $user =$repo->find($id);
    $form = $this->createForm(UserType2::class, $user);
    $form->handleRequest($request);
   $image=$user->getImage();

    if ($form->isSubmitted() && $form->isValid()) {
    
        $imageFile = $form->get('image')->getData();
        if ($imageFile = $form->get('image')->getData() != null){
            $imageFile = $form->get('image')->getData();
            $filename= uniqid() . '-' . $imageFile->getClientOriginalName();
            $imageFile->move(
                $this->getParameter('upload_directory'),
                $filename
            );

        
        $user->setImage($filename);
        }
        else{
            $user->setImage($image); 
        }
        $em->persist($user);
         $em->flush();
         $session->set('image', $user->getImage());
         $session->set('email', $user->getEmail());
         
         $session->set('test', $user->getEmail());
         $session->set('user_nom', $user->getNom());
         $session->set('user_prenom', $user->getPrenom());
         $session->set('localisation', $user->getLocalisation());
       
        return $this->redirectToRoute('front'); 

    }
else{
    return $this->render('user/editfront.html.twig', [
        'form' => $form,
        'user' =>$user,
       
    ]);
}

}






#[Route('/user/back/{id}/edit', name: 'editback')]
public function editback(UserRepository $repo, Request $request, EntityManagerInterface $em,String $id,UserPasswordHasherInterface $passwordHasher,SessionInterface $session)
{
    
    if($session->get('id') != $id){
        return $this->redirectToRoute('logout');   
    }
    $user =$repo->find($id);
    $form = $this->createForm(UserType2::class, $user);
    $form->handleRequest($request);
   $image=$user->getImage();

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();
        if ($imageFile = $form->get('image')->getData() != null){
            $imageFile = $form->get('image')->getData();
            $filename= uniqid() . '-' . $imageFile->getClientOriginalName();
            $imageFile->move(
                $this->getParameter('upload_directory'),
                $filename
            );

        
        $user->setImage($filename);
        }
        else{
            $user->setImage($image); 
        }
        $em->persist($user);
         $em->flush();
         $session->set('image', $user->getImage());
         $session->set('email', $user->getEmail());
         
         $session->set('test', $user->getEmail());
         $session->set('user_nom', $user->getNom());
         $session->set('user_prenom', $user->getPrenom());
         $session->set('localisation', $user->getLocalisation());
       
        return $this->redirectToRoute('back'); 

    }
else{

    return $this->render('user/editback.html.twig', [   
        'form' => $form,
       
    ]);
}



}


#[Route('/user/back/{id}/monter', name: 'monter')]
public function monter(UserRepository $repo, Request $request, EntityManagerInterface $em,int $id)
{
    $user =$repo->find($id);

    $user->setRoles(["ROLE_ADMIN"]); 
   
    $em->flush();
    return $this->redirectToRoute('showall');
}

#[Route('/user/back/{id}/demonter', name: 'demonter')]
public function demonter(UserRepository $repo, Request $request, EntityManagerInterface $em,int $id,SessionInterface $session)
{
    $user =$repo->find($id);

    $user->setRoles(["ROLE_USER"]); 
    $em->flush();
    if ($session->get('email')==$user->getEmail()){
    $session->set('roles', $user->getRoles());}
    return $this->redirectToRoute('showall');
}


#[Route('/user/back/{id}/suppphoto', name: 'supppphoto')]
public function suprimer_photo(UserRepository $repo, Request $request, EntityManagerInterface $em,int $id,SessionInterface $session)
{

    if($session->get('id') != $id){
        return $this->redirectToRoute('logout');   
    }
    $user =$repo->find($id);
     if ($user){
    $user->setImage("inconnu.jpg");
    $em->flush();
    $session->set('image', $user->getImage());
    return $this->redirectToRoute('front');
     } 
     else{
        return $this->redirectToRoute('logout');  
     }


   
}

#[Route('/user/back/{id}/suppphotoback', name: 'supppphotoback')]
public function suprimer_photoback(UserRepository $repo, Request $request, EntityManagerInterface $em,int $id,SessionInterface $session)
{

    if($session->get('id') != $id){
        return $this->redirectToRoute('logout');   
    }
    $user =$repo->find($id);
     if ($user){
    $user->setImage("inconnu.jpg"); 
    $em->flush();
    $session->set('image', $user->getImage());
    return $this->redirectToRoute('showall');
     } 
     else{
        return $this->redirectToRoute('logout');  
     }


   
}


#[Route('/email', name: 'send_email')]
public function sendEmail(Request $request,MailerInterface $mailer,UserRepository $repo): Response
{
    $email = (new Email())
 
        ->from('alpha2025group@gmail.com')
        ->to('mohamedeya.khadhraoui@esprit.tn')  
        ->subject('tester mailing')
        ->text('Sending emails is fun again!')
        ->html('<p>this is your verification code</p>'  . $repo->generateVerificationCode());

    
    $mailer->send($email);
    return $this->redirectToRoute('app_login');
    
}

#[Route('/smstexto', name: 'send_sms')]
public function sendsms(Request $request, MailerInterface $mailer, UserRepository $repo): Response
{
    $accountSid = $this->getParameter('twilio.account_sid');
    $authToken = $this->getParameter('twilio.auth_token');
    $twilioNumber = $this->getParameter('twilio.number');
    

    $client = new Client(
        $accountSid, 
        $authToken, 
        null, 
        null, 
        new \Twilio\Http\CurlClient([
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ])
    );
    
    $client->messages->create(
        '+21658425414', 
        [
            'from' => $twilioNumber,
            'body' => 'test test  '
        ]
    );
    
    
    return $this->redirectToRoute('app_login');
}

#[Route('/resetfrontpas', name: 'app_resetuserpassword', methods: ['GET', 'POST'])]
public function reset_user_page(Request $request,UserRepository $repo, SessionInterface $session,AuthenticationUtils $authenticationUtils,EntityManagerInterface $em): Response
{
    $verification_code1 =$repo->generateVerificationCode();
    $user=$repo->find($session->get('id'));
    $user->setVerificationCode($verification_code1);
    $em->flush();
     
      if ($request->isMethod('POST')) {
       
        $email =$session->get('email');
        $oldpassword = $request->request->get('oldpassword');
        $newpassword = $request->request->get('newpassword');
        $confirmpassword = $request->request->get('confirmpassword');
        $verificationcode = $request->request->get('code');
        
        $user = $repo->findOneBy(['email' => $email]);
       
        if ( !$isValid = password_verify($oldpassword, $user->getPassword()))  {
            $this->addFlash('error', "Faux Password ");
            return $this->render('user/resetpass.html.twig',[
                'code' => $verification_code1,
              ]); 
         
        }
        if ($newpassword != $confirmpassword){
        $this->addFlash('error', "Les 2 pass sont différents");
        return $this->render('user/resetpass.html.twig',[
            'code' => $verification_code1,
          ]);
        
        }
     
        if ( ($isValid = password_verify($oldpassword, $user->getPassword())) && $newpassword == $confirmpassword && $user->getVerificationCode()!=$verificationcode )  {
            $hashedPassword = password_hash($newpassword, PASSWORD_BCRYPT);
            $user->setPassword($hashedPassword);
            $user->setConfirmPassword($hashedPassword);
            $em->persist($user);
            $em->flush();
            return $this->render('user/front.html.twig');
         
        }
    }
  

    
   
    return $this->render('user/resetpass.html.twig',[
      'code' => $verification_code1,
    ]);
}





}