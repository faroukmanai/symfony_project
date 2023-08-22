<?php

namespace App\Controller;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\IngredientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class IngredientController extends AbstractController
{
    /**
     * THIS FUNCTION DISPLAY ALL THE INGREDIENTS
     *
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/ingredient', name: 'app_ingredient', methods: ['GET'])]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $ingredients = $paginator->paginate(
        $repository->findAll() , 
        $request->query->getInt('page', 1), 
        10 
    );
        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }
}
