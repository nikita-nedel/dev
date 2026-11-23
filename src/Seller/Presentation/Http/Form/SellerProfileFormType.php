<?php

declare(strict_types=1);

namespace App\Seller\Presentation\Http\Form;

use App\Seller\Application\Dto\SellerProfileData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class SellerProfileFormType extends AbstractType
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
            ])
            ->add('shopName', TextType::class, [
                'label' => 'Название магазина',
                'attr' => [
                    'class' => 'admin-input',
                    'placeholder' => 'Мой магазин',
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new NotBlank(message: 'Укажите название магазина'),
                    new Length(max: 255),
                ],
            ])
            ->add('legalName', TextType::class, [
                'label' => 'Юридическое название',
                'required' => false,
                'attr' => [
                    'class' => 'admin-input',
                    'placeholder' => 'ООО «Пример»',
                    'maxlength' => 255,
                ],
                'constraints' => [new Length(max: 255)],
            ])
            ->add('inn', TextType::class, [
                'label' => 'ИНН',
                'required' => false,
                'attr' => [
                    'class' => 'admin-input',
                    'placeholder' => '10 или 12 цифр',
                    'maxlength' => 12,
                    'inputmode' => 'numeric',
                ],
                'constraints' => [
                    new Regex(
                        pattern: '/^(?:\d{10}|\d{12})?$/',
                        message: 'ИНН должен состоять из 10 или 12 цифр',
                    ),
                ],
            ])
            ->add('contactPhone', TelType::class, [
                'label' => 'Телефон магазина',
                'required' => false,
                'attr' => [
                    'class' => 'admin-input',
                    'placeholder' => '+7 (812) 000-00-00',
                    'maxlength' => 255,
                ],
                'constraints' => [new Length(max: 255)],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание магазина',
                'required' => false,
                'attr' => [
                    'class' => 'admin-input',
                    'rows' => 4,
                    'placeholder' => 'Что вы продаёте, чем отличаетесь, какие условия доставки?',
                ],
                'constraints' => [new Length(max: 2000)],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SellerProfileData::class,
        ]);
    }
}
