<?php

namespace App\Form;

use App\Entity\InvestisseurRequest;
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
                    'placeholder' => 'Mr, Mme, Mlle',
                    'required' => true,
                    'choices'  => [
                        'Mr' => 'Mr',
                        'Mme' => 'Mme',
                        'Mlle' => 'Mlle',
                    ]
                ])
                ->add('lastname', TextType::class, [
                    'label' => 'Nom',
                    'attr' => [
                        'placeholder' => 'Entrez votre nom',
                    ]
                ])
                ->add('firstname', TextType::class, [
                    'label' => 'Prénom',
                    'attr' => [
                        'placeholder' => 'Entrez votre prénom',
                    ]
                ])
                ->add('email', EmailType::class, [
                    'label' => 'Email',
                    'attr' => [
                        'placeholder' => 'Entrez votre email',
                    ]
                ]);
        }

        if (!$existingUser) {
            $builder
                ->add('civility', ChoiceType::class, [
                    'label' => 'Civilité',
                    'placeholder' => 'Mr, Mme, Mlle',
                    'required' => true,
                    'choices'  => [
                        'Mr' => 'Mr',
                        'Mme' => 'Mme',
                        'Mlle' => 'Mlle',
                    ]
                ])
                ->add('lastname', TextType::class, [
                    'label' => 'Nom',
                    'attr' => [
                        'placeholder' => 'Entrez votre nom',
                    ]
                ])
                ->add('firstname', TextType::class, [
                    'label' => 'Prénom',
                    'attr' => [
                        'placeholder' => 'Entrez votre prénom',
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
            'label' => 'Je suis intéressé par la méthode Investisseur et souhaite être informé de sa disponibilité.',
            'attr' => [
                'class' => 'btn btn-light btn-block zen-button text-light w-100 mt-4'
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
