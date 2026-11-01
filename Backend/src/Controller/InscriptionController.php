<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InscriptionController extends AbstractController
{
    #[Route(path: '/api/inscription', name: 'api_inscription', methods: ['POST'])]
    public function inscrire(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        try {
            $donnees = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['erreur' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $contraintes = new Assert\Collection(
            fields: [
                'nom' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                    new Assert\Length(max: 50),
                ]),
                'prenom' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                    new Assert\Length(max: 50),
                ]),
                'email' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                    new Assert\Email(),
                    new Assert\Length(max: 255),
                ]),
                'motDePasse' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                    new Assert\Length(min: 8, max: 72),
                ]),
                'role' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\Choice(choices: ['ROLE_SPORTIF', 'ROLE_COACH', 'ROLE_GESTIONNAIRE']),
                ]),
            ]
        );

        $violations = $validator->validate($donnees, $contraintes);

        if (count($violations) > 0) {
            $erreurs = [];
            foreach ($violations as $violation) {
                $erreurs[trim($violation->getPropertyPath(), '[]')] = $violation->getMessage();
            }

            return $this->json(['erreurs' => $erreurs], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $email = mb_strtolower($donnees['email']);

        if ($entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email])) {
            return $this->json(
                ['erreurs' => ['email' => 'Cette adresse e-mail est déjà utilisée.']],
                Response::HTTP_CONFLICT
            );
        }

        $utilisateur = new Utilisateur();
        $utilisateur->setNom($donnees['nom']);
        $utilisateur->setPrenom($donnees['prenom']);
        $utilisateur->setEmail($email);
        $utilisateur->setRole($donnees['role']);
        $utilisateur->setMotDePasse($hasher->hashPassword($utilisateur, $donnees['motDePasse']));

        $entityManager->persist($utilisateur);
        $entityManager->flush();

        return $this->json([
            'id' => $utilisateur->getId(),
            'email' => $utilisateur->getEmail(),
            'role' => $utilisateur->getRole(),
            'emailConfirme' => $utilisateur->isEmailConfirme(),
        ], Response::HTTP_CREATED);
    }
}