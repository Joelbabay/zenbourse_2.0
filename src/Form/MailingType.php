<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MailingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => 'Sujet de l\'email',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le sujet est obligatoire'])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Objet'
                ]
            ])
            ->add('textContent', TextareaType::class, [
                'label' => 'Contenu du message',
                'required' => false, // Désactiver required HTML5 car CKEditor cache le champ
                'constraints' => [
                    new NotBlank(['message' => 'Le contenu est obligatoire'])
                ],
                'attr' => [
                    'class' => 'form-control ckeditor',
                    'rows' => 15,
                    'placeholder' => 'Saisissez votre message...'
                ],
                'help' => 'Utilisez l\'éditeur pour formater votre message'
            ])
            ->add('recipientType', ChoiceType::class, [
                'label' => 'Destinataires',
                'choices' => [
                    'Tous les utilisateurs' => 'all',
                    'Clients' => 'client',
                    'Prospects' => 'prospect',
                    'Invités' => 'invite',
                    'Utilisateurs avec accès Investisseur' => 'investisseur',
                    'Utilisateurs avec accès Intraday' => 'intraday',
                    'Utilisateurs avec accès temporaire actif' => 'temporary_active',
                    'Email spécifique (test)' => 'test'
                ],
                'required' => true,
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'recipient-type-checkboxes'
                ],
                'help' => 'Cochez les cases correspondant aux types de destinataires souhaités. "Tous les utilisateurs" et "Email spécifique (test)" ne peuvent pas être combinés avec d\'autres options.'
            ])
            ->add('testEmail', EmailType::class, [
                'label' => 'Email de test',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'votre-email@example.com'
                ],
                'help' => 'Utilisé uniquement si "Email spécifique (test)" est sélectionné'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'allow_extra_fields' => true, // Autoriser les champs supplémentaires (comme ceux ajoutés par CKEditor)
        ]);
    }
}
