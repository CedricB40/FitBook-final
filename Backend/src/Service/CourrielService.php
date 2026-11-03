<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Utilisateur;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Envoie les e-mails de FitBook : confirmation d'adresse et notifications de réservation.
 * Un échec d'envoi ne bloque jamais l'action de l'utilisateur (inscription, réservation).
 */
class CourrielService
{
    private const EXPEDITEUR = 'noreply@fitbook.be';
    private const NOM_EXPEDITEUR = 'FitBook';

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly VerifyEmailHelperInterface $verifyEmailHelper
    ) {
    }

    public function envoyerConfirmation(Utilisateur $utilisateur): void
    {
        $signature = $this->verifyEmailHelper->generateSignature(
            'api_confirmation_email',
            (string) $utilisateur->getId(),
            (string) $utilisateur->getEmail(),
            ['id' => $utilisateur->getId()]
        );

        $prenom = $this->echapper($utilisateur->getPrenom());
        $lien = $this->echapper($signature->getSignedUrl());

        $this->envoyer(
            (string) $utilisateur->getEmail(),
            'FitBook : confirme ton adresse e-mail',
            "<p>Bonjour $prenom,</p>"
            ."<p>Merci de t'être inscrit sur FitBook. Clique sur le lien ci-dessous pour confirmer ton adresse e-mail :</p>"
            ."<p><a href=\"$lien\">Confirmer mon adresse e-mail</a></p>"
            ."<p>Ce lien est valable une heure.</p>"
        );
    }

    public function envoyerReservation(Reservation $reservation, bool $confirmee): void
    {
        $sportif = $reservation->getSportif();
        $seance = $reservation->getSeance();
        $cours = $seance?->getCours();

        if (!$sportif || !$seance || !$cours) {
            return;
        }

        $prenom = $this->echapper($sportif->getPrenom());
        $titre = $this->echapper($cours->getTitre());
        $salle = $this->echapper($cours->getSalle()?->getNom());
        $date = $seance->getDateHeureDebut()?->format('d/m/Y \à H:i');

        $this->envoyer(
            (string) $sportif->getEmail(),
            $confirmee ? 'FitBook : réservation confirmée' : 'FitBook : réservation annulée',
            "<p>Bonjour $prenom,</p>"
            .($confirmee
                ? "<p>Ta réservation est confirmée :</p>"
                : "<p>Ta réservation a bien été annulée :</p>")
            ."<ul><li>Séance : $titre</li><li>Salle : $salle</li><li>Date : $date</li></ul>"
        );
    }

    private function envoyer(string $destinataire, string $sujet, string $contenuHtml): void
    {
        $courriel = (new Email())
            ->from(new Address(self::EXPEDITEUR, self::NOM_EXPEDITEUR))
            ->to($destinataire)
            ->subject($sujet)
            ->html($contenuHtml);

        try {
            $this->mailer->send($courriel);
        } catch (TransportExceptionInterface) {
            // L'envoi a échoué (serveur de messagerie injoignable) : on ne bloque pas l'utilisateur.
        }
    }

    private function echapper(?string $texte): string
    {
        return htmlspecialchars((string) $texte, ENT_QUOTES, 'UTF-8');
    }
}
