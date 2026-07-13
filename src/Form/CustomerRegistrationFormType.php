<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Customer;
use App\Entity\CustomerProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Имя',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Иван',
                    'minlength' => 2,
                    'maxlength' => 50,
                ],
                'constraints' => [
                    new NotBlank(message: 'Пожалуйста, введите имя'),
                    new Length(min: 2, max: 50, minMessage: 'Имя должно содержать минимум {{ limit }} символа', maxMessage: 'Имя не должно превышать {{ limit }} символов'),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Фамилия',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Иванов',
                    'minlength' => 2,
                    'maxlength' => 50,
                ],
                'constraints' => [
                    new NotBlank(message: 'Пожалуйста, введите фамилию'),
                    new Length(
                        min: 2,
                        max: 50,
                        minMessage: 'Фамилия должна содержать минимум {{ limit }} символа',
                        maxMessage: 'Фамилия не должна превышать {{ limit }} символов'),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Номер телефона',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control phone-input',
                    'placeholder' => 'phone',
                ],
                'constraints' => [
                    new NotBlank(message: 'Пожалуйста, введите номер телефона'),
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
                    new NotBlank(message: 'Пожалуйста, введите email'),
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
                    new NotBlank(message: 'Пожалуйста, введите пароль'),
                    new Length(min: 8, max: 4096, minMessage: 'Пароль должен содержать минимум {{ limit }} символов'),
                ],
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            $customer = $event->getData();
            if (!$customer instanceof Customer) {
                return;
            }

            $form = $event->getForm();
            $profile = (new CustomerProfile())
                ->setFirstName((string) $form->get('firstName')->getData())
                ->setLastName((string) $form->get('lastName')->getData())
                ->setPhone((string) $form->get('phone')->getData());

            $customer->setProfile($profile);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
