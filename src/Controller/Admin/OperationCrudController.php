<?php

namespace App\Controller\Admin;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Operation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Core\Security;

class OperationCrudController extends AbstractCrudController {
    private $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

    public static function getEntityFqcn(): string {
        return Operation::class;
    }

    public function configureCrud(Crud $crud): Crud {
        return $crud
            ->setSearchFields(null);
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

    public function configureFields(string $pageName): iterable {
        return [
            TextField::new('customerFullName', 'Nom Prénom ')
                ->formatValue(function ($value, $entity) {
                    return $entity->getCustomerFullName();
            }),
            ChoiceField::new('Type', 'type')->setChoices([
                'Petite' => 'little',
                'Moyenne' => 'medium',
                'Grande' => 'big',
                'Très Grande' => 'very_big',
                'Custom' => 'custom',
            ]),
            TextField::new('name', 'Nom de l’opération'),
            MoneyField::new('price', 'Prix')->setCurrency('EUR'),
            TextareaField::new('description', 'Description'),
            ChoiceField::new('status', 'Statut')->setChoices([
                'Envoyé' => 'sent',
                'En attente' => 'pending',
                'En cours' => 'in_progress',
                'Terminé' => 'finished',
                'Annulé' => 'canceled',
            ]),
            DateTimeField::new('created_at', 'Créé le'),
            DateTimeField::new('rdv_at', 'Rendez-vous le'),
            TextField::new('zipcode_ope', 'Code Postal'),
            TextField::new('city_ope', 'Ville'),
            TextField::new('street_ope', 'Rue'),
            DateTimeField::new('finished_at', 'Terminé le'),
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

        if ($user) {
            $qb->andWhere('entity.customer = :user')
               ->setParameter('user', $user);
        }

        return $qb;
    }
}
