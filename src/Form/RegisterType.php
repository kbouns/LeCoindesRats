<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('Username', TextType::class, [
            'label' => 'Ton surnom :',
            'attr' => [
                'placeholder' => 'Veuillez renseigner votre Tag/surnom!'
            ]
        ])
        ->add('firstname', TextType::class, [
            'label' => 'Prénom :',
            'attr' => [
                'placeholder' => 'Veuillez renseigner votre prénom !'
            ]
        ])
        ->add('lastname', TextType::class,[
            'label' => 'Nom :',
            'attr' => [
            'placeholder' => "Veuillez renseigner votre nom. !"
        ]
    ])
        ->add('email', EmailType::class,[
            'attr' => [
                'placeholder' => "Veuillez renseigner votre email. !"
            ]
        ])
        ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options'  => ['label' => 'Mot de passe :', 'hash_property_path' => 'password'],
            'second_options' => ['label' => 'Valider une 2eme fois votre mdp :'],
            'mapped' => false,
        ])
            ->add('InscriptionTime', null, [
                'label' => "Jour d'inscription :",
                'widget' => 'single_text',
            ])
            ->add('acceptCharte', CheckboxType::class, [
                'label' => "J'accepte la charte numérique",
                'mapped' => false, // ce champ n'est pas mappé directement à l'entité User
                'required' => true, // utilisateur doit cocher pour pouvoir soumettre
            ])
            ->add('submit',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [
                new UniqueEntity([
                    'entityClass' => User::class,
                    'fields' =>'email'
                ])
            ],
            'data_class' => User::class,
        ]);
    }
}
