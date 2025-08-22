<?php

namespace App\Security;

use App\Entity\Menu;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;
    private EntityManagerInterface $entityManager;

    public function __construct(RouterInterface $router, EntityManagerInterface $entityManager)
    {
        $this->router = $router;
        $this->entityManager = $entityManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        /** @var User $user */
        $user = $token->getUser();

        // Si l'utilisateur est un admin, on le redirige vers le dashboard
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('admin'));
        }

        // Si l'utilisateur a accès à la section Investisseur
        if (in_array('ROLE_INVESTISSEUR', $user->getRoles(), true)) {
            $firstMenu = $this->entityManager->getRepository(Menu::class)->findOneBy(
                ['section' => 'INVESTISSEUR'],
                ['menuorder' => 'ASC']
            );

            if ($firstMenu) {
                // Si le premier menu a des enfants, on redirige vers le premier enfant
                $targetMenu = $firstMenu->getChildren()->isEmpty() ? $firstMenu : $firstMenu->getChildren()->first();
                $route = $targetMenu->getParent() ? 'app_investisseur_child_page' : 'app_investisseur_page';
                $params = ['slug' => $targetMenu->getSlug()];
                if ($targetMenu->getParent()) {
                    $params = [
                        'parentSlug' => $targetMenu->getParent()->getSlug(),
                        'childSlug' => $targetMenu->getSlug()
                    ];
                }
                return new RedirectResponse($this->router->generate($route, $params));
            }
        }

        // Redirection par défaut vers le premier menu de la section HOME
        $firstHomeMenu = $this->entityManager->getRepository(Menu::class)->findOneBy(
            ['section' => 'HOME', 'isActive' => true],
            ['menuorder' => 'ASC']
        );

        if ($firstHomeMenu) {
            return new RedirectResponse($this->router->generate('app_home_page', ['slug' => $firstHomeMenu->getSlug()]));
        }

        // Fallback si aucun menu HOME n'est trouvé
        return new RedirectResponse($this->router->generate('app_home_page', ['slug' => 'accueil']));
    }
}
