<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Form;

use App\Catalog\Domain\Model\Product;
use App\Catalog\Presentation\Validation\ValidSku;
use App\Seller\Domain\Model\Shop;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;

final class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shop', ChoiceType::class, [
                'label' => 'Магазин',
                'choices' => $options['shops'],
                'choice_label' => static fn (Shop $shop): string => $shop->getName(),
                'choice_value' => static fn (Shop $shop): string => (string) $shop->getId(),
                'disabled' => $options['shopLocked'],
                'attr' => ['class' => 'admin-select'],
                'constraints' => [new NotBlank(message: 'Выберите магазин')],
            ])
            ->add('name', TextType::class, [
                'label' => 'Название',
                'attr' => [
                    'class' => 'admin-input',
                    'placeholder' => 'Например, хлопковая футболка oversize',
                ],
                'constraints' => [
                    new NotBlank(message: 'Укажите название'),
                    new Length(max: 200),
                ],
            ])
            ->add('sku', TextType::class, [
                'label' => 'Артикул (SKU)',
                'attr' => ['class' => 'admin-input', 'placeholder' => 'CL-001'],
                'constraints' => [
                    new NotBlank(message: 'Укажите артикул'),
                    new ValidSku(),
                ],
            ])
            ->add('price', TextType::class, [
                'label' => 'Цена, ' . Product::CURRENCY,
                'attr' => [
                    'class' => 'admin-input',
                    'inputmode' => 'decimal',
                    'placeholder' => '0.00',
                ],
                'constraints' => [
                    new NotBlank(message: 'Укажите цену'),
                    new Regex(
                        pattern: '/^\d{1,7}(\.\d{1,2})?$/',
                        message: 'Цена в формате 12.99',
                    ),
                    new Positive(message: 'Цена должна быть больше нуля'),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
                'required' => false,
                'attr' => ['class' => 'admin-input', 'rows' => 5],
                'constraints' => [new Length(max: Product::DESCRIPTION_MAX_LENGTH)],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ProductFormData::class,
                'shops' => [],
                'shopLocked' => false,
            ])
            ->setAllowedTypes('shops', 'array')
            ->setAllowedTypes('shopLocked', 'bool');
    }
}
