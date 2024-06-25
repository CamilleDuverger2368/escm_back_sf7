<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * send validation mail at register
     *
     * @param string $email user's mail
     * @param string $link validation's link
     */
    public function sendMailRegister(string $email, string $link): void
    {
        $message = new Email();

        $message->from("contact@escapematch.com")
              ->to($email)
              ->subject("Bienvenu sur EscapeMatch !")
//TO-DO : Remplacer l'adresse en dure par une variable selon l'environnement de lancement + mettre l'adresse du FRONT
              ->html("<p>Vous avez presque finalisé votre inscription !
              Il ne reste plus qu'à ce que vous validiez votre adresse mail avec le lien suivant
              <a href='https://escapematch.escmatch.fr/api/unlog/validation/" . $link . "'>Valider mon email</a>!</p>");

        $this->mailer->send($message);
    }

    /**
     * send confirmation mail at register
     *
     * @param string $email user's mail
     */
    public function sendMailConfirm(string $email): void
    {
        $message = new Email();

        $message->from("contact@escapematch.com")
              ->to($email)
              ->subject("Ton mail est validé !")
//TO-DO : Remplacer l'adresse en dure par une variable selon l'environnement de lancement + mettre l'adresse du FRONT
              ->html("<p>Vous avez finalisé votre inscription ! Bravo !
              Il n'y a plus qu'a profiter des offres d'escapematch !
              <a href='https://harmonious-dolphin-f4601c.netlify.app/login'>Se connecter</a></p>");

        $this->mailer->send($message);
    }

    /**
     * send reset password
     *
     * @param string $email user's mail
     * @param string $password new user's password
     */
    public function sendResetPassword($email, $password): void
    {
        $message = new Email();

        $message->from("contact@escapematch.com")
              ->to($email)
              ->subject("Votre nouveau mot de passe est la !")
//TO-DO : Remplacer l'adresse en dure par une variable selon l'environnement de lancement
              ->html("<p>Voici votre nouveau mot de passe temporaire !</p>
              <p>" . $password . "</p>
              <p> Vous pouvez vous connecter avec celui ci et aller sur votre profil pour en creer un nouveau !
              <a href='https://harmonious-dolphin-f4601c.netlify.app/login'>Me Connecter</a>!</p>");

        $this->mailer->send($message);
    }
}
