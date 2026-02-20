<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Visit;
use App\Repository\VisitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class VisitService
{
    private const BOT_PATTERNS = [
        'bot',
        'crawler',
        'spider',
        'scraper',
        'curl',
        'wget',
        'python-requests',
        'googlebot',
        'bingbot',
        'slurp',
        'duckduckbot',
        'baiduspider',
        'yandexbot',
        'facebookexternalhit',
        'twitterbot',
        'rogerbot',
        'linkedinbot',
        'embedly',
        'quora link preview',
        'showyoubot',
        'outbrain',
        'pinterest',
        'slackbot',
        'vkShare',
        'W3C_Validator',
        'whatsapp',
        'flipboard',
        'tumblr',
        'bitlybot',
        'SkypeUriPreview',
        'nuzzel',
        'Discordbot',
        'Google Page Speed',
        'Qwantify',
        'pinterestbot',
        'bitrix link preview',
        'XING-contenttabreceiver',
        'Chrome-Lighthouse',
        'Applebot',
        'ia_archiver',
        'archive.org_bot',
        'BingPreview',
        'Yahoo! Slurp',
    ];

    private const ADMIN_ROLES = ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_EDITOR'];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private VisitRepository $visitRepository,
        private RequestStack $requestStack,
        private TokenStorageInterface $tokenStorage
    ) {}

    /**
     * Vérifie si la requête doit être trackée
     */
    public function shouldTrack(Request $request): bool
    {
        // Ne pas tracker les requêtes vers l'admin
        if (str_starts_with($request->getPathInfo(), '/admin')) {
            return false;
        }

        // Ne pas tracker les assets (CSS, JS, images, etc.)
        $path = $request->getPathInfo();
        $assetExtensions = ['.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.eot'];
        foreach ($assetExtensions as $ext) {
            if (str_ends_with(strtolower($path), $ext)) {
                return false;
            }
        }

        // Ne pas tracker les requêtes AJAX
        if ($request->isXmlHttpRequest()) {
            return false;
        }

        // Ne pas tracker les requêtes non-GET (sauf si nécessaire)
        if (!in_array($request->getMethod(), ['GET'])) {
            return false;
        }

        // Ne pas tracker les requêtes vers les routes système Symfony
        if (str_starts_with($path, '/_')) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie si le user-agent est un bot
     */
    public function isBot(?string $userAgent): bool
    {
        if (!$userAgent) {
            return false;
        }

        $userAgentLower = strtolower($userAgent);

        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($userAgentLower, strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur est un admin
     */
    public function isAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $roles = $user->getRoles();
        $rolesValues = array_values($roles);

        foreach (self::ADMIN_ROLES as $adminRole) {
            if (in_array($adminRole, $rolesValues, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enregistre une visite
     */
    public function trackVisit(Request $request): void
    {
        if (!$this->shouldTrack($request)) {
            return;
        }

        // Vérifier si la session existe, sinon la créer
        if (!$request->hasSession()) {
            $request->setSession($this->requestStack->getSession());
        }

        $session = $request->getSession();
        $sessionId = $session->getId();
        $userAgent = $request->headers->get('User-Agent');
        $ipAddress = $request->getClientIp();
        $url = $request->getUri();
        $method = $request->getMethod();

        // Vérifier si c'est un bot
        $isBot = $this->isBot($userAgent);

        // Récupérer l'utilisateur connecté
        $user = null;
        $isAdmin = false;
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $tokenUser = $token->getUser();
            if ($tokenUser instanceof User) {
                $user = $tokenUser;
                $isAdmin = $this->isAdmin($user);
            }
        }

        // Ne pas tracker les admins
        if ($isAdmin) {
            return;
        }

        // Vérifier si cette session a déjà été comptée aujourd'hui
        $today = new \DateTime('today');
        $tomorrow = (clone $today)->modify('+1 day');

        $existingVisit = $this->visitRepository->createQueryBuilder('v')
            ->where('v.sessionId = :sessionId')
            ->andWhere('v.visitedAt >= :startDate')
            ->andWhere('v.visitedAt < :endDate')
            ->andWhere('v.isBot = false')
            ->andWhere('v.isAdmin = false')
            ->setParameter('sessionId', $sessionId)
            ->setParameter('startDate', $today)
            ->setParameter('endDate', $tomorrow)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // Si une visite existe déjà pour cette session aujourd'hui, ne pas en créer une nouvelle
        // (on compte une visite par session par jour)
        if ($existingVisit) {
            return;
        }

        // Créer la visite
        try {
            $visit = new Visit();
            $visit->setSessionId($sessionId);
            $visit->setIpAddress($ipAddress);
            $visit->setUserAgent($userAgent);
            $visit->setUrl($url);
            $visit->setMethod($method);
            $visit->setIsBot($isBot);
            $visit->setIsAdmin($isAdmin);
            $visit->setUser($user);
            $visit->setVisitedAt(new \DateTime());

            $this->entityManager->persist($visit);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            // En cas d'erreur (ex: table n'existe pas), on ignore silencieusement
            // pour ne pas bloquer l'application
            // En production, vous pouvez logger l'erreur ici
        }
    }

    /**
     * Récupère les statistiques de visites
     */
    public function getStats(): array
    {
        return $this->visitRepository->getVisitStats();
    }
}
