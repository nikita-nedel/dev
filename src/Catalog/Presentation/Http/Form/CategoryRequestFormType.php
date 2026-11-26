<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Form;

use App\Catalog\Domain\Model\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CategoryRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('parent', ChoiceType::class, [
                'label' => 'Родительская категория',
                'choices' => $options['parents'],
                'choice_label' => static fn (Category $category): string => $category->getName(),
                'choice_value' => static fn (?Category $category): string => null === $category
                    ? ''
                    : (string) $category->getId(),
                'placeholder' => '— Новая корневая категория —',
                'required' => false,
                'attr' => ['class' => 'admin-select'],
            ])
            ->add('name', TextType::class, [
                'label' => 'Название',
                'attr' => ['class' => 'admin-input', 'placeholder' => 'Например, Умный дом'],
                'constraints' => [
                    new NotBlank(message: 'Укажите название категории'),
                    new Length(max: Category::NAME_MAX_LENGTH),
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Комментарий для админа',
                'required' => false,
                'attr' => [
                    'class' => 'admin-input',
                    'rows' => 3,
                    'placeholder' => 'Почему нужна эта категория и какие товары в ней будут',
                ],
                'constraints' => [new Length(max: 2000)],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => CategoryRequestFormData::class,
                'parents' => [],
            ])
            ->setAllowedTypes('parents', 'array');
    }
}
