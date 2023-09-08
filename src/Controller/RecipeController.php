<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class RecipeController extends AbstractController
{
    /**
     * display all recipes
     *
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */

    #[Route('/recette', name: 'recipe.index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
            $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]) , 
            $request->query->getInt('page', 1), 
            10 
            );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes
        ]);
    }

    #[Route('/recette/publique', name: 'recipe.index.public', methods: ['GET'])]
    public function indexPublic(RecipeRepository $repository, PaginatorInterface $paginator, Request $request) : Response {

        $recipes = $paginator->paginate(
            $repository->findPublicRecipe(null),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/recipe/indexPublic.html.twig', [
            'recipes' => $recipes  
          ]);
    }

    #[Security("is_granted('ROLE_USER') and recipe.isIsPublic() === true")]
    #[Route('/recette/{id}', name: 'recipe.show', methods: ['GET'])]
    public function show(Recipe $recipe) : Response {
        return $this->render('pages/recipe/show.html.twig', [
          'recipe' => $recipe  
        ]);
    }

    /**
     * Create new recipe
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/creation', name: 'recipe.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        EntityManagerInterface $manager) 
        : Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été crée avec succès'
            );
            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
         
    }
    #[Route('/recette/edition/{id}', name:'recipe.edit', methods:['GET','POST'])]
    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
    public function edit( 
        Recipe $recipe,
        Request $request,
        EntityManagerInterface $manager
        ) : Response {

        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();

            $manager->persist($recipe);
            $manager->flush();

        
            $this->addFlash(
                'success',
                'Votre recette a été modifié avec succès'
            );
            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);     
    }

    #[Route('/recette/suppression/{id}', name:'recipe.delete', methods:['GET'])]
    public function delete(EntityManagerInterface $manager, Recipe $recipe) : Response 
    {   
        $manager->remove($recipe);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre recette a été supprimé avec succès'
        );

        return $this->redirectToRoute('recipe.index');
    }
}
