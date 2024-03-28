<?php

namespace App\Controller\Admin;

use DateTimeImmutable;
use App\Entity\Operation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
    public function edit(AdminContext $context)
    {
        $operation = $context->getEntity()->getInstance();
        $user = $this->getUser();
    
        if (!$operation instanceof Operation || ($operation->getCustomer() !== $user && !$this->isGranted('ROLE_ADMIN'))) {
            $this->addFlash('error', 'Vous n\'avez pas le droit de modifier cette opération.');
            return $this->redirectToRoute('admin'); // Assurez-vous que la route 'admin' est correcte
        }
    
        // Assurez-vous que vous avez accès à Twig ou injectez le service Twig
        return $this->render('operation_crud/edit.html.twig', [
            'operation' => $operation
        ]);
    }
    
public function delete(AdminContext $context)
{
    $operation = $context->getEntity()->getInstance();
    if (!$operation instanceof Operation) {
        throw new \RuntimeException('L\'entité attendue n\'est pas une instance d\'Operation.');
    }

    // Vérifier les droits de l'utilisateur sur l'opération
    $this->denyAccessUnlessGranted('DELETE', $operation);

    return parent::delete($context);
}

    
    protected function initialize(Request $request): void
    {
        parent::initialize($request);
    
        $entityId = $request->query->get('entityId');
        if ($entityId) {
            $operation = $this->entityManager->getRepository(Operation::class)->find($entityId);
            $this->denyAccessUnlessGranted('EDIT', $operation);
        }
    }
    
    public function configureCrud(Crud $crud): Crud {
        return $crud
            ->overrideTemplate('crud/new', 'operation_crud/new.html.twig')
            ->overrideTemplate('crud/edit', 'operation_crud/edit.html.twig')
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
            $this->denyAccessUnlessGranted('EDIT', $entityInstance);
            $this->setOperationPrice($entityInstance);
        }
    
        parent::updateEntity($entityManager, $entityInstance);
    }
    

    public function configureFields(string $pageName): iterable {
        $fields = [];

        if ($this->isGranted('ROLE_CUSTOMER') && Crud::PAGE_INDEX === $pageName) {
            $fields[] = FormField::addTab('Mission');
            $fields[] = DateTimeField::new('created_at', 'Créé le')
                ->hideOnForm();
            $fields[] = TextField::new('name', 'Mission');
            $fields[] = TextField::new('attachmentFile')
            ->setLabel('Photo')
            ->setFormType(VichImageType::class)
                        ->onlyWhenCreating();

            $fields[] = ImageField::new('attachment', 'Photo')
                        ->setBasePath('/images/products')
                        ->onlyOnIndex();

            $fields[] = TextEditorField::new('description', 'Description')
                        ->hideOnForm();

            $fields[] = TextareaField::new('description', 'Description')
                        ->renderAsHtml()
                        ->hideOnIndex();

            $fields[] = ChoiceField::new('type', 'Type de mission')
                        ->setChoices([
                            'Petite - 1000€' => 'Little',
                            'Moyenne - 2500€' => 'Medium',
                            'Grande - 5000€' => 'Big',
                            'Personnalisée' => 'Custom',
                        ])
                        ->renderAsBadges([
                            'Little' => 'info',
                            'Medium' => 'warning',
                            'Big' => 'success',
                            'Custom' => 'secondary',
                        ]);
                    
            $fields[] = AssociationField::new('salarie', 'Employé En Charge de Votre Demande')
                        ->formatValue(function ($value, $entity) {
                            $salarie = $entity->getSalarie();
                            return $salarie ? sprintf('%s %s', $salarie->getFirstName(), $salarie->getName()) : 'Non assigné';
                        });
                    
            $fields[] = ChoiceField::new('status')
                        ->setChoices([
                            'En attente' => 'En attente de Validation',
                            'En cours' => 'En cours',
                            'Terminée' => 'Terminée',
                            'Refusée' => 'Refusée',
                        ])
                        ->renderAsBadges([
                            'En attente de Validation' => 'warning',
                            'En cours' => 'primary',
                            'Terminée' => 'success',
                            'Refusée' => 'danger',
                        ]);
                    
            $fields[] = TextField::new('fullAddress', 'Adresse d\'intervention')
                        ->formatValue(function ($value, $entity) {
                            return $entity->getFullAddress();
                        });
            }
    
    
            if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
                // Champs pour les pages de création et d'édition
                $fields = [
                    FormField::addPanel('Détails de la Mission'),
                    TextField::new('name', 'Intitulé de l’opération')
                        ->setLabel('Mission'),
                    ChoiceField::new('type', 'Type de mission')
                        ->setChoices([
                            'Petite - 1000€' => 'Little',
                            'Moyenne - 2500€' => 'Medium',
                            'Grande - 5000€' => 'Big',
                            'Personnalisée' => 'Custom',
                        ]),
                    DateTimeField::new('rdv_at', 'Date de RDV'),
                    TextareaField::new('description', 'Description'),
                    TextField::new('street_ope', 'Rue')
                        ->setFormTypeOption('attr', ['class' => 'adresse-autocomplete']),
                    TextField::new('zipcode_ope', 'Code Postal')
                        ->setFormTypeOption('attr', ['class' => 'zipcode_ope']),
                    TextField::new('city_ope', 'Ville')
                        ->setFormTypeOption('attr', ['class' => 'city_ope']),
                    TextField::new('attachmentFile')
                        ->setLabel('Photo')
                        ->setFormType(VichImageType::class)
                        ->onlyWhenCreating(),
                        
                ];
                
            
        } else {
            if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_APPRENTI') || ($this->isGranted('ROLE_SENIOR') && Crud::PAGE_INDEX === $pageName))
 {
            return [
                FormField::addTab('Mission'),
                DateTimeField::new('created_at', 'Créé le')
                    ->hideOnForm(),
                FormField::addColumn('col-lg-8 col-xl-3'),
                IdField::new('id', 'Nº')
                    ->hideOnForm(),
                AssociationField::new('customer', 'Client')
                    ->hideOnForm(),
                AssociationField::new('salarie', 'Opérateur'),
                TextField::new('name', 'Intitulé de l’opération')
                    ->setLabel('Mission')
                    ->hideOnIndex(),
                TextField::new('attachmentFile')
                    ->setLabel('Photo')
                    ->setFormType(VichImageType::class)
                    ->onlyWhenCreating(),
                ImageField::new('attachment', 'Photo')
                    ->setBasePath('/images/products')
                    ->onlyOnIndex(),
                ChoiceField::new('type', 'Type de mission')
                    ->setChoices([
                        'Petite - 1000€' => 'Little',
                        'Moyenne - 2500€' => 'Medium',
                        'Grande - 5000€' => 'Big',
                        'Personnalisée' => 'Custom',
                    ])
                    ->renderAsBadges([
                        'Little' => 'info',
                        'Medium' => 'warning',
                        'Big' => 'success',
                        'Custom' => 'secondary',
                    ]),
                FormField::addColumn('col-lg-4 col-xl-4'),
                DateTimeField::new('rdv_at', 'RDV'),
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
                    ])
                    ->renderAsBadges([
                        'En attente de Validation' => 'info',
                        'En cours' => 'warning',
                        'Terminée' => 'success',
                        'Archivée' => 'success',
                    ])
                    ->hideOnForm(),
                TextField::new('street_ope', 'Rue')
                    ->setFormTypeOption('attr', ['class' => 'adresse-autocomplete']),
                TextField::new('zipcode_ope', 'CP')
                    ->setFormTypeOption('attr', ['class' => 'zipcode_ope']),
                TextField::new('city_ope', 'Ville')
                    ->setFormTypeOption('attr', ['class' => 'city_ope']),
                DateTimeField::new('finished_at', 'Terminée le')
                    ->hideOnForm(),
            ];
        }
        }
        return $fields;
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
            $qb->andWhere('entity.status = :statusPending OR entity.status = :statusCancelled OR entity.status = :statusAccepted OR entity.status = :statusFinished AND entity.salarie = :user')
            ->setParameter('statusPending', 'En attente de Validation')
            ->setParameter('statusAccepted', 'En cours')
            ->setParameter('statusCancelled', 'Refusée')
            ->setParameter('statusFinished', 'Terminée')
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
            $finishAction = Action::new('terminée', 'Terminer', 'fa fa-check')
            ->displayIf(function (Operation $operation) {
                return ($this->isGranted('ROLE_ADMIN') || 
                $this->isGranted('ROLE_SENIOR') || 
                $this->isGranted('ROLE_APPRENTI')) 
                && $operation->getStatus() === 'En cours';
            })
            ->linkToCrudAction('finishOperation'); 
            $archiveAction = Action::new('archivée', 'Archiver', 'fa fa-history')
            ->displayIf(function (Operation $operation) {
                $user = $this->security->getUser();
                return $this->isGranted('ROLE_ADMIN') || ($operation->getCustomer() === $user && $operation->getStatus() === 'Terminée');
            })
            ->linkToCrudAction('archiveOperation');
        return $actions
            ->add(Crud::PAGE_INDEX, $downloadInvoice)
            ->add(Crud::PAGE_INDEX, $archiveAction)
            ->add(Crud::PAGE_INDEX, $finishAction)
            ->add(Crud::PAGE_INDEX, $declineAction)
            ->add(Crud::PAGE_INDEX, $acceptAction)
            ;
    }
    
    /**
     * Méthode personnalisée pour l'action "Accepter".
     */
    public function acceptOperation(AdminContext $context, EntityManagerInterface $entityManager, SessionInterface $session): Response {
        $operation = $context->getEntity()->getInstance();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }
        
        $user = $this->security->getUser();
    
        // Vérifier le rôle de l'utilisateur et le nombre d'opérations en cours
        if ($user) {
            $role = '';
            $maxOperations = 0;
    
            if ($this->isGranted('ROLE_ADMIN')) {
                $role = 'admin';
                $maxOperations = 5;
            } elseif ($this->isGranted('ROLE_SENIOR')) {
                $role = 'senior';
                $maxOperations = 3;
            } elseif ($this->isGranted('ROLE_APPRENTI')) {
                $role = 'apprenti';
                $maxOperations = 1;
            }
    
            // Récupérer le nombre d'opérations en cours de l'utilisateur
            if ($role) {
                $qb = $entityManager->createQueryBuilder();
                $qb->select('COUNT(op)')
                   ->from(Operation::class, 'op')
                   ->where('op.status = :status')
                   ->andWhere('op.salarie = :user')
                   ->setParameter('status', 'En cours')
                   ->setParameter('user', $user);
                $count = $qb->getQuery()->getSingleScalarResult();
    
                // Limiter le nombre d'opérations en cours en fonction du rôle de l'utilisateur
                if ($count >= $maxOperations) {
                    // Afficher un message d'erreur ou rediriger avec un message d'erreur
                    if (!$session->getFlashBag()->has('error')) {
                        $session->getFlashBag()->add('error', 'Vous avez déjà accepté le maximum d\'opérations en cours.');
                    }
                    return new RedirectResponse('/admin');
                }
            }
        }
        
    
        // Logique pour accepter l'opération
        $operation->setStatus('En cours');
        $operation->setSalarie($user);
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

    public function archiveOperation(AdminContext $context, EntityManagerInterface $entityManager, SessionInterface $session): Response {
        $operation = $context->getEntity()->getInstance();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }
        
        // Logique pour accepter l'opération
        $operation->setStatus('Archivée');
        $operation->setSalarie($this->security->getUser());
        $entityManager->flush();

        
        // Vérifier si le message flash a déjà été affiché dans la session
        if (!$session->getFlashBag()->has('success')) {
            // Si le message flash n'a pas encore été affiché, l'ajouter
            $session->getFlashBag()->add('success', 'La mission est maintenant archivée');
                    return new RedirectResponse('/admin');
        }
    
        return new Response('<script>window.location.reload();</script>');
    } 
}
