<?php

namespace App\Exception;

use App\Entity\KodiImage;

class KodiImageAlreadyExistsException extends \Exception
{
    /**
     * @var KodiImage
     */
    private $image;

    public function __construct(KodiImage $image)
    {
        parent::__construct();

        $this->image = $image;
    }

    public function getImage(): KodiImage
    {
        return $this->image;
    }
}