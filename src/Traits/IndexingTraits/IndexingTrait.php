<?php

namespace Firesphere\SearchBackend\Traits\IndexingTraits;

use Exception;
use Firesphere\SearchBackend\Helpers\IndexingHelper;
use HttpException;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\DB;
use Solarium\Exception\HttpException as SolrException;

trait IndexingTrait
{
    /**
     * Set up the requirements for this task
     *
     * @param HTTPRequest $request Current request
     * @return array
     */
    protected function taskSetup(HTTPRequest $request): array
    {
        $vars = $request->getVars();
        $debug = $this->isDebug() || isset($vars['debug']);
        // Forcefully set the debugging to whatever the outcome of the above is
        $this->setDebug($debug, true);
        $group = $vars['group'] ?? 0;
        $start = $vars['start'] ?? 0;
        $group = ($start > $group) ? $start : $group;
        $isGroup = isset($vars['group']);

        return [$vars, $group, $isGroup];
    }

    /**
     * Index the classes for a specific index
     *
     * @param array $classes Classes that need indexing
     * @param bool $isGroup Indexing a specific group?
     * @param int $group Group to index
     * @return int|bool
     * @throws Exception
     * @throws HTTPException|HttpClientException|SolrException
     */
    protected function indexClassForIndex(array $classes, bool $isGroup, int $group)
    {
        $groups = 0;
        foreach ($classes as $class) {
            $groups = $this->indexClass($isGroup, $class, $group);
        }

        return $groups;
    }

    /**
     * Check if PCNTL is available and/or useable.
     * The unittest param is from phpunit.xml.dist, meant to bypass the exit(0) call
     * The pcntl parameter check is for unit tests, but PHPUnit does not support PCNTL (yet)
     *
     * @return bool
     */
    private function hasPCNTL(): bool
    {
        return Director::is_cli() &&
            function_exists('pcntl_fork') &&
            (Controller::curr()->getRequest()->getVar('unittest') === 'pcntl' ||
                !Controller::curr()->getRequest()->getVar('unittest'));
    }

    /**
     * For each core, spawn a child process that will handle a separate group.
     * This speeds up indexing through CLI massively.
     *
     * @codeCoverageIgnore Can't be tested because PCNTL is not available
     * @param string $class Class to index
     * @param int $group Group to index
     * @param int $groups Total amount of groups
     * @return int Last group indexed
     * @throws Exception
     * @throws HTTPException
     */
    private function spawnChildren(string $class, int $group, int $groups): int
    {
        $start = $group;
        $pids = [];
        $cores = IndexingHelper::getCores();
        // for each core, start a grouped indexing
        for ($i = 0; $i < $cores; $i++) {
            $start = $group + $i;
            if ($start < $groups) {
                $this->runForkedChild($class, $pids, $start);
            }
        }
        // Wait for each child to finish
        // It needs to wait for them independently,
        // or it runs out of memory for some reason
        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
        }

        return $start;
    }

    /**
     * Create a fork and run the child
     *
     * @codeCoverageIgnore Can't be tested because PCNTL is not available
     * @param string $class Class to index
     * @param array $pids Array of all the child Process IDs
     * @param int $start Start point for the objects
     * @return void
     * @throws HTTPException
     * @throws ValidationException
     */
    private function runForkedChild(string $class, array &$pids, int $start): void
    {
        $pid = pcntl_fork();
        // PID needs to be pushed before anything else, for some reason
        $pids[] = $pid;
        $config = DB::getConfig();
        DB::connect($config);
        $this->runChild($class, $pid, $start);
    }
}
