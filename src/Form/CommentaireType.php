<?php

namespace App\Form;

use App\Entity\Commentaire;
use App\Entity\Vehicule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('contenu');
        $builder->add('note');

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
            'data_class' => Commentaire::class,
            'vehicule_fixed' => false, // Valeur par défaut : le champ véhicule est affiché
        ]);

        $resolver->setAllowedTypes('vehicule_fixed', 'bool');
    }
}
