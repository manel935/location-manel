<?php

namespace App\Controller;

use App\Repository\ContratRepository;
use App\Repository\FactureRepository;
use App\Repository\VoitureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(
        VoitureRepository $voitureRepository,
        FactureRepository $factureRepository,
        ContratRepository $contratRepository
    ): Response
    {
        if ($this->getUser()) {
            $isAdmin = $this->getUser()->getRoles() == ['ROLE_ADMIN'];
            if ($isAdmin) {
                return $this->redirectToRoute("voiture_index");
            } else {
                $idAgence = $this->getUser()->getAgence()->getId();
                $nbrVoituresDisp = $voitureRepository->createQueryBuilder('v')
                    ->where('v.disponibilite = 1 AND v.agence = :agence')
                    ->setParameters([
                        'agence' => $idAgence
                    ])
                    ->select('count(v.id)')
                    ->getQuery()
                    ->getSingleScalarResult();

                $nbrFacturesImp = $factureRepository->createQueryBuilder('f')
                    ->where('f.payee = 0')
                    ->select('count(f.id)')
                    ->getQuery()
                    ->getSingleScalarResult();

                $nbrVoituresNonRendues = $contratRepository->createQueryBuilder('c')
                    ->leftJoin('c.voiture', 'v')
                    ->where('c.dateDeRet < :date AND v.disponibilite = 0 AND v.agence = :agence')
                    ->setParameters([
                        'date' => date('Y-m-d'),
                        'agence' => $idAgence
                    ])
                    ->select('count(c.id)')
                    ->getQuery()
                    ->getSingleScalarResult();

                return $this->render('index/index.html.twig', [
                    'voituresDisp' => $nbrVoituresDisp,
                    'voituresNonRendues' => $nbrVoituresNonRendues,
                    'facturesImp' => $nbrFacturesImp,
                ]);
            }
        } else {
            return $this->redirectToRoute("app_login");
        }
    }
}
