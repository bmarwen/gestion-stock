<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // date of expiration
        $date = new \DateTime();
        $date->add(new \DateInterval('P1Y'));

        $builder
            ->add('name')
            ->add('description')
            ->add('howMany')
            ->add('category')
            ->add('provider')
            ->add('bill',BillsType::class,[
                'required' => false
            ])
            ->add('code')
            ->add('mark')
            ->add('purchacePriceUnHt')
            ->add('tva')
            ->add('gain')
            ->add('expirationDate',DateType::class,[
                'widget' => 'single_text',
                    // this is actually the default format for single_text
                'format' => 'yyyy-MM-dd',
                'data' => $date
            ])
            ->add('imageFile', FileType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'translation_domain' => 'forms'
        ]);
    }
}
