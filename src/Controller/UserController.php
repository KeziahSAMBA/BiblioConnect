<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\CommentaireRepository;
use App\Repository\FavoriRepository;
use App\Repository\LivreRepository;
use App\Repository\ReservationRepository;
use App\Entity\Livre;
use App\Entity\Commentaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/dashboard', name: 'user_dashboard')]
    public function dashboard(
        Request $request,
        LivreRepository $livreRepository,
        CategorieRepository $categorieRepository,
        FavoriRepository $favoriRepository,
        ReservationRepository $reservationRepository
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $search = trim((string) $request->query->get('q', ''));
        $categoryId = $request->query->getInt('category');

        $reservationsActives = $reservationRepository->findBy([
            'user' => $user,
            'statut' => ['actif', 'en_attente']
        ]);
        $livresReservesIds = array_map(fn($r) => $r->getLivre()->getId(), $reservationsActives);

        $qb = $livreRepository->createQueryBuilder('l')
            ->leftJoin('l.categorie', 'c')
            ->addSelect('c');

        if ($search !== '') {
            $qb->andWhere('LOWER(l.titre) LIKE :search OR LOWER(l.auteur) LIKE :search OR LOWER(c.nom) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        if ($categoryId > 0) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        $livres = $qb->orderBy('l.titre', 'ASC')->getQuery()->getResult();
        $categories = $categorieRepository->findAll();
        $favoris = $favoriRepository->findBy(['user' => $user]);
        $favoriIds = array_map(fn($f) => $f->getLivre()->getId(), $favoris);

        $activeReservationsCount = $reservationRepository->countActiveReservationsForUser($user->getId());

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'livres' => $livres,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $categoryId,
            'favoriIds' => $favoriIds,
            'activeReservationsCount' => $activeReservationsCount,
            'livresReservesIds' => $livresReservesIds,
        ]);
    }

    #[Route('/livre/{id}', name: 'user_livre_detail')]

public function livreDetail(
    Livre $livre,
    ReservationRepository $reservationRepository,
    FavoriRepository $favoriRepository,
    CommentaireRepository $commentaireRepository
): Response {
    /** @var \App\Entity\User $user */
    $user = $this->getUser();

    $favori = $favoriRepository->findOneBy(['user' => $user, 'livre' => $livre]);
    $isFavori = $favori !== null;

    // Réservation active ou en attente
    $reservation = $reservationRepository->findOneBy([
        'user' => $user,
        'livre' => $livre,
        'statut' => ['actif', 'en_attente']
    ]);
    $isReserve = $reservation !== null;

    // Réservation terminée (pour pouvoir commenter)
    $reservationTerminee = $reservationRepository->findOneBy([
        'user' => $user,
        'livre' => $livre,
        'statut' => 'terminé'
    ]);
    $canComment = $reservationTerminee !== null;

    // Vérifier si déjà commenté
    $existingComment = $commentaireRepository->findOneBy([
        'user' => $user,
        'livre' => $livre
    ]);
    $dejaCommente = $existingComment !== null;

    $commentaires = $commentaireRepository->findBy(['livre' => $livre], ['createdAt' => 'DESC']);

    $averageRating = 0;
    if (count($commentaires) > 0) {
        $totalRating = array_sum(array_map(fn($c) => $c->getNote() ?? 0, $commentaires));
        $averageRating = round($totalRating / count($commentaires), 1);
    }

    return $this->render('user/livre_detail.html.twig', [
        'livre' => $livre,
        'isFavori' => $isFavori,
        'isReserve' => $isReserve,
        'canComment' => $canComment,
        'dejaCommente' => $dejaCommente,
        'commentaires' => $commentaires,
        'averageRating' => $averageRating,
    ]);
}

    #[Route('/commentaire/{livre}/add', name: 'user_commentaire_add', methods: ['POST'])]
    public function addComment(
        Livre $livre,
        Request $request,
        ReservationRepository $reservationRepository,
        CommentaireRepository $commentaireRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $reservation = $reservationRepository->findOneBy([
            'user' => $user,
            'livre' => $livre,
            'statut' => 'terminé'
        ]);

        if (!$reservation) {
            $this->addFlash('error', 'Vous ne pouvez commenter que les livres que vous avez terminés de lire.');
            return $this->redirectToRoute('user_livre_detail', ['id' => $livre->getId()]);
        }

        $existingComment = $commentaireRepository->findOneBy([
            'user' => $user,
            'livre' => $livre
        ]);

        if ($existingComment) {
            $this->addFlash('error', 'Vous avez déjà commenté ce livre.');
            return $this->redirectToRoute('user_livre_detail', ['id' => $livre->getId()]);
        }

        $contenu = trim($request->request->get('contenu') ?? '');
        $note = (int) ($request->request->get('note') ?? 0);

        if (empty($contenu)) {
            $contenu = 'Aucun commentaire.';
        }

        if ($note < 1 || $note > 5) {
            $this->addFlash('error', 'La note doit être comprise entre 1 et 5.');
            return $this->redirectToRoute('user_livre_detail', ['id' => $livre->getId()]);
        }

        $commentaire = new Commentaire();
        $commentaire->setContenu($contenu);
        $commentaire->setNote($note);
        $commentaire->setUser($user);
        $commentaire->setLivre($livre);
        $commentaire->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($commentaire);
        $entityManager->flush();

        $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');
        return $this->redirectToRoute('user_livre_detail', ['id' => $livre->getId()]);
    }

    #[Route('/profil', name: 'user_profil')]
    public function profil(
        ReservationRepository $reservationRepository,
        FavoriRepository $favoriRepository
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $reservations = $reservationRepository->findBy(['user' => $user], ['dateDebut' => 'DESC']);
        $favoris = $favoriRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('user/profil.html.twig', [
            'user' => $user,
            'reservations' => $reservations,
            'favoris' => $favoris,
        ]);
    }
}