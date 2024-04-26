<?php

namespace App\Controller\Admin;

use Exception;
use LogicException;
use DateTimeImmutable;
use DateTimeInterface;
use App\Entity\Operation;
use App\Service\LogsService;
use UnexpectedValueException;
use Doctrine\ORM\QueryBuilder;
use App\Service\InvoiceService;
use App\Service\SendMailService;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\Session\Session;

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
        $currentUser = $this->security->getUser();
        
        if (!$operation instanceof Operation || 
            ($operation->getCustomer() !== $currentUser && !$this->isGranted('ROLE_ADMIN'))) {
            $this->addFlash('error', 'Vous n\'avez pas le droit de modifier cette opération.');
            return $this->redirectToRoute('admin');
        }
    
        return $this->render('operation_crud/edit.html.twig', [
            'operation' => $operation
        ]);
    }
    
    
    public function delete(AdminContext $context)
    {
        $operation = $context->getEntity()->getInstance();
        $currentUser = $this->security->getUser();
    
        if (!$operation instanceof Operation || 
            ($operation->getCustomer() !== $currentUser && !$this->isGranted('ROLE_ADMIN'))) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette opération.');
            return $this->redirectToRoute('admin');
        }
    
        return parent::delete($context);
    }
    


    
    public function configureCrud(Crud $crud): Crud {
        return $crud
            ->overrideTemplate('crud/new', 'operation_crud/new.html.twig')
            ->overrideTemplate('crud/edit', 'operation_crud/edit.html.twig')
            ->setSearchFields(['name', 'type', 'status'])
            ->setPaginatorPageSize(7)            
            ->setPaginatorRangeSize(0);
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
        if ($entityInstance instanceof Operation && 
            $entityInstance->getCustomer() !== $this->getUser() && 
            !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Action non autorisée.');
        }
        parent::persistEntity($entityManager, $entityInstance);
    }
    
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        if ($entityInstance instanceof Operation && 
            $entityInstance->getCustomer() !== $this->getUser() && 
            !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Action non autorisée.');
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

            $fields[] = ChoiceField::new('type', 'Type')
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
                    
            $fields[] = TextField::new('salarie', 'Opérateur assigné')
                        ->formatValue(function ($value, $entity) {
                            $salarie = $entity->getSalarie();
                            return $salarie ? sprintf('%s %s', $salarie->getFirstName(), $salarie->getName()) : 'Non assigné';
                        });
                    
            $fields[] =ChoiceField::new('status')
                        ->setChoices([
                        'En attente - Envoyé' => 'En attente de Validation',
                        'En cours' => 'En cours',
                        'Terminée' => 'Terminée',
                        'Archivée' => 'Archivée',
                        'Refusée' => 'Refusée',
                    ])   ->renderAsBadges([
                        'En attente de Validation' => 'warning',
                        'En cours' => 'primary',
                        'Terminée' => 'success',
                        'Archivée' => 'info',
                        'Refusée' => 'danger',
                    ]); 
                    
            $fields[] = TextField::new('fullAddress', 'Adresse d\'intervention')
                        ->formatValue(function ($value, $entity) {
                            return $entity->getFullAddress();
                        });
            }
            
            $fields[] = DateTimeField::new('rdv_at', 'Date d\'intervention')
            ->formatValue(function (?DateTimeInterface $value) {
                if ($value) {
                    // Définir le locale en français
                    $formatter = new \IntlDateFormatter(
                        'fr_FR',
                        \IntlDateFormatter::FULL,
                        \IntlDateFormatter::SHORT,
                        date_default_timezone_get()
                    );
                    
                    // Formatage de la date pour qu'elle soit lisible
                    return $formatter->format($value);
                } else {
                    return 'À définir';
                }
            });

    
    
            if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
                // Champs pour les pages de création et d'édition
                $fields = [
                    FormField::addPanel('Détails de la Mission'),
                    TextField::new('name', 'Intitulé de l’opération')
                        ->setLabel('Mission'),
                    ChoiceField::new('type', 'Type')
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
                TextField::new('name', 'Mission')
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
                ChoiceField::new('type', 'Type')
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
                        'En attente - Envoyé' => 'En attente de Validation',
                        'En cours' => 'En cours',
                        'Terminée' => 'Terminée',
                        'Archivée' => 'Archivée',
                        'Refusée' => 'Refusée',
                    ])
                    ->renderAsBadges([
                        'En attente de Validation' => 'info',
                        'En cours' => 'warning',
                        'Terminée' => 'success',
                        'Archivée' => 'info',
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
        $security = $this->security; // Utiliser la propriété de classe
    
        // Définir les actions avec leurs icônes et les rendre visibles selon les conditions de sécurité
        $acceptAction = Action::new('accept', 'Accepter', 'fa fa-check')
        ->displayIf(function (Operation $operation) use ($security) {
            return $operation->getStatus() === 'En attente de Validation' &&
                       ($security->isGranted('ROLE_ADMIN') || 
                        $security->isGranted('ROLE_SENIOR') || 
                        $security->isGranted('ROLE_APPRENTI'));
            })
        ->linkToCrudAction('acceptOperation');

    // Définir l'action 'decline'
        $declineAction = Action::new('decline', 'Refuser', 'fa fa-times')
        ->displayIf(function (Operation $operation) use ($security) {
            return $operation->getStatus() === 'En attente de Validation' && 
                        ($security->isGranted('ROLE_ADMIN') || 
                        $security->isGranted('ROLE_SENIOR') || 
                        $security->isGranted('ROLE_APPRENTI'));
})
        ->linkToCrudAction('declineOperation');
    
        $finishAction = Action::new('finish', 'Terminer', 'fa fa-flag-checkered')
            ->displayIf(function (Operation $operation) use ($security) {
                return $operation->getStatus() === 'En cours'&& 
                        ($security->isGranted('ROLE_ADMIN') || 
                        $security->isGranted('ROLE_SENIOR') || 
                        $security->isGranted('ROLE_APPRENTI')); 
            })
            ->linkToCrudAction('finishOperation');
    
        $archiveAction = Action::new('archive', 'Archiver', 'fa fa-archive')
            ->displayIf(function (Operation $operation) use ($security) {
                return $operation->getStatus() === 'Terminée';
            })
            ->linkToCrudAction('archiveOperation');
    
        $downloadInvoice = Action::new('downloadInvoice', 'Télécharger Facture', 'fa fa-download')
            ->displayIf(static function (Operation $operation) {
                return $operation->getStatus() === 'Terminée';
            })
            ->linkToRoute('operation_download_invoice', function (Operation $operation) {
                return ['id' => $operation->getId()];
            });
    
        $changeOperatorAction = Action::new('changeOperator', 'Changer Opérateur', 'fa fa-exchange-alt')
            ->displayIf(static function (Operation $operation) use ($security) {
                return $security->isGranted('ROLE_ADMIN');
            })
            ->linkToRoute('admin_change_operator', function (Operation $operation) {
                return ['id' => $operation->getId()];
            });
    
    // Mettre à jour l'action "Modifier" pour ajouter une icône
    $actions->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
        return $action->setIcon('fa fa-edit')->setLabel('Modifier');
    });
    // Mettre à jour l'action "Supprimer" pour ajouter une icône
    $actions->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
        return $action->setIcon('fa fa-trash')->setLabel('Supprimer');
    });
    
        // Ajouter toutes les actions à la page index
        return $actions
            ->add(Crud::PAGE_INDEX, $changeOperatorAction)
            ->add(Crud::PAGE_INDEX, $downloadInvoice)
            ->add(Crud::PAGE_INDEX, $archiveAction)
            ->add(Crud::PAGE_INDEX, $finishAction)
            ->add(Crud::PAGE_INDEX, $declineAction)
            ->add(Crud::PAGE_INDEX, $acceptAction);
    }

    
    /**
     * Méthode personnalisée pour l'action "Accepter".
     */
    public function acceptOperation(AdminContext $context, EntityManagerInterface $entityManager, SendMailService $mail, LogsService $logsService): Response {
        $operation = $context->getEntity()->getInstance();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }
    
        $customer = $operation->getCustomer();
        if (!$customer) {
            throw new UnexpectedValueException('Client associé non trouvé');
        }
    
        $user = $this->security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non identifié');
        }
    
        $roleLimits = [
            'ROLE_ADMIN' => 5,
            'ROLE_SENIOR' => 3,
            'ROLE_APPRENTI' => 1
        ];
    
        list($role, $maxOperations) = $this->determineUserLimits($user, $roleLimits);
    
        if ($this->exceedsOperationLimit($entityManager, $user, $maxOperations)) {
            $this->addFlash('error', 'Vous avez déjà accepté le maximum d\'opérations en cours.');
            return new RedirectResponse('/admin');
        }
    
        $this->processOperation($entityManager, $operation, $user);
    
        $idOpe = $operation->getId();
        $created = $operation->getCreatedAt();
        $type = $operation->getType();
        $price = $operation->getPrice();
        $city = $operation->getCityOpe();
        $customerId = $operation->getCustomer();

        // Log successful acceptation
        try {
            $logsService->postLog([
                'loggerName' => 'Operation',
                'user' => 'Anonymous',
                'message' => 'User accepted operation',
                'level' => 'info',
                'data' => [
                    'id_ope' => $idOpe,
                    'created' => $created,
                    'type' => $type,
                    'price' => $price,
                    'city' => $city,
                    'customer_id' => $customerId
                ]

            ]);
            } catch (Exception $e) {
            }

        if ($this->sendOperationEmails($mail, $customer)) {
            $this->addFlash('success', 'La mission a été acceptée et est maintenant "En cours".');
            return new RedirectResponse('/admin');
        }
    
       

        return new RedirectResponse('/admin');
    }
    
    
    private function determineUserLimits($user, $roleLimits) {
        foreach ($roleLimits as $role => $limit) {
            if ($this->isGranted($role)) {
                return [$role, $limit];
            }
        }
        throw new LogicException('Rôle de l\'utilisateur non reconnu ou absent.');
    }
    
    private function exceedsOperationLimit(EntityManagerInterface $entityManager, $user, $maxOperations) {
        $count = $entityManager->createQueryBuilder()
            ->select('COUNT(op)')
            ->from(Operation::class, 'op')
            ->where('op.status = :status AND op.salarie = :user')
            ->setParameter('status', 'En cours')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    
        return $count >= $maxOperations;
    }
    
    
    private function processOperation(EntityManagerInterface $entityManager, $operation, $user) {
        $operation->setStatus('En cours');
        $operation->setSalarie($user);
        $entityManager->flush();
    }
    
    private function sendOperationEmails(SendMailService $mail, $customer) {
        try {
            $mail->send(
                'no-reply@cleanthis.fr',
                $customer->getEmail(),
                'Acceptation de votre opération',
                'opeaccept',
                ['user' => $customer]
            );
            return true;
        } catch (Exception $e) {
            error_log('Caught exception: Connexion avec MailHog sur 1025 non établie' . $e->getMessage());
            return false;
        }
    }
  
    public function declineOperation(AdminContext $context, EntityManagerInterface $entityManager, SendMailService $mail, LogsService $logsService): Response {
        $operation = $context->getEntity()->getInstance();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }
        $customer = $operation->getCustomer();
        if (!$customer) {
            throw new UnexpectedValueException('Client associé non trouvé');
        }
    
        // Logique pour refuser l'opération
        $operation->setStatus('Refusée');
        $operation->setSalarie($this->security->getUser());
        $entityManager->flush();
    
        // Logging the declined operation
        try {
            $logsService->postLog([
                'loggerName' => 'Operation',
                'user' => 'Anonymous',
                'message' => 'User declined operation',
                'level' => 'info',
                'data' => [
                    'id_ope' => $operation->getId(),
                    'created' => $operation->getCreatedAt(),
                    'type' => $operation->getType(),
                    'price' => $operation->getPrice(),
                    'city' => $operation->getCityOpe(),
                    'customer_id' => $customer->getId()
                ]
            ]);
        } catch (Exception $e) {
            error_log('Failed to log declined operation: ' . $e->getMessage());
        }
    
        // Attempt to send decline notification email
        try {
            $mail->send(
                'no-reply@cleanthis.fr',
                $customer->getEmail(),
                'Refus de votre opération',
                'opedecline',
                ['user' => $customer]
            );
            $this->addFlash('error', 'La mission a été annulée et est maintenant "Refusée".');
        } catch (Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
            error_log('Caught exception: Connexion avec MailHog sur 1025 non établie' . $e->getMessage());
        }
    
        return new RedirectResponse('/admin');
    }
    
    public function finishOperation(AdminContext $context, EntityManagerInterface $entityManager, SendMailService $mail, InvoiceService $invoiceService, LogsService $logsService): Response {
        $operation = $context->getEntity()->getInstance();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }
        
        // Logique pour terminer l'opération
        $operation->setStatus('Terminée');
        $operation->setFinishedAt(new DateTimeImmutable);
        $operation->setSalarie($this->security->getUser());
        $entityManager->flush();
    
        // Log operation finished
        try {
            $logsService->postLog([
                'loggerName' => 'Operation',
                'user' => 'Anonymous',
                'message' => 'User finished operation',
                'level' => 'info',
                'data' => [
                    'id_ope' => $operation->getId(),
                    'finished' => $operation->getFinishedAt(),
                    'type' => $operation->getType(),
                    'price' => $operation->getPrice(),
                    'city' => $operation->getCityOpe(),
                    'customer_id' => $operation->getCustomer()->getId()
                ]
            ]);
        } catch (Exception $e) {
            error_log('Failed to log operation: ' . $e->getMessage());
        }
    
        // Try to send an email with the invoice attached
        try {
            // Generate the invoice PDF and get its path
            $pdfPath = $invoiceService->generateInvoiceMail($operation);
            $mail->sendAttach(
                'no-reply@cleanthis.fr',
                $operation->getCustomer()->getEmail(),
                'Opération terminée - Facture',
                'opefinished',
                ['user' => $operation->getCustomer()],
                $pdfPath
            );
            $this->addFlash('success', 'La mission est maintenant terminée et la facture a été envoyée.');
        } catch (Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
            error_log('Caught exception: Connexion avec MailHog sur 1025 non établie ' . $e->getMessage());
        }
        return new RedirectResponse('/admin');
    }

    
    public function archiveOperation(AdminContext $context, EntityManagerInterface $entityManager, LogsService $logsService, Security $security): Response {
        $operation = $context->getEntity()->getInstance();
        /** @var User $currentUser */
        $currentUser = $security->getUser();
        
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }
    
        if ($currentUser && !$this->isGranted('ROLE_ADMIN') &&
            $operation->getCustomer() !== $currentUser &&
            $operation->getSalarie() !== $currentUser) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à archiver cette opération.');
            return $this->redirectToRoute('admin');
        }
    
        $operation->setArchivedFor($currentUser ? $currentUser->getId() : null);
        $entityManager->flush();


         //Log archived operation
         try {
            $logsService->postLog([
                'loggerName' => 'Operation',
                'user' => 'Anonymous',
                'message' => 'User archived operation',
                'level' => 'info'
            ]);
            } catch (Exception $e) {
        }
    
        $this->addFlash('success', 'La mission est maintenant archivée pour vous');
        return $this->redirectToRoute('admin');
    }
    

}
