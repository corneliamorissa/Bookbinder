<?php

namespace App\Controller;

use App\Entity\Avatar;
use App\Entity\LoginUser;
use App\Entity\User;
use App\Form\SignUpFormType;
use App\Repository\AvatarRepository;
use App\Repository\UserRepository;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\s;

class RegistrationController extends AbstractController
{

    private array $stylesheets;

    public function __construct(AuthenticationService $userService) {
        $this->stylesheets[] = 'main.css';
    }
    /**
     * @Route("/SignUp", name="SignUp")
     */
    #[Route("/SignUp", name: "SignUp")]
    public function signup(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, AvatarRepository $ap): Response {

        $avatar = $entityManager->getRepository(Avatar::class)->findAll();

        $form = null;
        $form = $this->createForm(SignUpFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $user = $form->getData();
            $avatar = $form ->get('avatar')->getData();
            $user->setAvatarId($avatar->getId());

            $plaintextPassword = $user->getPassword() ;

            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);

            // 4) save the User!
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Account Succesfully Created! Log in is needed');
            return $this->redirectToRoute('LogIn');
        }

        return $this->render('signup.html.twig', [
            'stylesheets' => $this->stylesheets,
            'form'=>$form->createView(),
            'avatar' => $avatar
        ]);
    }
}
