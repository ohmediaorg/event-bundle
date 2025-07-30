<?php

namespace OHMedia\EventBundle\Form;

use OHMedia\EventBundle\Entity\ArticleEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $articleEvent = $options['data'];

        $builder->add('name');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArticleEvent::class,
        ]);
    }
}
