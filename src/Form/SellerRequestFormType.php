<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\SellerRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SellerRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shopName', TextType::class, [
                'label' => 'Название магазина',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Мой магазин'],
                'constraints' => [new NotBlank(message: 'Укажите название магазина')],
            ])
            ->add('legalName', TextType::class, [
                'label' => 'Юридическое название',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'ООО Пример'],
            ])
            ->add('inn', TextType::class, [
                'label' => 'ИНН',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '1234567890'],
                'constraints' => [new Length(max: 12)],
            ])
            ->add('contactPhone', TelType::class, [
                'label' => 'Контактный телефон',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание магазина',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SellerRequest::class,
        ]);
    }
}
