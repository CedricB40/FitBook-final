<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProfilController extends AbstractController
{
    private const MOTIF_NOM = "/^[A-Za-zÀ-ÿ]+([ '\\-][A-Za-zÀ-ÿ]+)*$/u";

    #[Route(path: '/api/profil', name: 'api_profil_voir', methods: ['GET'])]
    public function voir(): JsonResponse
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        return $this->json($this->formater($utilisateur));
    }

    #[Route(path: '/api/profil', name: 'api_profil_modifier', methods: ['PUT'])]
    public function modifier(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager
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

        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();
        $email = mb_strtolower($donnees['email']);

        $existant = $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if ($existant && $existant->getId() !== $utilisateur->getId()) {
            return $this->json(
                ['erreurs' => ['email' => 'Cette adresse e-mail est déjà utilisée.']],
                Response::HTTP_CONFLICT
            );
        }

        $emailChange = $email !== $utilisateur->getEmail();

        $utilisateur->setNom($donnees['nom']);
        $utilisateur->setPrenom($donnees['prenom']);
        $utilisateur->setEmail($email);

        $entityManager->flush();

        return $this->json([
            'profil' => $this->formater($utilisateur),
            'token' => $emailChange ? $jwtManager->create($utilisateur) : null,
        ]);
    }

    private function formater(Utilisateur $utilisateur): array
    {
        return [
            'nom' => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
            'email' => $utilisateur->getEmail(),
            'role' => $utilisateur->getRole(),
        ];
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
