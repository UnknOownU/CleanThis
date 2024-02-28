<?php

namespace App\Controller\Admin;

use App\Entity\Operation;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OperationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Operation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Missions')
            ->setEntityLabelInSingular('Mission')
            ->setPageTitle("index", "Administration des missions");
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // IdField::new('id'),
            ChoiceField::new('type')
            ->setChoices([
                'Petite' => 'Petite',
                'Moyenne' => 'Moyenne',
                'Grosse' => 'Grosse',
                'Custom' => 'Custom',
            ]),
            TextField::new('name'),
            TextField::new('description'),
            NumberField::new('price'),
            ChoiceField::new('status')
            ->setChoices([
                'En attente' => 'En attente',
                'En cours' => 'En cours',
                'Terminé' => 'Terminé',
                'Annulée' => 'Annulée',
            ]),
            DateField::new('created_at'),
            DateField::new('rdv_at'), 
            TextField::new('zipcode_ope'),
            TextField::new('city_ope'),
            TextField::new('street_ope'),   
            
            AssociationField::new('customer'),       
            AssociationField::new('employe'),                                     
        ];
    }
    public function createEntity(string $entityFqcn) {
        $operation = new Operation();

        $operation->setCustomer($this->getUser());
        $operation->setEmploye($this->getUser());

        return $operation;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        // Assumant que $entityInstance est une instance de Operation
        // Si vous souhaitez mettre à jour le client à chaque modification, décommentez la ligne suivante
        $entityInstance->setCustomer($this->getUser());

        parent::updateEntity($entityManager, $entityInstance);
    }

}
