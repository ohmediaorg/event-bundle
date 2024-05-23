<?php

namespace OHMedia\EventBundle\Form;

use OHMedia\EventBundle\Entity\Event;
// use Doctrine\ORM\EntityRepository;
// use Doctrine\ORM\QueryBuilder;
// use OHMedia\FileBundle\Form\Type\FileEntityType;
// use OHMedia\MetaBundle\Form\Type\MetaEntityType;
// use OHMedia\TimezoneBundle\Form\Type\DateTimeType;
// use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
// use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
// use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
// use Symfony\Component\Form\Extension\Core\Type\EmailType;
// use Symfony\Component\Form\Extension\Core\Type\IntegerType;
// use Symfony\Component\Form\Extension\Core\Type\MoneyType;
// use Symfony\Component\Form\Extension\Core\Type\NumberType;
// use Symfony\Component\Form\Extension\Core\Type\TelType;
// use Symfony\Component\Form\Extension\Core\Type\TextareaType;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
// use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $event = $options['data'];

        // TIP: these all do the same thing
        // $builder->add('name');
        // $builder->add('name', null);
        // $builder->add('name', TextType::class);

        // always use the file-bundle for files
        // for some reason it is necessary to specify 'data'
        // when usually 'mapped' => true (default) is enough
        // $builder->add('file', FileEntityType::class, [
        //     'data' => $event->getFile(),
        // ]);
        // $builder->add('image', FileEntityType::class, [
        //     'image' => true,
        //     'data' => $event->getImage(),
        // ]);

        // always use the datetime-bundle to ensure timezones are good
        // $builder->add('start_datetime', DateTimeType::class);
        // $builder->add('end_datetime', DateTimeType::class);

        // if you have a checkbox for a toggle, make sure it is not required
        // $builder->add('is_featured', CheckboxType::class, [
        //     'required' => false,
        // ]);

        // <select>
        // $builder->add('selection', ChoiceType::class);

        // <select multiple>
        // $builder->add('selection', ChoiceType::class, [
        //     'multiple' => true,
        // ]);

        // array of <input type="radio">
        // $builder->add('selection', ChoiceType::class, [
        //     'expanded' => true,
        // ]);

        // array of <input type="checkbox">
        // $builder->add('selection', ChoiceType::class, [
        //     'expanded' => true,
        //     'multiple' => true,
        // ]);

        // for a OneToOne or ManyToOne relationship selection
        // $builder->add('owner', EntityType::class, [
        //     'class' => User::class,
        //     'query_builder' => function (EntityRepository $er): QueryBuilder {
        //         return $er->createQueryBuilder('u')
        //             ->orderBy('u.email', 'ASC');
        //     },
        //     'choice_label' => 'email',
        // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
