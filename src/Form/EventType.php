<?php

namespace OHMedia\EventBundle\Form;

use OHMedia\EventBundle\Entity\Event;
use OHMedia\WysiwygBundle\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $event = $options['data'];

        $builder->add('name');

        $builder->add('description', WysiwygType::class);

        $builder->add('snippet', TextareaType::class);

        $builder->add('location', TextType::class, [
            'required' => false,
        ]);

        $builder->add('ticket_url', UrlType::class, [
            'required' => false,
        ]);

        $builder->add('image', FileEntityType::class, [
            'image' => true,
            'data' => $event->getImage(),
            'required' => false,
        ]);

        $builder->add('times', CollectionType::class, [
            'entry_type' => EventTimeType::class,
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
