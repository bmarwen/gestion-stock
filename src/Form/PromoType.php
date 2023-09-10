<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Promo;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($builder->getData()->getId()) {
            $promo = $builder->getData();
            $startsAt = $promo->getStartsAt();
            $expiresAt = $promo->getExpiresAt();
        } else {
            $startsAt = new \DateTime();
            $expiresAt = new \DateTime();
            $expiresAt->add(new \DateInterval('P7D'));
        }

        $builder
            ->add('pourcent',IntegerType::class,[
                'label' => 'Pourcentage %',
            ])
            ->add('startsAt',DateTimeType::class,[
                'widget' => 'single_text',
                    // this is actually the default format for single_text
                'data' => $startsAt,
                'label' => 'Commence le',
            ])
            ->add('expiresAt',DateTimeType::class,[
                'widget' => 'single_text',
                    // this is actually the default format for single_text
                'data' => $expiresAt,
                'label' => 'Expire le',
            ])
            ->add('isEnabled', ChoiceType::class, [
                    'choices'  => [
                        'Oui' => true,
                        'Non' => false,
                    ], 
                    'label' =>'Activé ?'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Promo::class,
        ]);
    }
}
