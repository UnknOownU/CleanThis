<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Operation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

// src/Controller/Admin/DashboardController.php

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
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $url = $this->adminUrlGenerator
                ->setController(OperationCrudController::class) // Contrôleur d'opération pour l'admin
                ->generateUrl();

            return $this->redirect($url);
        } elseif ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $url = $this->adminUrlGenerator
                ->setController(OperationCrudController::class)
                ->generateUrl();

            return $this->redirect($url);
        } else {
            throw new AccessDeniedException('You do not have access to this section.');
        }
    }


    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addCssFile('css/Sidebar.css');
    }
    
    public function configureMenuItems(): iterable
    {
        if ($this->isGranted('ROLE_SENIOR')) {
            yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
            yield MenuItem::linkToCrud('Nettoyage', 'fa fa-broom', Operation::class);
            yield MenuItem::linkToRoute('Historique', 'fa fa-history', 'history_route');
            yield MenuItem::linkToCrud('Profil', 'fa fa-user', User::class);
            yield MenuItem::linkToRoute('Statistiques', 'fa fa-chart-line', 'statistics_route');
            

            yield MenuItem::section('Support');
            yield MenuItem::linkToRoute('Paramètres', 'fa fa-cogs', 'settings_route');
            yield MenuItem::linkToRoute('Besoin D’aide ?', 'fa fa-question-circle', 'help_route');
            yield MenuItem::linkToRoute('Chat', 'fa fa-comments', 'chat_route');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
            yield MenuItem::linkToCrud('Nettoyage', 'fa fa-broom', Operation::class);
            yield MenuItem::linkToRoute('Historique', 'fa fa-history', 'history_route');
            yield MenuItem::linkToCrud('Profil', 'fa fa-user', User::class);
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
        ->setTitle('<img src="images/cleanThis.png" class="img-fluid d-flex" style="max-width:220px; width:300%; padding-right:40px">');
    }

}