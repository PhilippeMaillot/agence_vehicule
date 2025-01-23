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
use App\Repository\VehiculeRepository;

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
                $vehicule = $reservation->getVehicule();
                if ($vehicule) {
                    $vehicule->setDisponibilite(false);
                }
            } elseif ($now > $reservation->getEndedAt()) {
                $reservation->setStatus('Fini');
                $vehicule = $reservation->getVehicule();
                if ($vehicule) {
                    $vehicule->setDisponibilite(true);
                }
            } elseif ($now < $reservation->getCreatedAt()) {
                $reservation->setStatus('En attente');
                $vehicule = $reservation->getVehicule();
                if ($vehicule) {
                    $vehicule->setDisponibilite(true);
                }
            }
        }

        // Sauvegarder les changements dans la base de données
        $entityManager->flush();

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, VehiculeRepository $vehiculeRepository): Response
{
    // Vérifier si un ID est passé dans l'URL
    $vehiculeId = $request->query->get('id');
    $vehicule = null;

    if ($vehiculeId) {
        $vehicule = $vehiculeRepository->find($vehiculeId);
        if (!$vehicule) {
            throw $this->createNotFoundException('Véhicule non trouvé.');
        }
    }

    $reservation = new Reservation();

    // Si un véhicule est pré-sélectionné, l’associer directement à la réservation
    if ($vehicule) {
        $reservation->setVehicule($vehicule);
    }

    // Générer le formulaire avec ou sans le champ "vehicule"
    $form = $this->createForm(ReservationType::class, $reservation, [
        'vehicule_fixed' => $vehicule !== null,
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $duration = $reservation->getCreatedAt()->diff($reservation->getEndedAt())->days;
        $reservation->setPrixTotal($reservation->getVehicule()->getPrixparjour() * $duration);
        $reservation->setUser($this->getUser());

        $now = new \DateTimeImmutable();
        if ($now >= $reservation->getCreatedAt() && $now <= $reservation->getEndedAt()) {
            $reservation->setStatus('En cours');
            $vehicule = $reservation->getVehicule();
            if ($vehicule) {
                $vehicule->setDisponibilite(false);
            }
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
            $now = new \DateTimeImmutable();
            if ($now >= $reservation->getCreatedAt() && $now <= $reservation->getEndedAt()) {
                $reservation->setStatus('En cours');
                $vehicule = $reservation->getVehicule();
                if ($vehicule) {
                    $vehicule->setDisponibilite(false);
                }
            } elseif ($now > $reservation->getEndedAt()) {
                $reservation->setStatus('Fini');
                $vehicule = $reservation->getVehicule();
                if ($vehicule) {
                    $vehicule->setDisponibilite(true);
                }
            } elseif ($now < $reservation->getCreatedAt()) {
                $reservation->setStatus('En attente');
                $vehicule = $reservation->getVehicule();
                if ($vehicule) {
                    $vehicule->setDisponibilite(true);
                }
            }
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
            $vehicule = $reservation->getVehicule();
                if ($vehicule) {
                    $vehicule->setDisponibilite(true);
                }
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
