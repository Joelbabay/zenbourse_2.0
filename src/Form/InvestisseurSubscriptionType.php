<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class InvestisseurSubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $existingUser = $options['existing_user'];

        if ($existingUser) {
            $builder
                ->add('civility', ChoiceType::class, [
                    'label' => 'Civilité',
                    'placeholder' => 'Sélectionnez une option',
                    'required' => false,
                    'choices'  => [
                        'Mr' => 'Mr',
                        'Mme' => 'Mme',
                        'Mlle' => 'Mlle',
                    ]
                ])
                ->add('firstname', TextType::class, [
                    'label' => 'Nom',
                    'attr' => [
                        'placeholder' => 'Entrez votre nom',
                    ]
                ])
                ->add('lastname', TextType::class, [
                    'label' => 'Prénom',
                    'attr' => [
                        'placeholder' => 'Entrez votre prenom',
                    ]
                ])
                ->add('address', TextType::class, [
                    'label' => 'Adresse',
                    'attr' => [
                        'placeholder' => 'Entrez votre adresse',
                    ]
                ])
            ;
        }

        if (!$existingUser) {
            $builder
                ->add('civility', ChoiceType::class, [
                    'label' => 'Civilité',
                    'placeholder' => 'Sélectionnez une option',
                    'required' => false,
                    'choices'  => [
                        'Mr' => 'Mr',
                        'Mme' => 'Mme',
                        'Mlle' => 'Mlle',
                    ]
                ])
                ->add('firstname', TextType::class, [
                    'label' => 'Nom',
                    'attr' => [
                        'placeholder' => 'Entrez votre nom',
                    ]
                ])
                ->add('lastname', TextType::class, [
                    'label' => 'Prénom',
                    'attr' => [
                        'placeholder' => 'Entrez votre prenom',
                    ]
                ])
                ->add('email', EmailType::class, [
                    'label' => 'Email',
                    'attr' => [
                        'placeholder' => 'Entrez votre email',
                    ]
                ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'S\'abonner à la méthode investisseur',
            'attr' => [
                'class' => 'btn btn-light btn-block zen-button text-light btn-lg w-100 mt-4'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'existing_user' => false,
        ]);

        $resolver->setAllowedTypes('existing_user', 'bool');
    }
}
