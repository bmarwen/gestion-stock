<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastname',TextType::class,[
                'required' => false, 'label' => 'Nom'
            ])
            ->add('firstname',TextType::class,[
                'required' => false, 'label' => 'Prénom'
            ])
            ->add('email', TextType::class, [
                'required' => false, 'label' => 'Email', 'attr' => ['pattern' => "[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"]
            ])
            ->add('address',TextType::class,[
                'required' => false, 'label' => 'Adresse', 'attr' => ['maxlength' => 100]
            ])
            ->add('telephone',TextType::class,[
                'required' => false, 'label' => 'Téléphone', 'attr' => ['pattern' => '[0-9]+', 'maxlength' => 8]
            ])
            ->add('birthday', BirthdayType::class, [
                'required' => false, 'label' => 'Anniversaire', 'placeholder' => [
                    'year' => 'Année', 'month' => 'Mois', 'day' => 'Jour',
                ], 'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
