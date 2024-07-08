<?php

namespace App\Form;

use App\Entity\Deal;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class DealType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextType::class)
            ->add('initial_price', TextType::class)
            ->add('reduce_price', TextType::class)
            ->add('publication_date', DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('link', TextType::class)
            ->add('imageFilename', FileType::class, [
                'label' => 'Image (JPEG, PNG file, Webp)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, or Webp)',
                    ])
                ],
            ])
            ->add('Delivery', ChoiceType::class, [
                'choices' => [
                    'Livraison gratuite' => 'gratuite',
                    'Livraison payante' => 'payante',
                ],
                'placeholder' => 'Choisissez une option de livraison',
                'attr' => ['class' => 'form-select'] 
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Deal::class,
        ]);
    }
}
