<?php

namespace App\Controller;

use App\Entity\Voiture;
use App\Form\VoitureType;
use App\Repository\VoitureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/voiture")
 */
class VoitureController extends AbstractController
{
    /**
     * @Route("/", name="voiture_index", methods={"GET"})
     */
    public function index(VoitureRepository $voitureRepository): Response
    {
        $isAdmin = $this->getUser()->getRoles() == ['ROLE_ADMIN'];
        $agence = $this->getUser()->getAgence();

        return $this->render('voiture/index.html.twig', [
            'voitures' => $isAdmin ? $voitureRepository->findAll() : $voitureRepository->findBy(['agence' => $agence->getId()]),
            'isAdmin' => $isAdmin
        ]);
    }

    /**
     * @Route("/new", name="voiture_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $voiture = new Voiture();
        $form = $this->createForm(VoitureType::class, $voiture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $voiture->setDisponibilite(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($voiture);
            $entityManager->flush();

            return $this->redirectToRoute('voiture_index');
        }

        return $this->render('voiture/new.html.twig', [
            'voiture' => $voiture,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="voiture_show", methods={"GET"})
     */
    public function show(Voiture $voiture): Response
    {
        return $this->render('voiture/show.html.twig', [
            'voiture' => $voiture,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="voiture_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Voiture $voiture): Response
    {
        $form = $this->createForm(VoitureType::class, $voiture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('voiture_index');
        }

        return $this->render('voiture/edit.html.twig', [
            'voiture' => $voiture,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="voiture_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Voiture $voiture): Response
    {
        if ($this->isCsrfTokenValid('delete'.$voiture->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($voiture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('voiture_index');
    }

    /**
     * @Route("/{id}/rent", name="voiture_rent", methods={"GET","POST"})
     */
    public function rent(Request $request, Voiture $voiture): Response
    {
        if ($voiture->getDisponibilite()) {
            $voiture->setDisponibilite(false);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('voiture_index');
    }

    /**
     * @Route("/{id}/return", name="voiture_return", methods={"GET","POST"})
     */
    public function return(Request $request, Voiture $voiture): Response
    {
        if (!$voiture->getDisponibilite()) {
            $voiture->setDisponibilite(true);
            $this->getDoctrine()->getManager()->flush();
        }
        
        return $this->redirectToRoute('voiture_index');
    }
}
