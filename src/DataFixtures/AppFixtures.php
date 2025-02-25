<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i=0; $i<10; $i++) {
            $author = new Author();
            $author->setFirstName('Prenom ' . $i);
            $author->setLastName('Nom' . $i);
            $manager->persist($author);
            
            $listAuthor[] = $author;
        }

        // Create books
        for ($i = 0; $i < 20; $i++) {
            $book = new Book;
            $book->setTitle('Livre ' . $i);
            $book->setCoverText('Quatrième de couverture numéro : ' . $i);
            //relation author/book with random id from array listAuthor 
            $book->setAuthor($listAuthor[array_rand($listAuthor)]);
            $manager->persist($book);
        }

        $manager->flush();
    }
}
