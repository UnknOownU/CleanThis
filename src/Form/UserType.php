<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles')
            ->add('password')
            ->add('name')
            ->add('firstname')
            ->add('zipcode')
            ->add('city')
            ->add('street')
            ->add('phone')
        ;
        $builder->get('roles')

    ->addModelTransformer(new CallbackTransformer(
        fn ($rolesAsArray) => count($rolesAsArray) ? $rolesAsArray[0]: null,
        fn ($rolesAsString) => [$rolesAsString]
));

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
