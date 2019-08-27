<?php

namespace App\Service;

use App\Entity\Actor;
use App\Entity\ActorPopularMovie;
use Symfony\Component\HttpClient\HttpClient;
use \DOMDocument;
use \DOMXPath;

class ActorParser
{
    private $url;

    public function __construct(string $url)
    {
          $this->url = $url;
    }

    public function getActorLinks(): array
    {
        $html = self::makeRequest($this->url);
        $xpath = self::prepareXPathObj($html);

        $elements = $xpath->query('//*[@id="main"]/div/div[*]/div[*]/div[*]/h3/a/@href');
        $actorLinks = [];
        foreach ($elements as $actorNode) {
            $actorLinks[] = 'https://www.imdb.com'.$actorNode->value;
        }

        return $actorLinks;
    }


    public function getActor()
    {
        $html = self::makeRequest($this->url);
        $xpath = self::prepareXPathObj($html);

        $name = $xpath->query('//*[@id="name-overview-widget-layout"]/tbody/tr[1]/td/h1/span[1]')[0]->nodeValue;
        $imgSrc = $xpath->query('//*[@id="name-poster"]')[0]->getAttribute('src');
        $birthdayDate = date_parse($xpath->query('//*[@id="name-born-info"]/time')[0]->textContent);

        $birthdayDateObj = new \DateTimeImmutable(
            $birthdayDate['year'].'-'.
            $birthdayDate['month'].'-'.
            $birthdayDate['day']);

        $actor = new Actor();

        $actor->setName($name);
        $actor->setImageSrc($imgSrc);
        $actor->setBirthday($birthdayDateObj);
        $actor->setBiography(trim($this->getBiography()));

        $popularMoviesNode = $xpath->query('//*[@id="knownfor"]/div');
        $popularMovies = [];

        foreach ($popularMoviesNode as $item) {
            $movie = new ActorPopularMovie();
            $movie->setName($item->childNodes[3]->childNodes[1]->textContent);
            $movie->setActor($actor);
            $movie->setRole($item->childNodes[3]->childNodes[3]->textContent);
            $movie->setYear($item->childNodes[5]->nodeValue);
            $popularMovies[] = $movie;
        }
        $res = new \stdClass();
        $res->actor = $actor;
        $res->movies = $popularMovies;

        return $res;

    }

    private function getBiography()
    {
        try {
            $html = self::makeRequest($this->url . '/bio?ref_=nm_ov_bio_sm');
        } catch(\HttpRequestException $e) {
            print_r($e->getMessage());
        }

        return (self::prepareXPathObj($html))
            ->query('//*[@id="bio_content"]/div[2]/p[1]')[0]->textContent;
    }

    private static function makeRequest(string $url): string
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $url);

        if ($response->getStatusCode() === 200) {
            return $response->getContent();
        } else {
            throw new \HttpRequestException('Problem with making http request');
        }
    }

    private static function prepareXPathObj(string $html): DOMXPath
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($html);

        return new DOMXPath($doc);
    }
}