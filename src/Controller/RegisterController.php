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
use Monolog\Handler\Curl\Util;

class RegisterController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('register/home.html.twig', [
            'connected' => 'OK',
        ]);
    }
    #[Route('/user/add', name: 'app_user_add')]
    public function addUser(Request $request,
     EntityManagerInterface $em, UserRepository $repo,
     UserPasswordHasherInterface $hash, Utils $utils, Messagerie $messagerie):Response{
        //récupération des identifiant de messagerie
        $login = $this->getParameter('login');
        $mdp = $this->getParameter('mdp');
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
            //récupérer l'utilisateur
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
                //récupérer l'id
                $id = $user->getId();
                //variable pour le mail
                $objet = 'activation du compte';
                $content = '<p>Pour activer votre compte veuillez cliquer ci-dessous
                </p><a href="localhost:8000/activate/'.$id.'">Activer</a>';
                //on stocke la fonction dans une variable
                $status = $messagerie->sendEmail($login, $mdp, $objet, $content, $user->getEmail());
                return $this->render('register/index.html.twig', [
                    'formulaire' => $form->createView(),
                    'error' => 'Le compte à bien été ajouté',
                    'status' => $status,
                ]);
            }
            //si le compte existe déja
            else{
                return $this->render('register/index.html.twig', [
                    'formulaire' => $form->createView(),
                    'error' => 'Les informations sont incorrectes',
                    'status' => '',
                ]);
            }
        }
        //affichage de base
        return $this->render('register/index.html.twig', [
            'formulaire' => $form->createView(),
            'error' => '',
            'status' => '',
        ]);
    }
    //fonction pour activer son compte depuis le mail
    #[Route('/activate/{id}', name: 'app_register_activate')]
    public function activateUser($id, UserRepository $repo, 
    EntityManagerInterface $em, Utils $utils): Response
    {
        //nettoyage du paramètre de la route
        $id = $utils->cleanInput($id);
        //vérifier si le compte existe
        $user = $repo->find($id);
        //cas l'utilisateur existe
        if($user){
            //mettre à jour le status (objet users)
            $user->setActivated(true);
            //persister les données
            $em->persist($user);
            //sauvegarder le changement
            $em->flush();
            //afficher le message
            return $this->render('register/activate.html.twig', [
                'error' => 'le compte à été activé',
            ]);
        }
        //si il existe on met à jour 
        else{
            //afficher le message
            return $this->render('register/index.html.twig', [
                'error' => 'Le compte n\'existe pas',
            ]);
        }
    }
    //fonction qui envoi le mail d'activation
    #[Route('/sendMail/activate/{id}', name:'app_send_activate')]
    public function sendMailActivate(Utils $utils, 
    Messagerie $messagerie, UserRepository $repo,$id):Response{
        //nettoyage de l'id
        $id = $utils->cleanInput($id);
        //récupération des identifiant de messagerie
        $login = $this->getParameter('login');
        $mdp = $this->getParameter('mdp');
        //variable qui récupére l'utilisateur
        $user = $repo->find($id);
        if($user){
            $objet = 'activation du compte';
            $content = '<p>Pour activer votre compte veuillez cliquer ci-dessous
            </p><a href="localhost:8000/activate/'.$id.'">Activer</a>';
            //on stocke la fonction dans une variable
            $status = $messagerie->sendEmail($login, $mdp, $objet, $content, $user->getEmail());
            return new Response($status, 200, []);
        }
        else{
            return new Response('Le compte n\'existe pas', 200, []);
        }
    }
}
