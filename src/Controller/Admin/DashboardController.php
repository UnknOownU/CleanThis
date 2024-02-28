<?php

namespace App\Controller\Admin;

use App\Entity\Operation;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class DashboardController extends AbstractDashboardController
{
    private $authorizationChecker;
    private $adminUrlGenerator;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response {
        // Utilisez les noms de contrôleurs corrects et vérifiez les rôles
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $url = $this->adminUrlGenerator
                ->setController(OperationCrudController::class) // Contrôleur d'opération pour l'admin
                ->generateUrl();

            return $this->redirect($url);
        } elseif ($this->authorizationChecker->isGranted('ROLE_USER')) {
            $url = $this->adminUrlGenerator
                ->setController(OperationCrudController::class)
                ->generateUrl();

            return $this->redirect($url);
        } else {
            throw new AccessDeniedException('You do not have access to this section.');
        }
    }


 
    public function configureMenuItems(): iterable
    {
        if ($this->isGranted('ROLE_USER')) {
            yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
            yield MenuItem::linkToCrud('Nettoyage', 'fa fa-broom', Operation::class);
            yield MenuItem::linkToRoute('Historique', 'fa fa-history', 'history_route');
            yield MenuItem::linkToRoute('Profil', 'fa fa-user', 'profile_route');
            yield MenuItem::linkToRoute('Statistiques', 'fa fa-chart-line', 'statistics_route');

            yield MenuItem::section('Support');
            yield MenuItem::linkToRoute('Paramètres', 'fa fa-cogs', 'settings_route');
            yield MenuItem::linkToRoute('Besoin D’aide ?', 'fa fa-question-circle', 'help_route');
            yield MenuItem::linkToRoute('Chat', 'fa fa-comments', 'chat_route');
        }


    }
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()

            ->setTitle('Cleanthis');

    }
}