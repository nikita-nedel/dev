<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminInviteRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Имя',
                'attr' => ['class' => 'admin-input', 'placeholder' => 'Иван'],
                'constraints' => [new NotBlank(message: 'Введите имя')],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Фамилия',
                'attr' => ['class' => 'admin-input', 'placeholder' => 'Иванов'],
                'constraints' => [new NotBlank(message: 'Введите фамилию')],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Пароль',
                    'attr' => ['class' => 'admin-input', 'autocomplete' => 'new-password', 'placeholder' => 'Не менее 8 символов'],
                ],
                'second_options' => [
                    'label' => 'Повторите пароль',
                    'attr' => ['class' => 'admin-input', 'autocomplete' => 'new-password', 'placeholder' => 'Повторите пароль'],
                ],
                'constraints' => [
                    new NotBlank(message: 'Введите пароль'),
                    new Length(min: 8, max: 4096, minMessage: 'Пароль должен содержать минимум {{ limit }} символов'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
