<?php

namespace DavidBadura\FixturesBundle;

use DavidBadura\FixturesBundle\FixtureConverter\FixtureConverterInterface;
use DavidBadura\FixturesBundle\Event\PreExecuteEvent;
use DavidBadura\FixturesBundle\Event\PostExecuteEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 *
 * @author David Badura <d.badura@gmx.de>
 */
class FixtureManager
{

    /**
     *
     * @var ConverterRepository
     */
    private $converterRepository;

    /**
     *
     * @var FixtureLoader
     */
    private $fixtureLoader;

    /**
     *
     * @var Executor
     */
    private $executor;

    /**
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     *
     * @var array
     */
    private $defaultFixturesPath = array();

    /**
     *
     * @param PersisterInterface $persister
     */
    public function __construct(FixtureLoader $fixtureLoader,
        Executor $executor, ConverterRepository $repository,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->fixtureLoader = $fixtureLoader;
        $this->executor = $executor;
        $this->eventDispatcher = $eventDispatcher;
        $this->converterRepository = $repository;
    }

    /**
     *
     * @return FixtureFactory
     */
    public function getFixtureLoader()
    {
        return $this->fixtureLoader;
    }

    /**
     *
     * @return Executor
     */
    public function getExecutor()
    {
        return $this->executor;
    }

    /**
     *
     * @return ConverterRepository
     */
    public function getConverterRepository()
    {
        return $this->converterRepository;
    }

    /**
     *
     * @param FixtureConverterInterface $converter
     * @return \DavidBadura\FixturesBundle\FixtureManager
     * @throws \Exception
     */
    public function addConverter(FixtureConverterInterface $converter)
    {
        $this->converterRepository->addConverter($converter);
        return $this;
    }

    /**
     *
     * @param string $name
     * @return boolean
     */
    public function hasConverter($name)
    {
        return isset($this->converterRepository->hasConverter($name));
    }

    /**
     *
     * @param string $name
     * @return FixtureConverterInterface
     * @throws \Exception
     */
    public function getConverter($name)
    {
        return $this->converterRepository->getConverter($name);
    }

    /**
     *
     * @param string $name
     * @return \DavidBadura\FixturesBundle\FixtureManager
     * @throws \Exception
     */
    public function removeConverter($name)
    {
        $this->converterRepository->removeConverter($name);
        return $this;
    }


    /**
     *
     * @param array $dirs
     * @return \DavidBadura\FixturesBundle\FixtureManager
     */
    public function setDefaultFixturesPath($fixturesPath)
    {
        if (!is_array($fixturesPath)) {
            $this->defaultFixturesPath = array($fixturesPath);
        } else {
            $this->defaultFixturesPath = $fixturesPath;
        }
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getDefaultFixturesPath()
    {
        return $this->defaultFixturesPath;
    }

    /**
     *
     * @param array $options
     */
    public function load(array $options = array())
    {
        $fixtures = $this->factory->loadFixtures(($options['fixtures']) ?: $this->defaultFixturesPath);

        $event = new PreExecuteEvent($fixtures, $options);
        $this->eventDispatcher->dispatch(FixtureEvents::onPreExecute, $event);

        $fixtures = $event->getFixtures();
        $options = $event->getOptions();

        $this->executor->execute($fixtures);

        $event = new PostExecuteEvent($fixtures, $options);
        $this->eventDispatcher->dispatch(FixtureEvents::onPostExecute, $event);

        $fixtures = $event->getFixtures();
        $options = $event->getOptions();

        return $fixtures;
    }

}

