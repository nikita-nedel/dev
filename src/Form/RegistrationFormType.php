<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Имя',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Иван',
                    'minlength' => 2,
                    'maxlength' => 50,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите имя',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Имя должно содержать минимум {{ limit }} символа',
                        'maxMessage' => 'Имя не должно превышать {{ limit }} символов',
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Фамилия',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Иванов',
                    'minlength' => 2,
                    'maxlength' => 50,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите фамилию',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Фамилия должна содержать минимум {{ limit }} символа',
                        'maxMessage' => 'Фамилия не должна превышать {{ limit }} символов',
                    ]),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Номер телефона',
                'attr' => [
                    'class' => 'form-control phone-input',
                    'placeholder' => 'phone',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите номер телефона',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'your@email.com',
                    'autocomplete' => 'email',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите email',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Пароль',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Не менее 8 символов',
                    'autocomplete' => 'new-password',
                    'minlength' => 8,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите пароль',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Пароль должен содержать минимум {{ limit }} символов',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Я принимаю условия использования и согласен с политикой конфиденциальности',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Вы должны принять условия использования.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}