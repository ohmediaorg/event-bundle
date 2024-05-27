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
        $dateTimeOptions = [
            'widget' => 'single_text',
        ];

        if ($options['timezone']) {
            $dateTimeOptions['view_timezone'] = $options['timezone'];
        }

        $builder->add('starts_at', DateTimeType::class, $dateTimeOptions);

        $builder->add('ends_at', DateTimeType::class, $dateTimeOptions);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventTime::class,
            'timezone' => null,
        ]);
    }
}
