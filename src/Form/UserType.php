<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $builder->getData() ? $builder->getData() : null;
        //Short if
//        if ($builder->getData()){
//            $user = $builder->getData();
//        }else{
//            $user = null;
//        }
        $file = $user && $user->getImage() ? new File($user->getImage(), false) : null ;

        $builder
            ->add('firstname', TextType::class)
            ->add('lastname' , TextType::class)
            ->add('created_at', DateType::class)
            ->add('updated_at' , DateType::class)
            ->add('image', FileType::class, [
                "data" => $file
            ])
            ->add('groupe')
        ;
    }   //configuration du formulaire

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }   // gere les options du formulaires
}
