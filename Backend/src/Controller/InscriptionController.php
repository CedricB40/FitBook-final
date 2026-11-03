<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\CourrielService;
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
    private const MOTIF_NOM = "/^[A-Za-zÀ-ÿ]+([ '\\-][A-Za-zÀ-ÿ]+)*$/u";
    private const MOTIF_MOT_DE_PASSE = '/^(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).+$/';

    #[Route(path: '/api/inscription', name: 'api_inscription', methods: ['POST'])]
    public function inscrire(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,
        CourrielService $courrielService
    ): JsonResponse {
        try {
            $donnees = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['erreur' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $contraintes = new Assert\Collection(
            fields: [
                'nom' => $this->contraintesNom('nom'),
                'prenom' => $this->contraintesNom('prénom'),
                'email' => new Assert\Sequentially([
                    new Assert\Type('string', message: "L'adresse e-mail doit être du texte."),
                    new Assert\NotBlank(message: 'Ce champ est obligatoire.'),
                    new Assert\Email(message: 'Adresse e-mail invalide.'),
                    new Assert\Length(max: 255, maxMessage: "L'adresse e-mail ne peut pas dépasser {{ limit }} caractères."),
                ]),
                'motDePasse' => new Assert\Sequentially([
                    new Assert\Type('string', message: 'Le mot de passe doit être du texte.'),
                    new Assert\NotBlank(message: 'Ce champ est obligatoire.'),
                    new Assert\Length(
                        min: 8,
                        max: 72,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        maxMessage: 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.'
                    ),
                    new Assert\Regex(
                        pattern: self::MOTIF_MOT_DE_PASSE,
                        message: 'Le mot de passe doit contenir au moins une lettre, un chiffre et un caractère spécial.'
                    ),
                ]),
                'role' => new Assert\Sequentially([
                    new Assert\Type('string', message: 'Le rôle doit être du texte.'),
                    new Assert\Choice(
                        choices: ['ROLE_SPORTIF', 'ROLE_COACH', 'ROLE_GESTIONNAIRE'],
                        message: 'Rôle invalide : choisis sportif, coach ou gestionnaire.'
                    ),
                ]),
            ],
            extraFieldsMessage: "Ce champ n'est pas autorisé.",
            missingFieldsMessage: 'Ce champ est obligatoire.'
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

        $courrielService->envoyerConfirmation($utilisateur);

        return $this->json([
            'id' => $utilisateur->getId(),
            'email' => $utilisateur->getEmail(),
            'role' => $utilisateur->getRole(),
            'emailConfirme' => $utilisateur->isEmailConfirme(),
        ], Response::HTTP_CREATED);
    }

    private function contraintesNom(string $libelle): Assert\Sequentially
    {
        return new Assert\Sequentially([
            new Assert\Type('string', message: "Le $libelle doit être du texte."),
            new Assert\NotBlank(message: 'Ce champ est obligatoire.'),
            new Assert\Length(
                min: 2,
                max: 50,
                minMessage: "Le $libelle doit contenir au moins {{ limit }} caractères.",
                maxMessage: "Le $libelle ne peut pas dépasser {{ limit }} caractères."
            ),
            new Assert\Regex(
                pattern: self::MOTIF_NOM,
                message: "Le $libelle ne peut contenir que des lettres, des espaces, des tirets ou des apostrophes."
            ),
        ]);
    }
}
