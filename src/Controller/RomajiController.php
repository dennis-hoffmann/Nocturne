<?php

namespace App\Controller;

use App\Elastic\RomajiAnalyzer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RomajiController extends AbstractController
{
    /**
     * @Route("/romaji", name="romaji_analyser")
     *
     * @param Request $request
     * @param RomajiAnalyzer $analyzer
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, RomajiAnalyzer $analyzer)
    {
        $romaji = '';
        $text = '';

        if ($request->isMethod(Request::METHOD_POST)) {
            $text = $request->get('text');
            $romaji = $analyzer->analyze($text);
        }

        return $this->render('romaji/index.html.twig', [
            'romaji' => $romaji,
            'text' => $text,
        ]);
    }
}