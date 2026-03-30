<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Commentaire;
use App\Entity\Article;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\UserRepository;
class CommentaireController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/article/{id}', name: 'article_detail')]
    public function detail(Article $article, Request $request,SessionInterface $session,UserRepository $repo): Response
    {
        $user  =$repo->find($session->get('id'));
        // Créer un nouveau commentaire
        $commentaire = new Commentaire();

        // Créer un formulaire pour ce commentaire
        $form = $this->createForm(CommentaireType::class, $commentaire);

        // Traiter la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Si l'utilisateur est connecté
            if ($article) {
                $commentaire->setArticle($article);
                $commentaire->setUser($user);
                // Utilisation de l'utilisateur connecté via la session
                $this->entityManager->persist($commentaire);
                $this->entityManager->flush();

                // Rediriger pour éviter le double envoi du formulaire
                return $this->redirectToRoute('article_detail', ['id' => $article->getId()]);
            } else {
                // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
                $commentaires = $this->entityManager->getRepository(Commentaire::class)->findBy(['article' => $article]);

                // Passer le formulaire et les commentaires à la vue
                return $this->render('article/detail.html.twig', [
                    'article' => $article,
                    'commentaires' => $commentaires,
                    'form' => $form->createView(),
                    'user' => $this->getUser(),
                    'user_id' => $user->getId(),
               
                ]);
            }
        }

        // Récupérer les commentaires existants pour cet article
        $commentaires = $this->entityManager->getRepository(Commentaire::class)->findBy(['article' => $article]);

        // Passer le formulaire et les commentaires à la vue
        return $this->render('article/detail.html.twig', [
            'article' => $article,
            'commentaires' => $commentaires,
            'form' => $form->createView(),
            'user' => $this->getUser(), // Passer l'utilisateur à la vue pour afficher le nom et prénom
            'user_id' => $user->getId(),     
        ]);
    }

   
  

    
    
    #[Route('/article/{id}/commentaire/new', name: 'commentaire_new')]
    public function new(Request $request, Article $article, EntityManagerInterface $entityManager): JsonResponse
    {
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);
    
        // Si c'est une requête AJAX et que le formulaire est valide
        if ($request->isXmlHttpRequest() && $form->isSubmitted() && $form->isValid()) {
            if ($this->getUser()) {
                $commentaire->setUser($this->getUser());
                $commentaire->setArticle($article);
                $commentaire->setDate(new \DateTime()); // Ajout de la date
    
                $entityManager->persist($commentaire);
                $entityManager->flush();
    
                return new JsonResponse([
                    'status' => 'success',
                    'commentaire' => [
                        'author' => $commentaire->getUser()->getUsername(),
                        'content' => $commentaire->getContenuCom(), // Correction ici
                        'createdAt' => $commentaire->getDate()->format('Y-m-d H:i:s'),
                    ]
                ]);
            } else {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Veuillez vous connecter pour ajouter un commentaire.'
                ]);
            }
        }
    
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Requête invalide.'
        ]);
    }
    
    

    #[Route('/comment/{id}/delete', name: 'comment_delete')]
    public function delete(Commentaire $comment): Response
    {
        $articleId = $comment->getArticle()->getId();
        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        // Redirection vers la page de détail de l'article après la suppression du commentaire
        return $this->redirectToRoute('article_detail', ['id' => $articleId]);
    }
   
    
    
    
    #[Route('/comment/{id}/edit', name: 'comment_edit')]
    public function edit(Request $request, Commentaire $comment): Response
    {
        // Créer un formulaire pour le commentaire existant
        $form = $this->createForm(CommentaireType::class, $comment);
    
        // Traiter la soumission du formulaire
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer les modifications dans la base de données
            $this->entityManager->flush();
    
            // Rediriger vers la page de l'article après la mise à jour du commentaire
            return $this->redirectToRoute('article_detail', ['id' => $comment->getArticle()->getId()]);
        }
    
        // Afficher le formulaire de modification
        return $this->render('commentaire/edit.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
        ]);
    }
    /**
 * @Route("/comment/{id}/delete", name="comment_delete", methods={"POST"})
 */
public function deleteComment(Comment $comment, EntityManagerInterface $entityManager)
{
    // Vérifier que l'utilisateur est admin
    if (!$this->isGranted('ROLE_ADMIN')) {
        throw $this->createAccessDeniedException();
    }

    // Supprimer le commentaire
    $entityManager->remove($comment);
    $entityManager->flush();

    $this->addFlash('success', 'Commentaire supprimé avec succès.');

    return $this->redirectToRoute('listblogs'); // Rediriger vers la liste des blogs
}

}
