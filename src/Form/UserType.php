<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'attr' => ['class' => 'formulaire'],
                'label'=> 'Nom',
                'required' => true,
            ])
            ->add('firstName', TextType::class,[
                'attr' => ['class' => 'formulaire'],
                'label'=> 'Prénom',
                'required' => true,
            ])
            ->add('email', EmailType::class,[
                'attr' => ['class' => 'formulaire'],
                'label'=> 'mail',
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'attr' => ['class' => 'formulaire'],
                'label'=> 'Mot de passe',
                'required' => true,
            ])
            ->add('Ajouter', SubmitType::class) ;  
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
