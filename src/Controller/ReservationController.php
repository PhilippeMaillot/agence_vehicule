<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        $reservations = $reservationRepository->findAll();
        $now = new \DateTimeImmutable();

        foreach ($reservations as $reservation) {
            if ($now >= $reservation->getCreatedAt() && $now <= $reservation->getEndedAt()) {
                $reservation->setStatus('En cours');
            } elseif ($now > $reservation->getEndedAt()) {
                $reservation->setStatus('Fini');
            } elseif ($now < $reservation->getCreatedAt()) {
                $reservation->setStatus('En attente');
            }
        }

        // Sauvegarder les changements dans la base de données
        $entityManager->flush();

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $duration = $reservation->getCreatedAt()->diff($reservation->getEndedAt())->days;
            $reservation->setPrixTotal($reservation->getVehicule()->getPrixparjour() * $duration);
            $reservation->setUser($this->getUser());
            $now = new \DateTimeImmutable();

            if ($now >= $reservation->getCreatedAt() && $now <= $reservation->getEndedAt()) {
                $reservation->setStatus('En cours');
            } elseif ($now > $reservation->getEndedAt()) {
                $reservation->setStatus('Fini');
            } elseif ($now < $reservation->getCreatedAt()) {
                $reservation->setStatus('En attente');
            }
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $duration = $reservation->getCreatedAt()->diff($reservation->getEndedAt())->days;
            $reservation->setPrixTotal($reservation->getVehicule()->getPrixparjour() * $duration);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
