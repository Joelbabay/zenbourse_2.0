<?php

namespace App\Form;

use App\Entity\Contact;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

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
                'attr' => [
                    'placeholder' => 'Choisir',
                    'required' => true,
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez votre nom',
                    'class' => 'required',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom est obligatoire',
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s]+$/',
                        'message' => 'Le nom ne peut contenir que des lettres',
                    ]),
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Entrez votre prénom',
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prénom est obligatoire',
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s]+$/',
                        'message' => 'Le prénom ne peut contenir que des lettres',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Entrez votre email',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'email est obligatoire']),
                    new Assert\Email(['message' => 'Email invalide']),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'placeholder' => 'Entrez votre message',
                    'cols' => 50,
                    'rows' => 6,
                ],
            ])
            // Honeypot (piège invisible pour les bots)
            ->add('website', TextType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'style' => 'display:none',
                    'tabindex' => '-1',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3([
                    'message' => 'La vérification anti-spam a échoué',
                ]),
                'action_name' => 'contact',
                'locale' => 'fr',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer mon message',
                'attr' => [
                    'class' => 'btn btn-light btn-block zen-button text-light w-100 mt-4'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
