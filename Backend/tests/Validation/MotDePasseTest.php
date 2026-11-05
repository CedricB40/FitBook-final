<?php

namespace App\Tests\Validation;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class MotDePasseTest extends TestCase
{
    private function valider(mixed $valeur): int
    {
        $validateur = Validation::createValidator();

        $contrainte = new Assert\Sequentially([
            new Assert\Type('string'),
            new Assert\NotBlank(),
            new Assert\Length(min: 8, max: 72),
        ]);

        return count($validateur->validate($valeur, $contrainte));
    }

    public function testMotDePasseValideEstAccepte(): void
    {
        $this->assertSame(0, $this->valider('Test_1234'));
    }

    public function testMotDePasseTropCourtEstRefuse(): void
    {
        $this->assertSame(1, $this->valider('Ab_1'));
    }

    public function testMotDePasseTropLongEstRefuse(): void
    {
        $this->assertSame(1, $this->valider(str_repeat('a', 73)));
    }

    public function testMotDePasseVideEstRefuse(): void
    {
        $this->assertSame(1, $this->valider(''));
    }
}