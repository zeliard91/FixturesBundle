<?php

namespace DavidBadura\FixturesBundle\Persister;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * 
 * @author David Badura <d.badura@gmx.de>
 */
class DoctrinePersister implements PersisterInterface
{
    
    /**
     *
     * @var ObjectManager
     */
    protected $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om) {
        $this->om = $om;
    }
    
    public function save($objects) {
        foreach ($objects as $object) {
            $this->om->persist($object);
        }
        $this->om->flush();
    }
    
}