<?php

namespace App\Controller\Admin;


use Vich\UploaderBundle\Form\Type\VichImageType;
use DateTimeImmutable;
use DateTimeInterface;
use App\Entity\Operation;
use Doctrine\ORM\QueryBuilder;
use App\Service\InvoiceService;
use App\Service\LogsService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
class OperationCrudController extends AbstractCrudController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getEntityFqcn(): string
    {
        return Operation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplate('crud/new', 'operation_crud/new.html.twig')
            ->overrideTemplate('crud/edit', 'operation_crud/edit.html.twig')
            ->setSearchFields(['name', 'type', 'status'])
            ->setPaginatorPageSize(7);
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [];

        if ($this->isGranted('ROLE_CUSTOMER') && Crud::PAGE_INDEX === $pageName) {
            $fields[] = FormField::addTab('Mission');
            $fields[] = DateTimeField::new('created_at', 'Créé le')->hideOnForm();
            $fields[] = TextField::new('name', 'Mission');
            $fields[] = TextField::new('attachmentFile')
                ->setLabel('Photo')
                ->setFormType(VichImageType::class)
                ->onlyWhenCreating();
            $fields[] = ImageField::new('attachment', 'Photo')
                ->setBasePath('/images/products')
                ->onlyOnIndex();
            $fields[] = TextEditorField::new('description', 'Description')->hideOnForm();
            $fields[] = TextareaField::new('description', 'Description')->renderAsHtml()->hideOnIndex();
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
            $fields[] = ChoiceField::new('status')
                ->setChoices([
                    'En attente' => 'En attente de Validation',
                    'En cours' => 'En cours',
                    'Terminée' => 'Terminée',
                    'Archivée' => 'Archivée',
                    'Refusée' => 'Refusée',
                ])
                ->renderAsBadges([
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
                    $formatter = new \IntlDateFormatter(
                        'fr_FR',
                        \IntlDateFormatter::FULL,
                        \IntlDateFormatter::SHORT,
                        date_default_timezone_get()
                    );
                    return $formatter->format($value);
                } else {
                    return 'À définir';
                }
            });

        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            $fields = [
                FormField::addPanel('Détails de la Mission'),
                TextField::new('name', 'Intitulé de l’opération')->setLabel('Mission'),
                ChoiceField::new('type', 'Type')
                    ->setChoices([
                        'Petite - 1000€' => 'Little',
                        'Moyenne - 2500€' => 'Medium',
                        'Grande - 5000€' => 'Big',
                        'Personnalisée' => 'Custom',
                    ]),
                DateTimeField::new('rdv_at', 'Date de RDV'),
                TextareaField::new('description', 'Description'),
                TextField::new('street_ope', 'Rue')->setFormTypeOption('attr', ['class' => 'adresse-autocomplete']),
                TextField::new('zipcode_ope', 'Code Postal')->setFormTypeOption('attr', ['class' => 'zipcode_ope']),
                TextField::new('city_ope', 'Ville')->setFormTypeOption('attr', ['class' => 'city_ope']),
                TextField::new('attachmentFile')->setLabel('Photo')->setFormType(VichImageType::class)->onlyWhenCreating(),
            ];
        } else {
            if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_APPRENTI') || ($this->isGranted('ROLE_SENIOR') && Crud::PAGE_INDEX === $pageName)) {
                return [
                    FormField::addTab('Mission'),
                    DateTimeField::new('created_at', 'Créé le')->hideOnForm(),
                    FormField::addColumn('col-lg-8 col-xl-3'),
                    TextField::new('name', 'Mission')->hideOnForm(),
                    AssociationField::new('customer', 'Client')->hideOnForm(),
                    AssociationField::new('salarie', 'Opérateur'),
                    TextField::new('name', 'Intitulé de l’opération')->setLabel('Mission')->hideOnIndex(),
                    TextField::new('attachmentFile')->setLabel('Photo')->setFormType(VichImageType::class)->onlyWhenCreating(),
                    ImageField::new('attachment', 'Photo')->setBasePath('/images/products')->onlyOnIndex(),
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
                    TextEditorField::new('description', 'Description')->hideOnForm(),
                    TextareaField::new('description', 'Description')->renderAsHtml()->hideOnIndex(),
                    ChoiceField::new('status')
                        ->setChoices([
                            'En attente' => 'En attente de Validation',
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
                    TextField::new('street_ope', 'Rue')->setFormTypeOption('attr', ['class' => 'adresse-autocomplete']),
                    TextField::new('zipcode_ope', 'CP')->setFormTypeOption('attr', ['class' => 'zipcode_ope']),
                    TextField::new('city_ope', 'Ville')->setFormTypeOption('attr', ['class' => 'city_ope']),
                    DateTimeField::new('finished_at', 'Terminée le')->hideOnForm(),
                ];
            }
        }
        return $fields;
    }

    public function createEntity(string $entityFqcn)
    {
        $operation = new Operation();
        $operation->setCustomer($this->getUser());
        $operation->setCreatedAt(new DateTimeImmutable());
        $operation->setSalarie(null);
        return $operation;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Operation &&
            $entityInstance->getCustomer() !== $this->getUser() &&
            !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Action non autorisée.');
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Operation &&
            $entityInstance->getCustomer() !== $this->getUser() &&
            !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Action non autorisée.');
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        \EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection $fields,
        \EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection $filters
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
            $qb->andWhere('entity.status IN (:statuses) AND entity.salarie = :user')
                ->setParameter('statuses', ['En attente de Validation', 'Refusée', 'En cours', 'Terminée'])
                ->setParameter('user', $user);
        }
    
        return $qb;
    }
    
    public function configureActions(Actions $actions): Actions
    {
        $security = $this->security;

        $acceptAction = Action::new('accept', 'Accepter', 'fa fa-check')
            ->displayIf(function (Operation $operation) use ($security) {
                return $operation->getStatus() === 'En attente de Validation' &&
                    ($security->isGranted('ROLE_ADMIN') ||
                    $security->isGranted('ROLE_SENIOR') ||
                    $security->isGranted('ROLE_APPRENTI'));
            })
            ->linkToCrudAction('acceptOperation');

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
                return $operation->getStatus() === 'En cours' &&
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
            ->displayIf(function (Operation $operation) {
                return $operation->getStatus() === 'Terminée';
            })
            ->linkToRoute('operation_download_invoice', function (Operation $operation) {
                return ['id' => $operation->getId()];
            });

        $changeOperatorAction = Action::new('changeOperator', 'Changer Opérateur', 'fa fa-exchange-alt')
            ->displayIf(function (Operation $operation) use ($security) {
                return $security->isGranted('ROLE_ADMIN');
            })
            ->linkToRoute('admin_change_operator', function (Operation $operation) {
                return ['id' => $operation->getId()];
            });

        $cancelAction = Action::new('cancel', 'Annuler', 'fa fa-times')
            ->displayIf(function (Operation $operation) {
                $acceptedAt = $operation->getAcceptedAt();
                $now = new DateTimeImmutable();
                return $acceptedAt && $now <= $acceptedAt->modify('+24 hours') && $operation->getCustomer() === $this->security->getUser();
            })
            ->linkToCrudAction('cancelOperation');

        return $actions
            ->add(Crud::PAGE_INDEX, $changeOperatorAction)
            ->add(Crud::PAGE_INDEX, $downloadInvoice)
            ->add(Crud::PAGE_INDEX, $archiveAction)
            ->add(Crud::PAGE_INDEX, $finishAction)
            ->add(Crud::PAGE_INDEX, $declineAction)
            ->add(Crud::PAGE_INDEX, $acceptAction)
            ->add(Crud::PAGE_INDEX, $cancelAction);
    }

    public function acceptOperation(AdminContext $context, EntityManagerInterface $entityManager, SessionInterface $session, SendMailService $mail, LogsService $logsService): Response
    {
        $operation = $context->getEntity()->getInstance();
        $customer = $operation->getCustomer();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }

        $user = $this->security->getUser();
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

            $qb = $entityManager->createQueryBuilder();
            $qb->select('COUNT(op)')
                ->from(Operation::class, 'op')
                ->where('op.status = :status')
                ->andWhere('op.salarie = :user')
                ->setParameter('status', 'En cours')
                ->setParameter('user', $user);
            $count = $qb->getQuery()->getSingleScalarResult();

            if ($count >= $maxOperations) {
                if (!$session->getFlashBag()->has('error')) {
                    $session->getFlashBag()->add('error', 'Vous avez déjà accepté le maximum d\'opérations en cours.');
                }
                return new RedirectResponse('/admin');
            }
        }

        $operation->setStatus('En cours');
        $operation->setSalarie($user);
        $operation->setAcceptedAt(new DateTimeImmutable());
        $entityManager->flush();

        $logsService->postLog([
            'loggerName' => 'Operation',
            'user' => 'Anonymous',
            'message' => 'User accepted operation',
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

        if (!$session->getFlashBag()->has('success')) {
            $session->getFlashBag()->add('success', 'La mission a été acceptée et est maintenant "En cours".');

            try {
                $mail->send(
                    'no-reply@cleanthis.fr',
                    $customer->getEmail(),
                    'Acceptation de votre opération',
                    'opeaccept',
                    ['user' => $customer]
                );
            } catch (\Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
            return new RedirectResponse('/admin');
        }

        return new Response('<script>window.location.reload();</script>');
    }

    public function declineOperation(AdminContext $context, EntityManagerInterface $entityManager, SessionInterface $session, SendMailService $mail, LogsService $logsService): Response
    {
        $operation = $context->getEntity()->getInstance();
        $customer = $operation->getCustomer();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }

        $operation->setStatus('Refusée');
        $operation->setSalarie($this->security->getUser());
        $entityManager->flush();

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

        if (!$session->getFlashBag()->has('error')) {
            $session->getFlashBag()->add('error', 'La mission a été annulée et est maintenant "Refusée".');
            try {
                $mail->send(
                    'no-reply@cleanthis.fr',
                    $customer->getEmail(),
                    'Refus de votre opération',
                    'opedecline',
                    ['user' => $customer]
                );
            } catch (\Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
            return new RedirectResponse('/admin');
        }

        return new Response('<script>window.location.reload();</script>');
    }

    public function finishOperation(AdminContext $context, EntityManagerInterface $entityManager, SessionInterface $session, SendMailService $mail, InvoiceService $invoiceService, LogsService $logsService): Response
    {
        $operation = $context->getEntity()->getInstance();
        $customer = $operation->getCustomer();
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }

        $operation->setStatus('Terminée');
        $operation->setFinishedAt(new DateTimeImmutable);
        $operation->setSalarie($this->security->getUser());
        $entityManager->flush();

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
                'customer_id' => $customer->getId()
            ]
        ]);

        if (!$session->getFlashBag()->has('success')) {
            $session->getFlashBag()->add('success', 'La mission est maintenant terminée');
            try {
                $pdfPath = $invoiceService->generateInvoiceMail($operation);
                $mail->sendAttach(
                    'no-reply@cleanthis.fr',
                    $customer->getEmail(),
                    'Opération terminée - Facture',
                    'opefinished',
                    ['user' => $customer],
                    $pdfPath
                );
            } catch (\Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
            return new RedirectResponse('/admin');
        }

        return new Response('<script>window.location.reload();</script>');
    }

    public function archiveOperation(AdminContext $context, EntityManagerInterface $entityManager, SessionInterface $session, LogsService $logsService): Response
    {
        $operation = $context->getEntity()->getInstance();
        $currentUser = $this->security->getUser();

        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }

        if ($operation->getCustomer() !== $currentUser &&
            $operation->getSalarie() !== $currentUser &&
            !$this->isGranted('ROLE_ADMIN')) {
            $session->getFlashBag()->add('error', 'Vous n\'êtes pas autorisé à archiver cette opération.');
            return $this->redirectToRoute('admin');
        }

        $operation->setStatus('Archivée');
        $entityManager->flush();

        $logsService->postLog([
            'loggerName' => 'Operation',
            'user' => 'Anonymous',
            'message' => 'User archived operation',
            'level' => 'info'
        ]);

        $session->getFlashBag()->add('success', 'La mission est maintenant archivée');
        return new RedirectResponse('/admin');
    }

    public function cancelOperation(AdminContext $context, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $operation = $context->getEntity()->getInstance();
        $currentUser = $this->security->getUser();

        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }

        $acceptedAt = $operation->getAcceptedAt();
        $now = new DateTimeImmutable();

        if ($acceptedAt && $now <= $acceptedAt->modify('+24 hours') && $operation->getCustomer() === $currentUser) {
            $operation->setStatus('Annulée');
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'L\'opération a été annulée avec succès.');
        } else {
            $session->getFlashBag()->add('error', 'Vous ne pouvez pas annuler cette opération car la période de 24 heures est écoulée ou vous n\'êtes pas autorisé.');
        }

        return new RedirectResponse('/admin');
    }
}
