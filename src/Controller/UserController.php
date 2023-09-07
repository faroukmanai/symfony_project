<?php
namespace App\Controller;
// Importation des dépendances nécessaires.
use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

// La classe UserController hérite d'AbstractController, ce qui donne accès à de nombreuses méthodes utilitaires fournies par Symfony.
class UserController extends AbstractController
{
    // La méthode "edit" est liée à une route spécifique grâce à l'annotation Route.
    // Cette route est destinée à éditer les détails d'un utilisateur spécifique.
   
    /**
     * edit user function
     *
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET','POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {

        // Vérifie si l'utilisateur est connecté.
        // Si ce n'est pas le cas, redirige vers la page de connexion.
        if(!$this-> getUser()){
            return $this->redirectToRoute('security.login');
        }

        // Vérifie si l'utilisateur connecté est le même que celui que l'on tente d'éditer.
        // Si c'est le cas, redirige vers la page d'index des recettes.
        // (Cela suggère que les utilisateurs ne sont pas autorisés à éditer leurs propres détails ou que c'est un comportement non désiré dans cette application.)
        if($this->getUser() !== $user) {
            return $this->redirectToRoute('recipe.index');
        }
        
        // Crée un formulaire pour éditer les détails de l'utilisateur.
        // UserType est probablement une classe définissant la structure du formulaire.
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            if ($hasher->isPasswordValid($user, $form->getData()->getPlainPassword())){
                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Les informations de l\'utilisateur ont bien été modifiées.'
                );
                return $this->redirectToRoute('recipe.index');
            }
            
            else{
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect'
                );
            }
            
        }

        // Renvoie une vue rendue (probablement un fichier Twig) avec le formulaire en tant que variable.
        // Cela permettra à l'utilisateur de voir et de soumettre le formulaire pour éditer les détails.
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/utilisateur/edition-mot-de-passe/{id}', name:'user.edit.password', methods: ["GET", "POST"])]
    public function editPassword(User $user, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response {

        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            // Vérifie si le mot de passe actuel est correct
            if ($hasher->isPasswordValid($user, $form->getData()['plainPassword'])){
                
                // Hash le nouveau mot de passe
                $hashedPassword = $hasher->hashPassword($user, $form->getData()['newPassword']);
                
                // Met à jour le mot de passe de l'utilisateur
                $user->setPassword($hashedPassword);
                
                // Persiste et enregistre les modifications
                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Le mot de passe a été modifié');
                return $this->redirectToRoute('recipe.index');
            }else{
                $this->addFlash('warning', 'Le mot de passe renseigné est incorrect');
            }
        }

        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
