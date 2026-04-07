<?php

namespace App\Form;

use App\Entity\Livre;
use App\Entity\Reservation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('dateDebut', DateType::class, [
            'label' => 'Date de début',
            'widget' => 'single_text',
            'input' => 'datetime',
            'attr' => [
                'min' => (new \DateTime('today'))->format('Y-m-d'),
            ],
        ]);

        if (!$options['livre_auto']) {
            $builder->add('livre', EntityType::class, [
                'class' => Livre::class,
                'choice_label' => 'titre',
                'placeholder' => 'Sélectionnez un livre',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'livre_auto' => false,
        ]);
        $resolver->setAllowedTypes('livre_auto', 'bool');
    }
}
