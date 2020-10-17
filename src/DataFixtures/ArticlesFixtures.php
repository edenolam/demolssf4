<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ArticlesFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i++) {
            $article = new Article();
            $article->setTitle("Titre de l'article n°$i");
            $article->setContent("Contenu de l'aricle n°$i");
            $article->setImage("http://placehold.it/350x150");
            $article->setCreatedAt(new \DateTime());

            $manager->persist($article);
        }
        $manager->flush();
    }
}
