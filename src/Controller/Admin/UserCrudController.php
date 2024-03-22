<?php
// src/Controller/Admin/UserCrudController.php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\OperationCrudController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\Filter;
use Symfony\Component\Validator\Constraints\Regex;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\{EmailType};
use Symfony\Component\Form\{FormBuilderInterface, FormEvent};
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, EmailField, TextField};
use Symfony\Component\Form\Extension\Core\Type\{PasswordType, RepeatedType};
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Action, Actions, Crud, KeyValueStore};


class UserCrudController extends AbstractCrudController
{
    private Security $security;
    private AuthorizationCheckerInterface $authChecker;
    
    public function __construct(
        public UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        AuthorizationCheckerInterface $authChecker
    ) {
        $this->security = $security;
        $this->authChecker = $authChecker;
    }
    

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud {
        return $crud
            ->overrideTemplate('crud/new', 'operation_crud/new.html.twig')
            ->overrideTemplate('crud/edit', 'operation_crud/edit.html.twig')
            ->setPageTitle(Crud::PAGE_INDEX, 'Membres')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le Membre')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un Membre')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du Membre')
            ->setPaginatorPageSize(10)
            ->setPaginatorRangeSize(0)
            ->setSearchFields(null);
            $rolesFilter = $this->getContext()->getRequest()->query->get('roles');
            if ($rolesFilter) {
                $crud->setDefaultSort(['roles' => $rolesFilter]);
        }
    }
    
    public function configureActions(Actions $actions): Actions {
        $actions = parent::configureActions($actions);
    
        // Obtenez l'utilisateur actuellement connecté
        $currentUser = $this->security->getUser();
    
        // Vérifiez si l'utilisateur actuel est un administrateur
        $isAdmin = $this->security->isGranted('ROLE_ADMIN');
    
        // Désactiver l'action 'NEW' pour les utilisateurs sans le rôle 'ROLE_ADMIN'
        if (!$isAdmin) {
            $actions->disable(Action::NEW);
        }
    
        // Mise à jour de l'action DELETE
        $actions->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) use ($currentUser, $isAdmin) {
            // L'action DELETE est conditionnée : seulement pour les administrateurs et ils ne peuvent pas se supprimer eux-mêmes
            return $action->displayIf(static function ($entity) use ($currentUser, $isAdmin) {
                return $isAdmin && $entity->getId() !== $currentUser->getId();
            });
        });
    
        return $actions;
    }
    
    

    public function edit(AdminContext $context) {
        $entity = $context->getEntity()->getInstance();
        $currentUser = $this->security->getUser();
    
        // Interdire l'édition d'autres profils pour les rôles non-admin
        if (!$this->security->isGranted('ROLE_ADMIN') && $entity->getId() !== $currentUser->getId()) {
            throw new AccessDeniedException('Vous n\'avez pas les droits pour modifier ce profil.');
        }
    
        return parent::edit($context);
    }

// ...

    

    public function configureFields(string $pageName): iterable
    {
        $fields = [  
            IdField::new('id')->hideOnForm(),
            TextareaField::new('email')
            ->setFormType(EmailType::class),
            TextField::new('name', 'Nom'),
            TextField::new('firstname','Prenom'),
            TextField::new('street', 'Rue')
            ->setFormTypeOption('attr', ['class' => 'adresse-autocomplete']),
            TextField::new('zipcode', 'Code Postal')
            ->setFormTypeOption('attr', ['class' => 'zipcode_ope']),
            TextField::new('city', 'Ville')
            ->setFormTypeOption('attr', ['class' => 'city_ope']),
            TextField::new('phone','Telephone'),
            ChoiceField::new('singleRole', 'Role')
            ->setChoices([
                'Admin' => 'ROLE_ADMIN',
                'Senior' => 'ROLE_SENIOR',
                'Apprenti' => 'ROLE_APPRENTI',
                'Client' => 'ROLE_CUSTOMER'
            ])
            ->renderAsBadges([
                'ROLE_APPRENTI' => 'warning',
                'ROLE_SENIOR' => 'primary',
                'ROLE_EXPERT' => 'success',
                'ROLE_ADMIN' => 'danger'
            ])
            ->setFormTypeOption('disabled', !$this->security->isGranted('ROLE_ADMIN')),
    ];
            $password = TextField::new('password', 'Mot de passe')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer mot de passe'],
                'mapped' => false,
                'constraints' => [
                    new Regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', "Il faut un mot de passe de 8 caractères, une majuscule et un chiffre")
                ]
            ])
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms();

        $fields[] = $password; 

        return $fields;
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $actions): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $actions);
        return $this->addPasswordEventListener($formBuilder);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $actions): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $actions);
        return $this->addPasswordEventListener($formBuilder);
    }

    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());   
    } 

    private function hashPassword() {
        return function($event) {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }
            $password = $form->get('password')->getData();
            if ($password === null) {
                return;
            }

            $hash = $this->userPasswordHasher->hashPassword($form->getData(), $password);
            $form->getData()->setPassword($hash);
        };

    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $entityAlias = $qb->getRootAliases()[0];
        $currentUser = $this->security->getUser();
    
        // Filtre pour les administrateurs
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $userType = $this->container->get('request_stack')->getCurrentRequest()->query->get('userType');
    
            if ($userType === 'customer') {
                $qb->andWhere($entityAlias . '.roles LIKE :role')
                    ->setParameter('role', '%"ROLE_CUSTOMER"%');
            } elseif ($userType === 'employee') {
                $qb->andWhere($entityAlias . '.roles LIKE :roleAdmin OR ' . $entityAlias . '.roles LIKE :roleSenior OR ' . $entityAlias . '.roles LIKE :roleApprenti')
                    ->setParameter('roleAdmin', '%"ROLE_ADMIN"%')
                    ->setParameter('roleSenior', '%"ROLE_SENIOR"%')
                    ->setParameter('roleApprenti', '%"ROLE_APPRENTI"%');
            }
        } else {
            // Limiter les utilisateurs non-administrateurs à voir uniquement leur propre profil
            if ($currentUser instanceof User) {
                $qb->andWhere($entityAlias . '.id = :current_user_id')
                    ->setParameter('current_user_id', $currentUser->getId());
            }
        }
    
        return $qb;
    }
    
}
