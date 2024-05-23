<?php

namespace OHMedia\EventBundle\Form;

use OHMedia\EventBundle\Entity\EventTime;
use OHMedia\TimezoneBundle\Form\Type\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventTimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('starts_at', DateTimeType::class);

        $builder->add('ends_at', DateTimeType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventTime::class,
        ]);
    }
}
