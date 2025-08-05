<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 20,
                        'maxMessage' => 'Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'La ville ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 10,
                        'maxMessage' => 'Le code postal ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'Le pays ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
