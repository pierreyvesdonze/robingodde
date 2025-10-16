<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => false,
                'attr'  => [
                    'class'       => 'uk-input',
                    'placeholder' => 'Titre du projet'
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le nom du projet ne peut pas dépasser 255 caractères.',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'class'       => 'uk-input',
                    'placeholder' => 'Description du projet',
                ]
            ])
            ->add('mainImage', FileType::class, [
                'label' => 'Image principale',
                'mapped' => false, // Si tu gères l'upload dans le contrôleur
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF, WEBP).',
                    ])
                ],
            ])
            ->add('images', FileType::class, [
                'label' => 'Autres images',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '5M',
                                'mimeTypes' => [
                                    'image/jpeg',
                                    'image/png',
                                    'image/gif',
                                    'image/webp',
                                ],
                                'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF, WEBP).',
                            ])
                        ]
                    ])
                ],
            ])
              ->add('date', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'class'       => 'uk-input',
                    'placeholder' => "L'année du projet",
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
