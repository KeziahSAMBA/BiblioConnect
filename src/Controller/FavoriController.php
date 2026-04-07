<?php

namespace App\Controller;

use App\Entity\Favori;
use App\Entity\Livre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/user/favori')]
class FavoriController extends AbstractController
{
    #[Route('/toggle/{id}', name: 'user_favori_toggle')]
    public function toggle(Livre $livre, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $favori = $em->getRepository(Favori::class)->findOneBy([
            'user' => $user,
            'livre' => $livre
        ]);

        if ($favori) {
            $em->remove($favori);
            $this->addFlash('success', 'Retiré des favoris');
        } else {
            $favori = new Favori();
            $favori->setUser($user);
            $favori->setLivre($livre);
            $favori->setCreatedAt(new \DateTimeImmutable());

            $em->persist($favori);
            $this->addFlash('success', 'Ajouté aux favoris');
        }

        $em->flush();

        return $this->redirectToRoute('user_dashboard');
    }
}