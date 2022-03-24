<?php

namespace App\Form;

use App\Entity\CommandOnLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandOnLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('address')
            ->add('addressComplement',TextType::class, [
                'required' => false,
            ])
            ->add('phone',TextType::class,[
                'required' => true, 'attr' => ['pattern' => '/^[0-9]{8}$/', 'maxlength' => 8]
            ])
            ->add('email',TextType::class, [
                'required' => false, 'attr' => ['pattern' => "[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"]
            ])
            ->add('moreDetails',TextareaType::class,[
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommandOnLine::class,
        ]);
    }
}
