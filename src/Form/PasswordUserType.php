<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\FormError;

class PasswordUserType extends AbstractType
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('actualPassword', PasswordType::class, [
                'mapped' => false,
                'label' => "Votre mdp actuel :"
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe :'],
                'second_options' => ['label' => 'Valider une 2eme fois votre mdp :'],
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Changer le mot de passe'
            ])
            ->addEventListener(FormEvents::SUBMIT, function (SubmitEvent $event) {
                $form = $event->getForm();
                $user = $form->getData();


                if (!$user instanceof PasswordAuthenticatedUserInterface) {

                    $form->addError(new FormError('User entity is not valid.'));
                    return;
                }

                $actualPwd = $form->get('actualPassword')->getData();
                $newPwd = $form->get('plainPassword')->getData();

                if ($this->passwordHasher->isPasswordValid($user, $actualPwd)) {
       
                    $hashedNewPwd = $this->passwordHasher->hashPassword($user, $newPwd);

                    $user->setPassword($hashedNewPwd);
                } else {

                    $form->get('actualPassword')->addError(new FormError('Current password is incorrect.'));
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
