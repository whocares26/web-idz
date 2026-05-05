<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\OrderInput;
use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'Имя'])
            ->add('lastName', TextType::class, ['label' => 'Фамилия'])
            ->add('phone', TelType::class, ['label' => 'Телефон'])
            ->add('city', ChoiceType::class, [
                'label' => 'Город',
                'choices' => array_combine(Order::CITIES, Order::CITIES),
                'placeholder' => '-- Выберите --',
            ])
            ->add('address', TextType::class, ['label' => 'Адрес доставки'])
            ->add('delivery', ChoiceType::class, [
                'label' => 'Способ доставки',
                'choices' => array_combine(Order::DELIVERY_METHODS, Order::DELIVERY_METHODS),
                'placeholder' => '-- Выберите --',
            ])
            ->add('payment', ChoiceType::class, [
                'label' => 'Способ оплаты',
                'choices' => array_combine(Order::PAYMENT_METHODS, Order::PAYMENT_METHODS),
                'placeholder' => '-- Выберите --',
            ])
            ->add('totalSum', HiddenType::class)
            ->add('items', CollectionType::class, [
                'entry_type' => OrderItemFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderInput::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'order_form',
        ]);
    }
}
