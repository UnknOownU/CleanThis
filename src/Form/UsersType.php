<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer; // a jouter par abdel
class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles')
            ->add('password')
            ->add('lastname')
            ->add('firstname')
            ->add('street')
            ->add('zipcode')
            ->add('city')
            ->add('phone')

  
          ;
          $builder->get('roles')// a jouter par abdel

          ->addModelTransformer(new CallbackTransformer(
              fn ($rolesAsArray) => count($rolesAsArray) ? $rolesAsArray[0]: null,
              fn ($rolesAsString) => [$rolesAsString]
      )); // fin ajout 
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
