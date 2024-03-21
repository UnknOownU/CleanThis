<?php

namespace App\Controller\Admin;

use DateTimeImmutable;
use App\Entity\Operation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Vich\UploaderBundle\Form\Type\VichImageType;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
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
        $operation->setSalarie(null);
        return $operation;
    }
    
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        if ($entityInstance instanceof Operation) {
            $this->setOperationPrice($entityInstance);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }
    
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        if ($entityInstance instanceof Operation) {
            $this->setOperationPrice($entityInstance);
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
    
    private function setOperationPrice(Operation $operation) {
        switch ($operation->getType()) {
            case 'Little':
                $operation->setPrice(100000);
                break;
            case 'Medium':
                $operation->setPrice(250000);
                break;
            case 'Big':
                $operation->setPrice(500000);
                break;
            case 'Custom':
                break;
        }
    }

    public function configureFields(string $pageName): iterable {
        //Formulaire creation operation pour client
        if ($this->isGranted('ROLE_CUSTOMER')) {
            return [
                FormField::addTab('Mission'),
                DateTimeField::new('created_at', 'Créé le')
                    ->hideOnForm(),
                FormField::addColumn('col-lg-8 col-xl-3'),
                IdField::new('id', 'Nº')
                    ->hideOnForm(),
                AssociationField::new('customer', 'Client')
                    ->hideOnForm()
                    ->hideOnIndex(),
                AssociationField::new('salarie', 'Salarié')
                    ->hideOnForm(),
                TextField::new('name', 'Intitulé de l’opération')
                    ->setLabel('Mission'),
                TextField::new('attachmentFile')
                    ->setLabel('Photo')
                    ->setFormType(VichImageType::class)
                    ->onlyWhenCreating(),
                ImageField::new('attachment')
                    ->setLabel('Photo')
                    ->setBasePath('/images/products')
                    ->onlyOnIndex(),
                ChoiceField::new('type')
                    ->setChoices([
                        'Petite - 1000€' => 'Little',
                        'Moyenne - 2500€' => 'Medium',
                        'Grande - 5000€' => 'Big',
                        'Personnalisée' => 'Custom',
                ]),
                MoneyField::new('price', 'Prix')
                    ->setCurrency('EUR')
                    ->hideOnForm(),
                FormField::addColumn('col-lg-4 col-xl-4'),
                DateTimeField::new('rdv_at', 'Date de RDV'),
                FormField::addColumn('col-lg-3 col-xl-6'),
                TextEditorField::new('description', 'Description')
                    ->hideOnForm(),
                TextareaField::new('description', 'Description')
                    ->renderAsHtml()
                    ->hideOnIndex(),
                TextField::new('street_ope', 'Rue')
                    ->setFormTypeOption('attr', ['class' => 'adresse-autocomplete']),
                TextField::new('zipcode_ope', 'Code Postal')
                    ->setFormTypeOption('attr', ['class' => 'zipcode_ope']),
                TextField::new('city_ope', 'Ville')
                    ->setFormTypeOption('attr', ['class' => 'city_ope']),
                DateTimeField::new('finished_at', 'Terminé le')
                    ->hideOnForm() 
                ];
        } else { 
        //Formulaire creation operation pour salariés
            return [
                FormField::addTab('Mission'),
                DateTimeField::new('created_at', 'Créé le')
                    ->hideOnForm(),
                FormField::addColumn('col-lg-8 col-xl-3'),
                IdField::new('id', 'Nº')
                    ->hideOnForm(),
                AssociationField::new('customer', 'Client')
                    ->hideOnForm(),
                TextField::new('name', 'Intitulé de l’opération')
                    ->setLabel('Mission'),
                TextField::new('attachmentFile')
                    ->setLabel('Photo')
                    ->setFormType(VichImageType::class)
                    ->onlyWhenCreating(),
                ImageField::new('attachment')
                    ->setLabel('Photo')
                    ->setBasePath('/images/products')
                    ->onlyOnIndex(),
                ChoiceField::new('type')
                    ->setChoices([
                        'Petite' => 'Little',
                        'Moyenne' => 'Medium',
                        'Grande' => 'Big',
                        'Personnalisée' => 'Custom', //TODO:
                ]),
                MoneyField::new('price', 'Prix')
                    ->setCurrency('EUR')
                    ->setLabel('Prix'),
                FormField::addColumn('col-lg-4 col-xl-4'),
                DateTimeField::new('rdv_at', 'Date de RDV'),
                FormField::addColumn('col-lg-3 col-xl-6'),
                TextEditorField::new('description', 'Description')
                    ->hideOnForm(),
                TextareaField::new('description', 'Description')
                    ->renderAsHtml()
                    ->hideOnIndex(),
                ChoiceField::new('status')
                    ->setChoices([
                        'En attente' => 'En attente de Validation',
                        'En cours' => 'En cours',
                        'Terminée' => 'Terminée',
                        'Refusée' => 'Refusée',
                    ]),
                TextField::new('street_ope', 'Rue')
                    ->setFormTypeOption('attr', ['class' => 'adresse-autocomplete']),
                TextField::new('zipcode_ope', 'Code Postal')
                    ->setFormTypeOption('attr', ['class' => 'zipcode_ope']),
                TextField::new('city_ope', 'Ville')
                    ->setFormTypeOption('attr', ['class' => 'city_ope']),
                DateTimeField::new('finished_at', 'Terminé le')
                    ->hideOnForm(),
                AssociationField::new('salarie', 'Salarié')
            ];}
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
            $qb->andWhere('entity.status = :status')
                ->setParameter('status', $statusFilter);
        }
    
        if ($this->isGranted('ROLE_CUSTOMER')) {
            $qb->andWhere('entity.customer = :currentUser')
               ->setParameter('currentUser', $user);
        } elseif ($this->isGranted('ROLE_ADMIN')) {
            // Laisser l'administrateur voir toutes les opérations
        } else {
            // Restreindre les utilisateurs qui ne sont pas administrateurs.
            $qb->andWhere('entity.status = :statusPending OR entity.status = :statusCancelled OR (entity.status = :statusAccepted AND entity.salarie = :user)')
            ->setParameter('statusPending', 'En attente de Validation')
            ->setParameter('statusAccepted', 'En cours')
            ->setParameter('statusCancelled', 'Refusée')
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
        $downloadInvoice = Action::new('downloadInvoice', 'Télécharger Facture', 'fa fa-download')
            ->linkToRoute('operation_download_invoice', function (Operation $operation) {
                return ['id' => $operation->getId()];
            })
            ->displayIf(static function (Operation $operation) {
                return $operation->getStatus() === 'Terminée';
            });
            $finishAction = Action::new('terminée', 'Terminée', 'fa fa-check')
            ->displayIf(function (Operation $operation) {
                return ($this->isGranted('ROLE_ADMIN') || 
                $this->isGranted('ROLE_SENIOR') || 
                $this->isGranted('ROLE_APPRENTI')) 
                && $operation->getStatus() === 'En cours';
            })
            ->linkToCrudAction('finishOperation'); 
        return $actions
            ->add(Crud::PAGE_INDEX, $acceptAction)
            ->add(Crud::PAGE_INDEX, $declineAction)
            ->add(Crud::PAGE_INDEX, $downloadInvoice)
            ->add(Crud::PAGE_INDEX, $finishAction);
    }
    
    /**
     * Méthode personnalisée pour l'action "Accepter".
     */
    public function acceptOperation(AdminContext $context, EntityManagerInterface $entityManager, SessionInterface $session): Response {
        $operation = $context->getEntity()->getInstance();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }
        
        // Logique pour accepter l'opération
        $operation->setStatus('En cours');
        $operation->setSalarie($this->security->getUser());
        $entityManager->flush();

        
        // Vérifier si le message flash a déjà été affiché dans la session
        if (!$session->getFlashBag()->has('success')) {
            // Si le message flash n'a pas encore été affiché, l'ajouter
            $session->getFlashBag()->add('success', 'La mission a été acceptée et est maintenant "En cours".');
                    return new RedirectResponse('/admin');
        }
    
        return new Response('<script>window.location.reload();</script>');
    }
    
    public function declineOperation(AdminContext $context, EntityManagerInterface $entityManager, SessionInterface $session): Response {
        $operation = $context->getEntity()->getInstance();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }
        // Logique pour refuser l'opération
        $operation->setStatus('Refusée');
        $operation->setSalarie($this->security->getUser());
        $entityManager->flush();
                if (!$session->getFlashBag()->has('error')) {
            // Si le message flash n'a pas encore été affiché, l'ajouter
            $session->getFlashBag()->add('error', 'La mission a été annulée et est maintenant "Refusée".');
                    return new RedirectResponse('/admin');
        }
    
        return new Response('<script>window.location.reload();</script>');

    }
    public function finishOperation(AdminContext $context, EntityManagerInterface $entityManager, SessionInterface $session): Response {
        $operation = $context->getEntity()->getInstance();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }
        
        // Logique pour accepter l'opération
        $operation->setStatus('Terminée');
        $operation->setSalarie($this->security->getUser());
        $entityManager->flush();

        
        // Vérifier si le message flash a déjà été affiché dans la session
        if (!$session->getFlashBag()->has('success')) {
            // Si le message flash n'a pas encore été affiché, l'ajouter
            $session->getFlashBag()->add('success', 'La mission est maintenant terminée');
                    return new RedirectResponse('/admin');
        }
    
        return new Response('<script>window.location.reload();</script>');
    } 
}
