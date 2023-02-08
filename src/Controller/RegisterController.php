<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserType;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Utils;
use App\Service\Messagerie;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function index(): Response
    {
        return $this->render('register/index.html.twig', [
            'controller_name' => 'RegisterController',
        ]);
    }
    #[Route('/user/add', name: 'app_user_add')]
    public function addUser(Request $request,
     EntityManagerInterface $em, UserRepository $repo,
     UserPasswordHasherInterface $hash, Utils $utils, Messagerie $messagerie):Response{
        //import du fichier dde connexion mail
        include 'App/smtp.php';
        //variable qui stocke un nouvel utilisateur
        $user = new User();
        //variable qui stocke le formulaire
        $form = $this->createForm(UserType::class, $user);
        //récupérer le contenu du formulaire
        $form->handleRequest($request);
        //tester si le fomulaire est submit
        if($form->isSubmitted()){
            //récupération et nettoyage du mail
            $user->setEmail($utils->cleanInput($_POST['user']['email']));
            $recup = $repo->findBy(['email'=>$user->getEmail()]);
            //test des doublons
            if($recup == null){
                //variable pour récupérer le mot de passe en clair et le nettoyer
                $pass = $utils->cleanInput($_POST['user']['password']);
                //nettoyage des valeurs (nom et prénom)
                $user->setName($utils->cleanInput($_POST['user']['name']));
                $user->setFirstName($utils->cleanInput($_POST['user']['firstName']));
                //hash du mot de passe
                $pass = $hash->hashPassword($user, $pass);
                //set du password hash
                $user->setPassword($pass);
                //set du role
                $user->setRoles(['ROLE_USER']);
                //set l'activation
                $user->setActivated(false);
                //persist les données
                $em->persist($user);
                //sauvegarder en BDD
                $em->flush();
                //variable pour le mail
                $objet = 'activation du compte';
                $content = '<p>Pour activer votre compte veuillez cliquer ci-dessous
                </p><a href="localhost:8000">Activer</a>';
                $messagerie->sendEmail($login, $mdp, $objet, $content, $user->getEmail());
                return $this->render('register/index.html.twig', [
                    'formulaire' => $form->createView(),
                    'error' => 'Le compte à bien été ajouté',
                ]);
            }
            //si le compte existe déja
            else{
                return $this->render('register/index.html.twig', [
                    'formulaire' => $form->createView(),
                    'error' => 'Les informations sont incorrectes',
                ]);
            }
        }
        //affichage de base
        return $this->render('register/index.html.twig', [
            'formulaire' => $form->createView(),
            'error' => '',
        ]);
    }
}
