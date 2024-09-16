<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('civility', ChoiceType::class, [
                'label' => 'Civilité',
                'placeholder' => 'Mr, Mme, Mlle',
                'required' => true,
                'choices'  => [
                    'Mr' => 'Mr',
                    'Mme' => 'Mme',
                    'Mlle' => 'Mlle',
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez votre nom',
                    'class' => 'required',
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
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'placeholder' => 'Entrez votre message',
                    'cols' => 50,
                    'rows' => 6,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer mon message',
                'attr' => [
                    'class' => 'btn btn-light btn-block zen-button text-light btn-lg w-100 mt-4'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
