<?php

namespace App\Form;

use App\Entity\Download;
//use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DownloadRequestType extends AbstractType
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
                ]
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
                    'class' => 'required',
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Entrez votre email',
                    'class' => 'required',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Je téléharge la liste des valeurs de l’étude statistique',
                'attr' => [
                    'class' => 'btn btn-light zen-button text-light mt-4 w-100',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Download::class,
        ]);
    }
}
