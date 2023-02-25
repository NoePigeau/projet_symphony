<?php
	
	namespace App\Form;
	
	use App\Entity\Rating;
	use Symfony\Component\Form\AbstractType;
	use Symfony\Component\Form\FormBuilderInterface;
	use Symfony\Component\OptionsResolver\OptionsResolver;
	use Symfony\Component\Form\Extension\Core\Type\NumberType;
	use Symfony\Component\Form\Extension\Core\Type\TextareaType;
	
	class RatingType extends AbstractType
	{
		public function buildForm(FormBuilderInterface $builder, array $options)
		{
			$builder
					->add('rate', NumberType::class, [
						'attr' => [
							'min' => 0,
							'max' => 5
						],
					])
					->add('opinion', TextareaType::class, [
						'label' => 'Opinion'
					])
//					->add('agent', EntityType::class, [
//						'class' => User::class,
////							'hidden' => true,
//						'label' => 'Agent',
//						'choice_label' => 'nickname',
//						'query_builder' => function (UserRepository $us) {
//							return $us->findByRole('ROLE_AGENT');
//						}
//					])
			;
		}
		
		public function configureOptions(OptionsResolver $resolver)
		{
			$resolver->setDefaults([
				'data_class' => Rating::class
            ]);
		}
	}