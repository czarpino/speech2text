<?php

namespace App\Form\Type;


use App\Entity\AudioUpload;
use App\Entity\AudioUploadChunk;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AudioUploadChunkInputType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('upload_id', EntityType::class, [
            'class' => AudioUpload::class,
            'property_path' => 'audioUpload'
        ]);

        $builder->add('order', IntegerType::class, [
            'property_path' => 'chunkNumber'
        ]);

        $builder->add('audio_data', TextType::class, [
            'property_path' => 'audioData'
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AudioUploadChunk::class,
            'csrf_protection' => false
        ));
    }
}