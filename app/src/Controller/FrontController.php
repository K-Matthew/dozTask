<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Character;
use App\Entity\Episode;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FrontController extends AbstractController
{
    public function __construct(private HttpClientInterface $client) {
        
    }
    
    public $apiUrl = "https://rickandmortyapi.com/api/character/?page=";
    
    #[Route('/{page}', defaults: ['page' => 1], name: 'app_front')]
    public function index($page): Response
    {
        $url = $this->apiUrl . $page;
        $page = $this->createPage($url);

        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
            'characters' => $page['characters'],
            'pagination' => $page['pagination'],
        ]);
    }

    public function createPage($url)
    {
        $page = [];
        $characters = [];
        $pagiantion = [];

        $response = $this->fetchFromApi($url);
        $data = $response->toArray();

        if (!empty($data['results'])) {
            foreach ($data['results'] as $info) {
                $person = new Character();
                $person->setApiId($info['id']);
                $person->setName($info['name']);
                $person->setAvatar($info['image']);
                if (!empty($info['episode'])) {
                    $asyncResponses = [];
                    foreach($info['episode'] as $episodeApi) {
                        $asyncResponses[] = $this->fetchFromApi($episodeApi);
                    }
                    foreach ($asyncResponses as $asyncResponse) {
                        $episodeData = $asyncResponse->toArray();
                        $episode = new Episode();
                        $episode->setName($episodeData['name']);
                        $person->addEpisode($episode);
                    }
                }
                $characters[] = $person;
            }
        }

        $pagiantion['prev'] = $this->takePage($data['info']['prev']);
        $pagiantion['next'] = $this->takePage($data['info']['next']);
        $page['characters'] = $characters;
        $page['pagination'] = $pagiantion;

        return $page;
    }

    public function fetchFromApi($url)
    {
        return $this->client->request(
            'GET',
            $url,
        );
    }

    public function takePage($url)
    {
        $page = '';
        if (!empty($url)) {
            $query_str = parse_url($url, PHP_URL_QUERY);
            parse_str($query_str, $query_params);
            $page = $query_params['page'];
        }

        return $page;
    }
}
