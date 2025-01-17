<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Dog;

#[AsCommand(
    name: 'app:import-dog',
    description: 'Importe des données depuis une API externe vers l\'API Platform',
)]
class ImportDogCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private const BATCH_SIZE = 20;  // Nombre d'images à persister avant d'effectuer un flush

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    #[AsCommand(
        name: 'app:import-dog',
        description: 'Importe des données depuis une API externe vers l\'API Platform',
    )]

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Début de l\'importation des races et images...');
    
        // URL de l'API source pour récupérer les races
        $urlBreeds = 'https://dog.ceo/api/breeds/list/all';
    
        // Initialisation du client HTTP
        $httpClient = HttpClient::create();
    
        try {
            // Étape 1 : Récupérer les races et sous-races
            $response = $httpClient->request('GET', $urlBreeds);
            $data = $response->toArray();
    
            if (!isset($data['message']) || !is_array($data['message'])) {
                $output->writeln('Format de réponse inattendu.');
                return Command::FAILURE;
            }
    
            // Parcourir les races et sous-races
            foreach ($data['message'] as $breed => $subBreeds) {
                if (empty($subBreeds)) {
                    // Cas où il n'y a pas de sous-race
                    $this->processBreed($httpClient, $breed, null, $output);
                } else {
                    // Cas avec sous-races
                    foreach ($subBreeds as $subBreed) {
                        $this->processBreed($httpClient, $breed, $subBreed, $output);
                    }
                }
            }
    
            $this->entityManager->flush();
            $output->writeln('Importation terminée avec succès.');
    
            return Command::SUCCESS;
        } catch (TransportExceptionInterface $e) {
            $output->writeln('Erreur lors de l\'appel API : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Récupère les images pour une race (et sous-race si présente) et persiste l'entité Dog.
     */
    private function processBreed($httpClient, string $breed, ?string $subBreed, OutputInterface $output)
    {
        $breedPath = $breed;
        $urlImages = "https://dog.ceo/api/breed/$breedPath/images";
        
        try {
            // Récupérer les images
            $response = $httpClient->request('GET', $urlImages);
            $imagesData = $response->toArray();
        
            if (!isset($imagesData['message']) || !is_array($imagesData['message'])) {
                $output->writeln("Aucune image trouvée pour $breedPath.");
                return;
            }
    
            // Nombre d'images traitées
            $count = 0;
            foreach ($imagesData['message'] as $imageUrl) {
                // Vérifier si l'image existe déjà dans la base de données
                $existingDog = $this->entityManager->getRepository(Dog::class)->findOneBy(['image' => $imageUrl]);
        
                if ($existingDog) {
                    // Si l'image existe déjà, on passe à l'image suivante
                    continue;
                }
    
                $dog = new Dog();
                $dog->setBreed($breed);
                $dog->setSubBreed($subBreed);
                $dog->setImage($imageUrl);
        
                $this->entityManager->persist($dog);
                $count++;
        
                // Si le batch atteint la taille définie, on effectue un flush et un clear pour libérer la mémoire
                if ($count % self::BATCH_SIZE === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();  // Libère la mémoire des entités persistées
                    $output->writeln("Flushing et nettoyage après $count images.");
                }
            }
    
            // Si des entités restantes sont persistées (moins de BATCH_SIZE), on effectue un flush final
            if ($count % self::BATCH_SIZE !== 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $output->writeln("Flush final après $count images.");
            }
    
        } catch (TransportExceptionInterface $e) {
            $output->writeln("Erreur lors de la récupération des images pour $breedPath : " . $e->getMessage());
        }
    }
     
}
