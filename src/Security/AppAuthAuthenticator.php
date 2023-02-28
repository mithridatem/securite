<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Repository\UserRepository;

class AppAuthAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;
    //attribut pour stocker le UserRepository
    private $repo;
    public const LOGIN_ROUTE = 'app_login';

    //on passe en paramètre le UserRepository dans le constructeur
    public function __construct(private UrlGeneratorInterface $urlGenerator,
    UserRepository $repo)
    {
        //instanciation du UserRepository
        $this->repo = $repo;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
        //récupération de l'utilisateur
        $email = $request->request->get('email', '');
        $recup = $this->repo->findBy(['email'=> $email]);
        //test si l'utilisateur est connecté
        if($recup){
            //test si le compte n'est pas activé
            if(!$recup[0]->isActivated()){
                //récupération de l'id de l'utilisateur
                $id = $recup[0]->getId();
                //redirection vers la fonction d'activation
                return new RedirectResponse($this->urlGenerator->generate('app_send_activate', ['id'=> $id]));
            }
            //test si le compte est activé
            else{
                //redirection vers l'acceuil
                return new RedirectResponse($this->urlGenerator->generate('app_home'));
            }
        }
        
        // return new RedirectResponse($this->urlGenerator->generate('some_route'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
