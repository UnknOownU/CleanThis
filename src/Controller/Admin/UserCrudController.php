<?php
// src/Controller/Admin/UserCrudController.php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\OperationCrudController;
use App\Entity\Operation;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;


class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // other configurations...
            ->setSearchFields(null) // Disable search for ROLE_USER if desired
            ->setPaginatorPageSize(1); // Assuming ROLE_USER can only see their profile
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if ($this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_ADMIN')) {
            // Assurez-vous que l'utilisateur a seulement le rôle ROLE_USER et pas ROLE_ADMIN
            $qb->andWhere('entity.id = :id')
                ->setParameter('id', $this->getUser()->getId());
        }

        return $qb;
    }
    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        // Retirer les actions de suppression et de modification pour les utilisateurs avec le rôle ROLE_USER
        if ($this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_ADMIN')) {
            $actions->disable(Action::NEW, Action::EDIT, Action::DELETE);
        }

        return $actions;
    }

}
