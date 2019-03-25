<?php

namespace Utarwyn\Jenkins\Entity;

use Exception;
use Utarwyn\Jenkins\Error\ConnectionErrorException;
use Utarwyn\Jenkins\JenkinsEntity;

/**
 * Class Project
 *
 * @package Utarwyn\Jenkins\Entity
 */
class Project extends JenkinsEntity
{

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $displayName;

    /**
     * @var string
     */
    protected $fullDisplayName;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var boolean
     */
    protected $buildable;

    protected $builds;

    /**
     * @var string
     */
    protected $color;

    protected $firstBuild;

    protected $healthReport;

    /**
     * @var boolean
     */
    protected $inQueue;

    /**
     * @var boolean
     */
    protected $keepDependencies;

    /**
     * @var integer
     */
    protected $nextBuildNumber;

    /**
     * @var boolean
     */
    protected $concurrentBuild;

    public function __construct($client, string $name)
    {
        parent::__construct($client, "job/$name");
    }

    /**
     * @return string Description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @return string
     */
    public function getFullDisplayName(): string
    {
        return $this->fullDisplayName;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isBuildable(): bool
    {
        return $this->buildable;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return is_null($this->color) ? "" : $this->color;
    }

    public function getHealthReport(): array
    {
        return $this->healthReport;
    }

    /**
     * @return bool
     */
    public function isInQueue(): bool
    {
        return $this->inQueue;
    }

    /**
     * @return bool
     */
    public function isKeepDependencies(): bool
    {
        return $this->keepDependencies;
    }

    /**
     * @param int $id
     *
     * @return Build
     */
    public function getBuild(int $id): Build
    {
        try {
            return new Build($this->client, $this, $id);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @return null|Build
     */
    public function getFirstBuild()
    {
        return $this->getBuildFromJson($this, "firstBuild");
    }

    /**
     * @return null|Build
     */
    public function getLastBuild()
    {
        return $this->getBuildFromJson($this, "lastBuild");
    }

    /**
     * @return null|Build
     */
    public function getLastCompletedBuild()
    {
        return $this->getBuildFromJson($this, "lastCompletedBuild");
    }

    /**
     * @return null|Build
     */
    public function getLastFailedBuild()
    {
        return $this->getBuildFromJson($this, "lastFailedBuild");
    }

    /**
     * @return null|Build
     */
    public function getLastStableBuild()
    {
        return $this->getBuildFromJson($this, "lastStableBuild");
    }

    /**
     * @return null|Build
     */
    public function getLastSuccessful()
    {
        return $this->getBuildFromJson($this, "lastSuccessfulBuild");
    }

    /**
     * @return null|Build
     */
    public function getLastUnstableBuild()
    {
        return $this->getBuildFromJson($this, "lastUnstableBuild");
    }

    /**
     * @return null|Build
     */
    public function getLastUnsuccessfulBuild()
    {
        return $this->getBuildFromJson($this, "lastUnsuccessfulBuild");
    }

    /**
     * @return int
     */
    public function getNextBuildNumber(): int
    {
        return $this->nextBuildNumber;
    }

    public function getBuilds()
    {
        return $this->builds;
    }

    /**
     * @param array $params
     *
     * @return QueueItem|null
     * @throws ConnectionErrorException
     */
    public function runBuild(array $params = []): ?QueueItem
    {
        $target = empty($params) ? 'build' : 'buildWithParameters';
        $url    = sprintf('job/%s/%s', $this->name, $target);

        try {
            $response = $this->client->post($url, $params);
        } catch (ConnectionErrorException $e) {
            return null;
        }

        if ($response->getStatusCode() !== 201) {
            return null;
        }

        $location = parse_url($response->getHeaderLine('Location'), PHP_URL_PATH);

        if (empty($location)) {
            return null;
        }

        return new QueueItem($this->client->get($location));
    }

    /**
     * @param Project $project
     * @param         $jsonKey
     *
     * @return null|Build
     */
    private function getBuildFromJson(Project $project, $jsonKey)
    {
        $buildObj = $project->getData()->$jsonKey;

        return $this->getBuild($buildObj->number);
    }
}
