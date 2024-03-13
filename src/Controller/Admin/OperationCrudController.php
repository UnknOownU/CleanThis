<?php

namespace App\Controller\Admin;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Operation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OperationCrudController extends AbstractCrudController {
    private Security $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

    public static function getEntityFqcn(): string {
        return Operation::class;
    }

    public function configureCrud(Crud $crud): Crud {
        return $crud
            // Chemins des templates pour les actions CRUD
           ->overrideTemplate('crud/new', 'user/new.html.twig')
           ->overrideTemplate('crud/edit', 'user/edit.html.twig');
    }

    public function configureFields(string $pageName): iterable {
        return [
            FormField::addTab('Mission'),
            DateTimeField::new('created_at', 'Créé le')->hideOnForm(),

            FormField::addColumn('col-lg-8 col-xl-3'),
            IdField::new('id', 'Nº de commande')->hideOnForm(),
            AssociationField::new('customer', 'Client')->hideOnForm(),
            TextField::new('name', 'Intitulé de l’opération')
            ->setLabel('Mission'),
            ChoiceField::new('type')->setChoices([
                'Little' => 'Petite',
                'Medium' => 'Moyenne',
                'Big' => 'Grande',
                'Custom' => 'Custom',
            ])->hideOnIndex(),
            TextField::new('type')->hideOnForm(),
            MoneyField::new('price', 'Prix')->setCurrency('EUR'),
            FormField::addColumn('col-lg-4 col-xl-4'),
            DateTimeField::new('rdv_at', 'Date de RDV'),
            FormField::addColumn('col-lg-3 col-xl-6'),
            TextEditorField::new('description', 'Description'),
            ChoiceField::new('status')->setChoices([
                'En attente' => 'En attente de Validation',
                'En cours' => 'En cours',
                'Terminée' => 'Terminée',
            ]),

            TextField::new('street_ope', 'Rue')
            ->setFormTypeOption('attr', ['class' => 'adresse-autocomplete']),
            TextField::new('zipcode_ope', 'Code Postal')
            ->setFormTypeOption('attr', ['class' => 'zipcode_ope']),
            TextField::new('city_ope', 'Ville')
            ->setFormTypeOption('attr', ['class' => 'city_ope']),
            DateTimeField::new('finished_at', 'Terminé le')->hideOnForm(),

            AssociationField::new('customer'),       
            AssociationField::new('salarie'), 
        ];
    }

    public function createEntity(string $entityFqcn) {
        $operation = new Operation();
        $operation->setCustomer($this->getUser());
        $operation->setCreatedAt(new DateTimeImmutable());
        return $operation;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        if ($entityInstance instanceof Operation) {
            $entityInstance->setCustomer($this->getUser());
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureActions(Actions $actions): Actions {
        $acceptAction = Action::new('accept', 'Accepter', 'fa fa-check')
            ->displayIf(function (Operation $operation) {
                return $operation->getStatus() === 'En attente de Validation' && 
                       ($this->isGranted('ROLE_SENIOR') || $this->isGranted('ROLE_APPRENTI'));
            })
            ->linkToCrudAction('acceptOperation');

        $declineAction = Action::new('decline', 'Refuser', 'fa fa-times')
            ->displayIf(function (Operation $operation) {
                return $operation->getStatus() === 'En attente de Validation' && 
                       ($this->isGranted('ROLE_SENIOR') || $this->isGranted('ROLE_APPRENTI'));
            })
            ->linkToCrudAction('declineOperation');

        return $actions
            ->add(Crud::PAGE_INDEX, $acceptAction)
            ->add(Crud::PAGE_INDEX, $declineAction);
    }

    /**
     * @Route("/operation/{id}/accept", name="operation_accept")
     */
    public function acceptOperation(Operation $operation, EntityManagerInterface $entityManager): Response {
        if ($operation->getStatus() !== 'En attente de Validation') {
            $this->addFlash('error', 'Cette opération ne peut pas être acceptée.');
            return new Response('<script>window.location.reload();</script>');
        }

        $operation->setStatus('En cours');
        $operation->setSalarie($this->security->getUser());
        $entityManager->flush();

        $this->addFlash('success', 'Opération acceptée avec succès.');
        return new Response('<script>window.location.reload();</script>');
    }

    /**
     * @Route("/operation/{id}/decline", name="operation_decline")
     */
    public function declineOperation(Operation $operation, EntityManagerInterface $entityManager): Response {
        if ($operation->getStatus() !== 'En attente de Validation') {
            $this->addFlash('error', 'Cette opération ne peut pas être refusée.');
            return new Response('<script>window.location.reload();</script>');
        }

        $operation->setStatus('Refusée');
        $entityManager->flush();

        $this->addFlash('error', 'Opération refusée.');
        return new Response('<script>window.location.reload();</script>');
    }
}
