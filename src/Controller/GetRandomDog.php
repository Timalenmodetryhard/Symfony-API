<?php

namespace App\Controller;

use App\Repository\DogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpFoundation\JsonResponse;

#[AsController]
class GetRandomDog extends AbstractController
{
    private DogRepository $dogRepository;

    public function __construct(DogRepository $dogRepository)
    {
        $this->dogRepository = $dogRepository;
    }

    public function __invoke()
    {
        // Récupérer tous les chiens
        $dogs = $this->dogRepository->findAll();

        // Vérifier si la liste est vide
        if (empty($dogs)) {
            return new JsonResponse(
                [
                    'error' => 'Aucun chien trouvé',
                    'status' => 404
                ],
                404
            );
        }

        // Sélectionner un chien au hasard
        $randomDog = $dogs[array_rand($dogs)];

        // Retourner le chien sélectionné
        return new JsonResponse($randomDog);
    }
}
