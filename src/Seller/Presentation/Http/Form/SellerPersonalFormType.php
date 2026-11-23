<?php

declare(strict_types=1);

namespace App\Seller\Presentation\Http\Form;

use App\Seller\Application\Dto\SellerPersonalData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SellerPersonalFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Имя',
                'attr' => [
                    'class' => 'admin-input',
                    'placeholder' => 'Иван',
                    'autocomplete' => 'given-name',
                    'maxlength' => 50,
                ],
                'constraints' => [
                    new NotBlank(message: 'Укажите имя'),
                    new Length(min: 2, max: 50, minMessage: 'Имя должно быть не короче {{ limit }} символов'),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Фамилия',
                'attr' => [
                    'class' => 'admin-input',
                    'placeholder' => 'Петров',
                    'autocomplete' => 'family-name',
                    'maxlength' => 50,
                ],
                'constraints' => [
                    new NotBlank(message: 'Укажите фамилию'),
                    new Length(min: 2, max: 50, minMessage: 'Фамилия должна быть не короче {{ limit }} символов'),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Телефон',
                'attr' => [
                    'class' => 'admin-input',
                    'placeholder' => '+375 (29) 123-45-67',
                    'autocomplete' => 'tel',
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new NotBlank(message: 'Укажите номер телефона'),
                    new Length(max: 255),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SellerPersonalData::class,
        ]);
    }
}
