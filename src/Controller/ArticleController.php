<?php
namespace App\Controller;
use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\FormArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface; // Import the paginator
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Form\CommentaireType;  // Assuming you have a form type for commentaires
use App\Repository\ArticleRepository;


    class ArticleController extends AbstractController
    {
        private EntityManagerInterface $entityManager;
    
        public function __construct(EntityManagerInterface $entityManager)
        {
            $this->entityManager = $entityManager;
        }
        #[Route('/article', name: 'app_article')]
        public function index(): Response
        {
            return $this->render('article/index.html.twig', [
                'controller_name' => 'ArticleController',
            ]);
        }
    
        #[Route('/blogs', name: 'blogs')]
        public function blogs(Request $request, PaginatorInterface $paginator): Response
        {
            $categorie = $request->query->get('categorie');
            
            if ($categorie) {
                // Filter articles by category
                $query = $this->entityManager
                    ->getRepository(Article::class)
                    ->createQueryBuilder('a')
                    ->where('a.categorie = :categorie')
                    ->setParameter('categorie', $categorie)
                    ->getQuery();
            } else {
                // Otherwise, get all articles
                $query = $this->entityManager
                    ->getRepository(Article::class)
                    ->createQueryBuilder('a')
                    ->getQuery();
            }
        
               // Paginate the query results 
            $pagination = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1), // Default to page 1
                5 // Items per page
            );
        
            return $this->render('article/blogs.html.twig', [
                'pagination' => $pagination,
                'selectedCategorie' => $categorie,
            ]);
        }
        
        #[Route('/blogsback', name: 'blogsback')]
        public function blogsback(Request $request, SluggerInterface $slugger): Response
        {
            // Récupérer tous les articles ou filtrer selon la catégorie
            $articles = $this->entityManager->getRepository(Article::class)->findAll();
        
            // Formulaire pour ajouter un nouvel article
            $article = new Article();
            $form = $this->createForm(FormArticleType::class, $article);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $imageFile = $form->get('image')->getData();
            
                if ($article->getImage()) {
                    $newFilename = $this->uploadImage($imageFile, $slugger);
                    if ($newFilename) {
                        $article->setImage($newFilename);
                    } else {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l’image.');
                        return $this->redirectToRoute('article_new'); // ou autre route
                    }
                } else {
                    $this->addFlash('error', 'L’image est obligatoire.');
                    return $this->redirectToRoute('article_new'); // Rediriger si l’image est absente
                }
            
                $this->entityManager->persist($article);
                $this->entityManager->flush();
            
                return $this->redirectToRoute('listarticle');
            }
            
            // Passer les articles et le formulaire à la vue
            return $this->render('article/blogsbck.html.twig', [
                'articles' => $articles,  // Assurez-vous que 'articles' est passé ici
                'form' => $form->createView(),
            ]);
        }
        
        #[Route('/article/new', name: 'article_new')]
        public function new(Request $request, SluggerInterface $slugger): Response
        {
            $article = new Article();
            $form = $this->createForm(FormArticleType::class, $article);
            $form->handleRequest($request);
        
            if ($form->isSubmitted() && $form->isValid()) {
                $imageFile = $form->get('image')->getData();
        
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
        
                    try {
                        $imageFile->move(
                            $this->getParameter('articles_directory'), // Assurez-vous que ce paramètre est bien configuré
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // Gérer l'erreur si le fichier ne peut pas être déplacé
                    }
        
                    $article->setImage($newFilename);
                }
        
                // Remplacer getDoctrine() par l'EntityManager injecté
                $this->entityManager->persist($article);
                $this->entityManager->flush();
        
                return $this->redirectToRoute('listarticle');
            }
        
            return $this->render('article/blogsback.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        
        
        #[Route('/list', name: 'listarticle')]
        public function list(Request $request, PaginatorInterface $paginator): Response
        {
            // Utilisation correcte de l'EntityManager injecté
            $query = $this->entityManager
                ->getRepository(Article::class)
                ->createQueryBuilder('a')
                ->getQuery();
        
            // Paginer les résultats de la requête
            $articles = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1), // Par défaut, page 1
                5 // Articles par page
            );
        
            return $this->render('article/listblogs.html.twig', [
                'articles' => $articles,
            ]);
        }
        
    #[Route('/delete/{id}', name: 'app_article_delete')]
    public function delete(Article $article): Response
    {
        $this->entityManager->remove($article);
        $this->entityManager->flush();
    
        return $this->redirectToRoute('listarticle');
    }
    
        #[Route('/article/edit/{id}', name: 'app_article_edit')]
        public function edit(Request $request, Article $article, SluggerInterface $slugger): Response
        {
            $form = $this->createForm(FormArticleType::class, $article);
            $form->handleRequest($request);
        
            if ($form->isSubmitted() && $form->isValid()) {
                $imageFile = $form->get('image')->getData();
        
                if ($imageFile) {
                    $newFilename = $this->uploadImage($imageFile, $slugger);
                    if ($newFilename) {
                        $article->setImage($newFilename);
                    }
                }
        
                // Utilisation de l'EntityManager injecté
                $this->entityManager->flush();
        
                return $this->redirectToRoute('listarticle');
            }
        
            return $this->render('article/edit.html.twig', [
                'form' => $form->createView(),
                'article' => $article,
            ]);
        }
        
    
        private function uploadImage($imageFile, SluggerInterface $slugger): ?string
        {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
    
            try {
                $imageFile->move(
                    $this->getParameter('articles_directory'),
                    $newFilename
                );
                return $newFilename;
            } catch (FileException $e) {
                $this->addFlash('error', 'Image upload failed: ' . $e->getMessage());
                return null;
            }
        }
     // src/Controller/ArticleController.php
     #[Route('/article/{id}', name: 'article_detail')]
     public function detail(Article $article, Request $request): Response
     {
         // Créer un nouveau commentaire
         $commentaire = new Commentaire();
     
         // Créer un formulaire pour ce commentaire
         $form = $this->createForm(CommentaireType::class, $commentaire);
     
         // Traiter la soumission du formulaire
         $form->handleRequest($request);
         if ($form->isSubmitted() && $form->isValid()) {
             if ($this->getUser()) { // Vérifier si l'utilisateur est connecté
                 $commentaire->setArticle($article);
                 $commentaire->setUser($this->getUser()); // Assurez-vous que l'utilisateur est connecté
                 $this->entityManager->persist($commentaire);
                 $this->entityManager->flush();
     
                 // Rediriger pour éviter le double envoi du formulaire
                 return $this->redirectToRoute('article_detail_with_user', ['id' => $article->getId()]);
             } else {
                 // Rediriger l'utilisateur vers la page de connexion s'il n'est pas connecté
                 return $this->redirectToRoute('app_login');
             }
         }
     
         // Récupérer les commentaires existants pour cet article
         $commentaires = $this->entityManager->getRepository(Commentaire::class)->findBy(['article' => $article]);
     
         // Passer le formulaire et les commentaires à la vue
         return $this->render('article/detail.html.twig', [
             'article' => $article,
             'commentaires' => $commentaires,
             'form' => $form->createView(),
         ]);
     }
     
        
            
    
    }
        
    