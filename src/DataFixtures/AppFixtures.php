<?php

namespace App\DataFixtures;

use App\Command\ImageImporterCommand;
use App\Entity\KodiImageType;
use App\Entity\User;
use App\Service\KodiImageImporter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use \Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();

        $user
            ->setEmail('dhoffmann@localhost')
            ->setRoles([])
            ->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'bruh'
        ));

        $manager->persist($user);

        $imageTypes = [
            [
                'name' => KodiImageImporter::IMAGE_TYPE_ALBUM,
                'width' => 800,
                'height' => 800,
                'path' => '/album/'
            ],
            [
                'name' => KodiImageImporter::IMAGE_TYPE_ALBUM_THUMB,
                'width' => 250,
                'height' => 250,
                'path' => '/album/thumb/'
            ],
            [
                'name' => KodiImageImporter::IMAGE_TYPE_ARTIST_FANART,
                'width' => 1920,
                'height' => 1080,
                'path' => '/artist/fanart/'
            ],
            [
                'name' => KodiImageImporter::IMAGE_TYPE_ARTIST_FANART_THUMB,
                'width' => 480,
                'height' => 270,
                'path' => '/artist/fanart/thumb/'
            ],
            [
                'name' => KodiImageImporter::IMAGE_TYPE_ARTIST_COVER,
                'width' => 800,
                'height' => 800,
                'path' => '/artist/'
            ],
            [
                'name' => KodiImageImporter::IMAGE_TYPE_ARTIST_COVER_THUMB,
                'width' => 250,
                'height' => 250,
                'path' => '/artist/thumb/'
            ],
        ];

        foreach ([] as $imageType) {
            $type = new KodiImageType();
            $type
                ->setName($imageType['name'])
                ->setWidth($imageType['width'])
                ->setHeight($imageType['height'])
                ->setPath($imageType['path'])
            ;

            $manager->persist($type);
        }

        $manager->flush();
    }
}