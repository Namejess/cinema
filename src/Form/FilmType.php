<?php

namespace App\Form;

use App\Entity\Film;
use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class FilmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Titre', TextType::class, array('required' => true, 'label' => false))
            ->add('Description', CKEditorType::class, array('required' => true, 'label' => false))
            ->add('Image', FileType::class,[
                'required' => false,
                'label' => false,
                'data_class' => null,
                'empty_data' => 'null',
            ])
            ->add('Categorie', EntityType::class, [
                'class' => Categorie::class,
                'label' =>false,
                'required' =>true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Film::class,
        ]);
    }
}
