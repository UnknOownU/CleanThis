<?php
// src/Controller/Admin/UserCrudController.php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use App\Entity\User;
use App\Entity\Operation;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\OperationCrudController;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Symfony\Component\Validator\Constraints\Regex;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;

use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\Form\{FormBuilderInterface, FormEvent};

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\Filter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, EmailField, TextField};
use Symfony\Component\Form\Extension\Core\Type\{PasswordType, RepeatedType};
use EasyCorp\Bundle\EasyAdminBundle\Config\{Action, Actions, Crud, KeyValueStore};


class UserCrudController extends AbstractCrudController
{
    private Security $security;
    public function __construct(
        public UserPasswordHasherInterface $userPasswordHasher,
        Security $security
    ) {
        $this->security = $security;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }


    public function configureCrud(Crud $crud): Crud {
        return $crud
            ->overrideTemplate('crud/new', 'user/new.html.twig')
            ->overrideTemplate('crud/edit', 'user/edit.html.twig')
            ->setPageTitle(Crud::PAGE_INDEX, 'Membres')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le Membre')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un Membre')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du Membre')
            ->setSearchFields(null);
            $rolesFilter = $this->getContext()->getRequest()->query->get('roles');
            if ($rolesFilter) {
                $crud->setDefaultSort(['roles' => $rolesFilter]);
            }
    }

    public function configureActions(Actions $actions): Actions {
        $actions = parent::configureActions($actions);
    
        // Désactiver toutes les actions si l'utilisateur n'a pas les rôles nécessaires
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SENIOR') && !$this->isGranted('ROLE_APPRENTI')) {
            $actions->disable(Action::DETAIL);
            $actions->disable(Action::INDEX);
            $actions->disable(Action::NEW);
            $actions->disable(Action::EDIT);
            $actions->disable(Action::DELETE);
            throw new AccessDeniedException('Accès refusé. Vous n\'avez pas les droits nécessaires pour accéder à cette page.');
        }
    
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [  
            IdField::new('id')->hideOnForm(),
            EmailField::new('email'),
            TextField::new('name'),
            TextField::new('firstname'),
            TextField::new('street', 'Rue')
            ->setFormTypeOption('attr', ['class' => 'adresse-autocomplete']),
            TextField::new('zipcode', 'Code Postal')
            ->setFormTypeOption('attr', ['class' => 'zipcode_ope']),
            TextField::new('city', 'Ville')
            ->setFormTypeOption('attr', ['class' => 'city_ope']),
            TextField::new('phone'),
            ChoiceField::new('singleRole', 'Role')
                ->setChoices([
                    'Admin' => 'ROLE_ADMIN',
                    'Senior' => 'ROLE_SENIOR',
                    'Apprenti' => 'ROLE_APPRENTI',
                    'Client' => 'ROLE_CUSTOMER'
                ]),
    ];

        $password = TextField::new('password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => '(Repeat)'],
                'mapped' => false,
                'constraints' => [
                    new Regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', "Il faut un mot de passe de 8 caractères, une majuscule et un chiffre")
                ]
            ])
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms()
            ;
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

public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
{
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $entityAlias = $qb->getRootAliases()[0];

        $request = $this->container->get('request_stack')->getCurrentRequest();
        $userType = $request->query->get('userType');

        if ($userType === 'customer') {
            $qb->andWhere($entityAlias . '.roles LIKE :role')
               ->setParameter('role', '%"ROLE_CUSTOMER"%');
        } elseif ($userType === 'employee') {
            $qb->andWhere($entityAlias . '.roles LIKE :roleAdmin OR ' . $entityAlias . '.roles LIKE :roleSenior OR ' . $entityAlias . '.roles LIKE :roleApprenti')
               ->setParameter('roleAdmin', '%"ROLE_ADMIN"%')
               ->setParameter('roleSenior', '%"ROLE_SENIOR"%')
               ->setParameter('roleApprenti', '%"ROLE_APPRENTI"%');
        }

        return $qb;
    }
}
