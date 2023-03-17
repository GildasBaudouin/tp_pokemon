<?php

namespace App\Controller;

use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
