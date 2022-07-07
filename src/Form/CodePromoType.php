<?php

namespace App\Form;

use App\Entity\CodePromo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CodePromoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($builder->getData()->getId()) {
            $codepromo = $builder->getData();
            $startsAt = $codepromo->getStartsAt();
            $expiresAt = $codepromo->getExpiresAt();
        } else {
            $startsAt = new \DateTime();
            $expiresAt = new \DateTime();
            $expiresAt->add(new \DateInterval('P1M'));
        }
        
        $codePromoGenerated = $this->generateRandomString();
        
        $builder
            ->add('code', TextType::class, [
                'data' => $codePromoGenerated,
                'label' => 'Code (Modifiable)'
            ])
            ->add('startsAt',DateType::class,[
                'widget' => 'single_text',
                    // this is actually the default format for single_text
                'format' => 'yyyy-MM-dd',
                'data' => $startsAt,
                'label' => 'Commence le'
            ])
            ->add('expiresAt',DateType::class,[
                'widget' => 'single_text',
                    // this is actually the default format for single_text
                'format' => 'yyyy-MM-dd',
                'data' => $expiresAt,
                'label' => 'Expire le'
            ])
            ->add('minPrice', NumberType::class, [
                'label' =>'Prix minimum pour pouvoir appliquer une promotion',
                'attr' => ['step' => '0.01']
            ])
            ->add('isEnabled', ChoiceType::class, [
                'choices'  => [
                    'Oui' => true,
                    'Non' => false,
                ], 
                'label' =>'Activé ?'
            ])
            ->add('percent',IntegerType::class,[
                'label' => 'Pourcentage %',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CodePromo::class,
        ]);
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
