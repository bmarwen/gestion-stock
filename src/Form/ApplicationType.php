<?php

namespace App\Form;

use App\Entity\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class,[
                'choices'  => [
                    'Image' => 'image',
                    'Information' => 'information',
                ],
            ])
            ->add('name', TextType::class,[
                'label' => 'Nom (format: type_page_pageposition_placement_order)'
            ])
            ->add('value', TextType::class,[
                'label' => 'Valeur / Déscription'
            ])
            ->add('redirectTo', TextType::class,[
                'label' => 'Url de redirection (après le nom du site, exemple: pour la page d\'accueil il faut mettre / pour une page de produit il faut mettre /produits/numero)',
                'required' => false
            ])
            ->add('imageFile', FileType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Application::class,
        ]);
    }
}
