<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Command;
use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('client',EntityType::class, [
                'class' => Client::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c');
                },
            ])
            ->add('comment',TextareaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
            'translation_domain' => 'forms'
        ]);
    }
}
