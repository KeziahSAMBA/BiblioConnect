<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Livre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LivreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [new NotBlank(message: 'Le titre est obligatoire')]
            ])
            ->add('auteur', TextType::class, [
                'constraints' => [new NotBlank(message: "L'auteur est obligatoire")]
            ])
            ->add('langue', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('image', FileType::class, [
                'required' => false,
                'mapped' => false, // géré manuellement dans le controller
                'label' => 'Image du livre'
            ])
            ->add('stock', IntegerType::class)
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom', // affiche le nom au lieu de l'id
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livre::class,
        ]);
    }
}