<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 *
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

/**
 * Récupère les recettes publiques depuis la base de données.
 * 
 * @param int|null $nbRecipes Le nombre maximum de recettes à récupérer. Si null ou 0, récupère toutes les recettes.
 * 
 * @return array Retourne un tableau des recettes publiques.
 */
public function findPublicRecipe(?int $nbRecipes): array
{
    // Simule un retard (probablement pour des raisons de test) de 3 secondes.
    // sleep(3);

    // Commence la construction de la requête pour récupérer les recettes.
    $queryBuilder = $this->createQueryBuilder('r')
        ->where('r.isPublic = 1')             // Filtre pour obtenir uniquement les recettes publiques.
        ->orderBy('r.createdAt', 'DESC');     // Trie les recettes par date de création en ordre décroissant.

    // Si $nbRecipes n'est ni null ni 0, définir le nombre maximum de résultats.
    if ($nbRecipes !== 0 && $nbRecipes !== null) {
        $queryBuilder->setMaxResults($nbRecipes);
    }

    // Exécute la requête et récupère le résultat.
    return $queryBuilder->getQuery()
        ->getResult();
}


}
