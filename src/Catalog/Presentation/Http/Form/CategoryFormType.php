<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Form;

use App\Catalog\Domain\Model\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Regex;

final class CategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['withParent']) {
            $builder->add('parent', ChoiceType::class, [
                'label' => 'Родительская категория',
                'choices' => $options['parents'],
                'choice_label' => static fn (Category $category): string => $category->getName(),
                'choice_value' => static fn (?Category $category): string => null === $category
                    ? ''
                    : (string) $category->getId(),
                'placeholder' => '— Корневая категория —',
                'required' => false,
                'attr' => ['class' => 'admin-select'],
            ]);
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'Название',
                'attr' => ['class' => 'admin-input', 'placeholder' => 'Например, Умный дом'],
                'constraints' => [
                    new NotBlank(message: 'Укажите название категории'),
                    new Length(max: Category::NAME_MAX_LENGTH),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
                'required' => false,
                'attr' => ['class' => 'admin-input', 'rows' => 4],
            ])
            ->add('icon', TextType::class, [
                'label' => 'Иконка (Bootstrap Icons)',
                'required' => false,
                'attr' => ['class' => 'admin-input', 'placeholder' => 'bi-phone'],
                'constraints' => [
                    new Regex(
                        pattern: '/^bi-[a-z0-9-]+$/',
                        message: 'Класс иконки должен выглядеть как bi-phone',
                    ),
                ],
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Порядок сортировки',
                'required' => false,
                'empty_data' => '0',
                'attr' => ['class' => 'admin-input'],
                'constraints' => [new PositiveOrZero(message: 'Порядок не может быть отрицательным')],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => CategoryFormData::class,
                'parents' => [],
                'withParent' => true,
            ])
            ->setAllowedTypes('parents', 'array')
            ->setAllowedTypes('withParent', 'bool');
    }
}
