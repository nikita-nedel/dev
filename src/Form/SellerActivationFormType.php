<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SellerActivationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'mapped' => false,
            'first_options' => [
                'label' => 'Пароль',
                'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password'],
            ],
            'second_options' => [
                'label' => 'Повторите пароль',
                'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password'],
            ],
            'constraints' => [
                new NotBlank(message: 'Введите пароль'),
                new Length(min: 8, max: 4096),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
