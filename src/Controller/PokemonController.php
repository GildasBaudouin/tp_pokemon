<?php

namespace App\Controller;

use App\Entity\Pokemon;
use App\Form\PokemonType;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('pokemon/', name: 'pokemon_')]
class PokemonController extends AbstractController
{
    #[Route('list/{tri}', name: 'list')]
    public function list(PokemonRepository $pokemonRepository, $tri = null): Response
    {
        $pokemonList = $pokemonRepository->triPokemon($tri);
//        $pokemonList = $pokemonRepository->findBy([],['']);

//        dump($pokemonList);
        return $this->render('main/list.html.twig', [
            'pokemonList' => $pokemonList
        ]);
    }


    #[Route('details/{id}', name: 'details')]
    public function details(int $id, PokemonRepository $pokemonRepository): Response
    {
        $pokemon = $pokemonRepository->find($id);
        return $this->render('pokemon/details.html.twig', [
            'pokemon' => $pokemon
        ]);
    }

    #[Route('capture/{id}', name: 'capture')]
    public function capture(int $id, EntityManagerInterface $entityManager, PokemonRepository $pokemonRepository): Response
    {
        $pokemon = $pokemonRepository->find($id);
        $capture = $pokemon->isEstCapture();

        $capture ?  $pokemon->setEstCapture(false) :   $pokemon->setEstCapture(true);

//        if ($capture) {
//            $pokemon->setEstCapture(false);
//        } else {
//            $pokemon->setEstCapture(true);
//        }

        $entityManager->flush();
        $pokemonList = $pokemonRepository->findAll();

        return $this->redirectToRoute('pokemon_list');
    }

    #[Route('create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // étape 1 : créer une instance de Serie
        $pokemon = new Pokemon();

        // étape 2 : créer une instance de SerieType
        $pokemonForm = $this->createForm(PokemonType::class, $pokemon);

        $pokemonForm->handleRequest($request);


        // test si le formulaire a été soumis
        if ($pokemonForm->isSubmitted() && $pokemonForm->isValid()) {
            $pokemon->setEstCapture(false);

            $pokemonFile = $pokemonForm->get('image')->getData();

            if ($pokemonFile) {
                $originalFilename = pathinfo($pokemonFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pokemonFile->guessExtension();
                $pokemonFile->move($this->getParameter('brochures_directory'), $newFilename);

                $pokemon->setImage($newFilename);
            }

            $entityManager->persist($pokemon);
            $entityManager->flush();

            // ajout d'un message flash afin d'indiquer à l'utilisateur que tout
            // c'est bien passé
            $this->addFlash('success', 'pokemon added!!!');
            return $this->redirectToRoute('pokemon_details',
                ['id' => $pokemon->getId()]);
        }

        dump($request);
        return $this->render('pokemon/create.html.twig', [
            'pokemonForm' => $pokemonForm->createView()
        ]);
    }
}
