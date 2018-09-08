<?php

namespace App\Form\Type;


use App\Entity\AudioUpload;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AudioUploadInputType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('filename', TextType::class);

        $builder->get('filename')
                ->addModelTransformer(new CallbackTransformer(
                    function ($filename) {
                        return $filename;
                    },
                    function ($filename) {
                        return time() . '-' . $filename;
                    }
                ));

    }

    public function getBlockPrefix()
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AudioUpload::class,
            'csrf_protection' => false
        ));
    }
}