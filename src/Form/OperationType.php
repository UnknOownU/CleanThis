<?php

namespace App\Form;

use App\Entity\Operation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type')
            ->add('name')
            ->add('price')
            ->add('description')
            ->add('status')
            ->add('created_at')
            ->add('rdv_at')
            ->add('zipcode_ope')
            ->add('city_ope')
            ->add('street_ope')
            ->add('customer', EntityType::class, [
                'class' => User::class,
'choice_label' => 'id',
            ])
            ->add('salarie', EntityType::class, [
                'class' => User::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Operation::class,
        ]);
    }
}
