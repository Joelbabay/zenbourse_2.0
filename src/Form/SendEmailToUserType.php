<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class SendEmailToUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => 'Objet',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'L\'objet est obligatoire']),
                    new Length([
                        'min' => 1,
                        'max' => 1000,
                        'minMessage' => 'L\'objet doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'L\'objet ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Ex: Confirmation de votre accès Investisseur',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le message est obligatoire']),
                    new Length([
                        'min' => 5,
                        'max' => 45000,
                        'minMessage' => 'Le message doit contenir au moins {{ limit }} caractères',
                    ]),
                ],
                'attr' => [
                    'rows' => 10,
                    'placeholder' => 'Votre message...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
