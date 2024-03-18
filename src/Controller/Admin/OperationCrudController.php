<?php

namespace App\Controller\Admin;

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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Vich\UploaderBundle\Form\Type\VichImageType;

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
            ->overrideTemplate('crud/new', 'user/new.html.twig')
            ->overrideTemplate('crud/edit', 'user/edit.html.twig')
            ->setSearchFields(null);
            $statusFilter = $this->getContext()->getRequest()->query->get('status');
            if ($statusFilter) {
                $crud->setDefaultSort(['status' => $statusFilter]);
            }
    }

    public function createEntity(string $entityFqcn) {
        $operation = new Operation();
        $operation->setCustomer($this->getUser());
        $operation->setCreatedAt(new DateTimeImmutable());
        $operation->setSalarie($this->getUser());
        return $operation;
    }
    
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        if ($entityInstance instanceof Operation) {
            // Vous pouvez ajuster cette logique pour définir le prix en fonction de la valeur du champ 'type'
            $this->setOperationPrice($entityInstance);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }
    
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        if ($entityInstance instanceof Operation) {
            // Même logique pour ajuster le prix lors de la mise à jour
            $this->setOperationPrice($entityInstance);
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
    
    private function setOperationPrice(Operation $operation) {
        // Assurez-vous que le type est bien défini
        switch ($operation->getType()) {
            case 'Little':
                $operation->setPrice(2500);
                break;
            case 'Medium':
                $operation->setPrice(5000);
                break;
            case 'Big':
                $operation->setPrice(750000);
                break;
            case 'Custom':
                // Implémentez ici votre logique pour un prix personnalisé
                break;
        }
    }

    public function configureFields(string $pageName): iterable {
        return [
            FormField::addTab('Mission'),
            DateTimeField::new('created_at', 'Créé le')->hideOnForm(),
            FormField::addColumn('col-lg-8 col-xl-3'),
            IdField::new('id', 'Nº')->hideOnForm(),
            AssociationField::new('customer', 'Client')->hideOnForm(),
            TextField::new('name', 'Intitulé de l’opération')
            ->setLabel('Mission'),
            TextField::new('attachmentFile')->setFormType(VichImageType::class)->onlyWhenCreating(),
            ImageField::new('attachment')->setBasePath('/images/products')->onlyOnIndex(),
            ChoiceField::new('type')
            ->setChoices([
                'Petite' => 'Little',
                'Moyenne' => 'Medium',
                'Grande' => 'Big',
                'Personnalisée' => 'Custom',
            ]),
        MoneyField::new('price', 'Prix')
            ->setCurrency('EUR')
            ->hideOnForm(), // Cacher le champ prix dans le formulaire
            FormField::addColumn('col-lg-4 col-xl-4'),
            DateTimeField::new('rdv_at', 'Date de RDV'),
            FormField::addColumn('col-lg-3 col-xl-6'),
            TextEditorField::new('description', 'Description')
            ->hideOnForm(),
            TextareaField::new('description', 'Description')
            ->renderAsHtml()
            ->hideOnIndex(),
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
        ];
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->security->getUser();
        $statusFilter = $this->getContext()->getRequest()->query->get('status');
    
        if ($statusFilter) {
            $qb->andWhere('entity.status = :status')->setParameter('status', $statusFilter);
        }
    
        if ($this->isGranted('ROLE_CUSTOMER')) {
            // Restreindre les opérations aux celles du client connecté
            $qb->andWhere('entity.customer = :currentUser')
               ->setParameter('currentUser', $user);
        } elseif ($this->isGranted('ROLE_ADMIN')) {
            // Laisser l'administrateur voir toutes les opérations
        } else {
            // Restreindre les utilisateurs qui ne sont pas administrateurs.
            $qb->andWhere('entity.status = :statusPending OR (entity.status = :statusAccepted AND entity.salarie = :user)')
               ->setParameter('statusPending', 'En attente de Validation')
               ->setParameter('statusAccepted', 'En cours')
               ->setParameter('user', $user);
        }
    
        return $qb;
    }
    
    
    public function configureActions(Actions $actions): Actions {
        $acceptAction = Action::new('accept', 'Accepter', 'fa fa-check')
            ->displayIf(function (Operation $operation) {
                return ($this->isGranted('ROLE_ADMIN') || 
                $this->isGranted('ROLE_SENIOR') || 
                $this->isGranted('ROLE_APPRENTI')) 
                && $operation->getStatus() === 'En attente de Validation';
            })
            ->linkToCrudAction('acceptOperation');
        $declineAction = Action::new('decline', 'Refuser', 'fa fa-times')
            ->displayIf(function (Operation $operation) {
                return ($this->isGranted('ROLE_ADMIN') || 
                $this->isGranted('ROLE_SENIOR') || 
                $this->isGranted('ROLE_APPRENTI')) 
                && $operation->getStatus() === 'En attente de Validation';
            })
            ->linkToCrudAction('declineOperation');
        return $actions
            ->add(Crud::PAGE_INDEX, $acceptAction)
            ->add(Crud::PAGE_INDEX, $declineAction);
    }
    
    /**
     * Méthode personnalisée pour l'action "Accepter".
     */
    public function acceptOperation(AdminContext $context, EntityManagerInterface $entityManager): Response {
        $operation = $context->getEntity()->getInstance();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }
        // Logique pour accepter l'opération
        $operation->setStatus('En cours');
        $operation->setSalarie($this->security->getUser());
        $entityManager->flush();
        $this->addFlash('success', 'La mission a été acceptée et est maintenant "En cours".');
        return new Response('<script>window.location.reload();</script>');
    }
    
    public function declineOperation(AdminContext $context, EntityManagerInterface $entityManager): Response {
        $operation = $context->getEntity()->getInstance();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }
        // Logique pour refuser l'opération
        $operation->setStatus('Refusée');
        $entityManager->flush();
        $this->addFlash('error', 'La mission a été refusée.');
        // Utilisez l'URL de referrer, ou redirigez vers une route par défaut si aucun referrer n'est disponible
        $referrerUrl = $context->getReferrer() ?: $this->adminUrlGenerator->setDashboard()->generateUrl();
        return $this->redirect($referrerUrl);
    }
}
