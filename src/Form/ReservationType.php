<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Vehicule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('created_at', null, [
            'widget' => 'single_text',
        ]);

        $builder->add('ended_at', null, [
            'widget' => 'single_text',
        ]);

        // Si un véhicule est pré-sélectionné, ne pas afficher le champ "vehicule"
        if (!$options['vehicule_fixed']) {
            $builder->add('vehicule', EntityType::class, [
                'class' => Vehicule::class,
                'choice_label' => 'modele',
                'placeholder' => 'Sélectionnez un véhicule',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'vehicule_fixed' => false, // Par défaut, le champ "vehicule" est affiché
        ]);

        $resolver->setAllowedTypes('vehicule_fixed', 'bool');
    }
}
