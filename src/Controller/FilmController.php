<?php

namespace App\Controller;

use App\Entity\Film;
use App\Form\FilmType;
use App\Repository\FilmRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted as ConfigurationIsGranted;

#[Route('/film')]
#[ConfigurationIsGranted("ROLE_ADMIN")]
class FilmController extends AbstractController
{
    #[Route('/', name: 'app_film_index', methods: ['GET', "POST"])]
    public function index(FilmRepository $filmRepository, PaginatorInterface $paginator, Request $request): Response
    {
        //On récupére la variable de form
        if (!empty($_POST['search'])) {
            //J'execute la requete avec le parametre dde recherche
            $donnees = $filmRepository->findByExampleField($_POST['search']);
        } else {
            //j'execute la requete sans parametre de recherche 
            $donnees = $filmRepository->findBy(array(), array('id' => 'desc'));
        }

        $pagination = $paginator->paginate(
            $donnees,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('film/index.html.twig', [
            'films' => $pagination,
        ]);
    }


    //---------------------------NEW-------------------------------------

    #[Route('/new', name: 'app_film_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FilmRepository $filmRepository): Response
    {
        $film = new Film();
        $form = $this->createForm(FilmType::class, $film);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('Image')->getData();

            if ($file != null) {
                // Définis le dossier de destionation
                $path = '/';
                // Je définis le nom final de l'image
                $filename = uniqid() . '.' . $file->guessExtension();
                // Upload 
                $file->move($this->getParameter('films_directory') . $path, $filename);
            }
            // stockage de l'image dans la base de données
            $film->setImage($filename);

            $filmRepository->save($film, true);

            return $this->redirectToRoute('app_film_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('film/new.html.twig', [
            'film' => $film,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_film_show', methods: ['GET'])]
    public function show(Film $film): Response
    {
        return $this->render('film/show.html.twig', [
            'film' => $film,
        ]);
    }

    //---------------------------EDIT-------------------------------------

    #[Route('/{id}/edit', name: 'app_film_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Film $film, FilmRepository $filmRepository): Response
    {
        $imageExist = $film->getImage();

        $form = $this->createForm(FilmType::class, $film);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('Image')->getData();

            if ($file != null) {

                //supprimer l'image existante
                $lienImageExist = '../public/film/' . $imageExist;

                if (file_exists($lienImageExist)) {
                    unlink($lienImageExist);
                };

                // Définis le dossier de destionation
                $path = '/';
                // Je définis le nom final de l'image
                $filename = uniqid() . '.' . $file->guessExtension();
                // Upload 
                $file->move($this->getParameter('films_directory') . $path, $filename);
            }
            // stockage de l'image dans la base de données
            $film->setImage($filename);

            $filmRepository->save($film, true);

            return $this->redirectToRoute('app_film_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('film/edit.html.twig', [
            'film' => $film,
            'form' => $form,
        ]);
    }

    //---------------------------DELETE-------------------------------------

    #[Route('/{id}', name: 'app_film_delete', methods: ['POST'])]
    public function delete(Request $request, Film $film, FilmRepository $filmRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $film->getId(), $request->request->get('_token'))) {
            $filmRepository->remove($film, true);
        }

        return $this->redirectToRoute('app_film_index', [], Response::HTTP_SEE_OTHER);
    }
}
