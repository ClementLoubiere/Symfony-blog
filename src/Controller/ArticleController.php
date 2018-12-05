<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @package App\Controller
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/{id}")
     */
    public function index(Article $article, Request $request, $id)
    {
        /*
         *
         * Lister les commentaires en dessous, avec nom utilisateur, date de publication et le contenu du message
         */

        $user = $this->getUser();
        $date = new \DateTime();
        $em = $this->getDoctrine()->getManager();

        $comment = new Comment();

        $comment
            ->setDatePublication($date)
            ->setUser($user)
            ->setArticle($article);

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted()){
            if($form->isValid()){

                $em->persist($comment);
                $em->flush();

                $this->addFlash(
                    'success',
                    'Votre commentaire est enregistré'
                );


                // return $this->redirectToRoute('app_article_index', ['id' => $id]);
                // pour une redirection vers la page sur laquelle on est sans être en POST de nouveau
                return $this->redirectToRoute($request->get('_route'), ['id' => $id]);

            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }

        $repository = $em->getRepository(Comment::class);
        $comments = $repository->findBy(['article' => $article ], ['datePublication' => 'desc'],10);


        return $this->render('article/index.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
            'comments' => $comments
        ]);
    }

}
