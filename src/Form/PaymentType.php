<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', TextType::class, [
				'label' => 'Nom du titulaire de la carte',
			])
			->add('cardNumber', IntegerType::class, [
				'label' => 'Numéro de carte',
			])
			->add('expirationMonth', ChoiceType::class, [
				'label' => 'Mois d\'expiration',
				'choices' => [
					'01' => '01',
					'02' => '02',
					'03' => '03',
					'04' => '04',
					'05' => '05',
					'06' => '06',
					'07' => '07',
					'08' => '08',
					'09' => '09',
					'10' => '10',
					'11' => '11',
					'12' => '12',
				],
			])
			->add('expirationYear', IntegerType::class, [
				'label' => 'Année d\'expiration',
			])
			->add('cvc', IntegerType::class, [
				'label' => 'Code de sécurité (CVC)',
			])
			->add('submit', SubmitType::class, [
				'label' => 'Payer',
			]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => PaymentFormData::class,
			]);
	}
}
