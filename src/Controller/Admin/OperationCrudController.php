<?php

namespace App\Controller\Admin;

use App\Entity\Operation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use App\Controller\Admin\EntityDto;
use App\Controller\Admin\FieldCollection;
use App\Controller\Admin\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use phpDocumentor\Reflection\Types\Integer;

class OperationCrudController extends AbstractCrudController {
    public static function getEntityFqcn(): string {
        return Operation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // other configurations...
            ->setSearchFields(null) // Disable search for ROLE_USER if desired
            ->setPaginatorPageSize(1); // Assuming ROLE_USER can only see their profile
    }

        public function createEntity(string $entityFqcn) {
            $operation = new Operation();
    
            // Assumant que vous avez une relation entre Operation et User
            // et que vous avez un setter `setCustomer` dans votre entité Operation
            $operation->setCustomer($this->getUser());
    
            return $operation;
        }
    
        public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void {
            // Assumant que $entityInstance est une instance de Operation
            // Si vous souhaitez mettre à jour le client à chaque modification, décommentez la ligne suivante
            // $entityInstance->setCustomer($this->getUser());
        
            parent::updateEntity($entityManager, $entityInstance);
        }
        public function configureFields(string $pageName): iterable
        {
            return [
                FormField::addTab('Mission'),
                FormField::addColumn('col-lg-8 col-xl-3'),
    
                IdField::new('id')->hideOnForm(),
                AssociationField::new('customer')->hideOnForm(),
                

                TextField::new('name')
                ->setLabel('Mission'),

                ChoiceField::new('type')->setChoices([
                    'Little' => 'Little',
                    'Middle' => 'Middle',
                    'Big' => 'Big',
                ])->hideOnIndex(),
                TextField::new('type')->hideOnForm(),

                MoneyField::new('price')->setCurrency('EUR'),
    
                FormField::addColumn('col-lg-4 col-xl-4'),
                
                DateTimeField::new('rdv_at')->setLabel('When?'),
                DateTimeField::new('created_at')->hideOnForm(),
    
                FormField::addColumn('col-lg-3 col-xl-6'),
                TextEditorField::new('description')->hideOnIndex(),
                

                TextField::new('status'),
                ChoiceField::new('status')->setChoices([
                    'En Attente de Validation' => 'En Attente de Validation',
                    'Validée' => 'Validée',
                    'En cours' => 'En cours',
                    'Terminée' => 'Terminée',
                ])->hideOnIndex(),
                // ChoiceField::new('status')->renderAsBadges([
                //     // $value => $badgeStyleName
                //     'paid' => 'success',
                //     'pending' => 'warning',
                //     'refunded' => 'danger',
                // ]),
                // IntegerField::new('preparation')
                // ->setLabel('Time of mission'),
                // CollectionField::new('steps')
                //     ->setEntryType(StepType::class)
                //     ->allowDelete()
                //     ->allowAdd(),
                // CollectionField::new('images')
                //     ->setEntryType(ImageType::class)
                //     ->allowDelete()
                //     ->allowAdd(),
    
                TextField::new('street_ope'),
                TextField::new('city_ope'),
                TextField::new('zipcode_ope'),
    
            ];
        }
    }