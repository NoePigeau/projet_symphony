<?php

namespace App\Form;

use App\Entity\Equipment;
use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('equipment', ChoiceType::class, [
                'choices' => $options['equipments'],
                'choice_value' => 'id',
                'choice_label' => function (?Equipment $equipment) {
                    return $equipment ? strtoupper($equipment->getName()) : '';
                },
                ])
            ->add('amount', IntegerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'equipments' => []
        ]);
    }
}
