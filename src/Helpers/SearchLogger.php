<?php

namespace Firesphere\ElasticSearch\Helpers;

use Firesphere\SearchBackend\Models\SearchLog;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\ValidationException;

abstract class SearchLogger
{
    protected $client;

    /**
     * @var array Default options
     */
    protected $options = [];

    public abstract function __construct();


    /**
     * Log the given message and dump it out.
     * Also boot the Log to get the latest errors from Elastic
     *
     * @param string $type
     * @param string $message
     * @throws HTTPException
     * @throws ValidationException
     */
    public static function logMessage($type, $message): void
    {
        $logger = new static();
        $logger->saveLog($type, [$message]);
        /** @var SearchLog $lastError */
        $lastError = SearchLog::get()->last();

        $err = ($lastError === null) ? 'Unknown' : $lastError->getLastErrorLine();
        $errTime = ($lastError === null) ? 'Unknown' : $lastError->Timestamp;
        $message .= sprintf('%sLast known Elastic error:%s%s: %s', PHP_EOL, PHP_EOL, $errTime, $err);
        /** @var LoggerInterface $logger */
        $logger = Injector::inst()->get(LoggerInterface::class);
        $logger->alert($message);
        if (Director::is_cli() || Controller::curr()->getRequest()->getVar('unittest')) {
            Debug::dump($message);
        }
    }

    /**
     * Save the latest Elastic errors to the log
     *
     * @param string $type
     * @param array $logs
     * @throws HTTPException
     * @throws ValidationException
     */
    public function saveLog($type, $logs): void
    {
        foreach ($logs as $error) {
            $filter = [
                'Timestamp' => $error['time'],
                'Index'     => $error['core'] ?? 'x:Unknown',
                'Level'     => $error['level'],
            ];
            $this->findOrCreateLog($type, $filter, $error);
        }
    }


    /**
     * Attempt to find, otherwise create, a log object
     *
     * @param $type
     * @param array $filter
     * @param $error
     * @throws ValidationException
     */
    private function findOrCreateLog($type, array $filter, $error): void
    {
        // Not covered in tests. It's only here to make sure the connection isn't closed by a child process
        $conn = DB::is_active();
        // @codeCoverageIgnoreStart
        if (!$conn) {
            $config = DB::getConfig();
            DB::connect($config);
        }
        // @codeCoverageIgnoreEnd
        if (!SearchLog::get()->filter($filter)->exists()) {
            $logData = [
                'Message' => $error['message'],
                'Type'    => $type,
            ];
            $log = array_merge($filter, $logData);
            SearchLog::create($log)->write();
            if (Director::is_cli() || Controller::curr()->getRequest()->getVar('unittest')) {
                /** @var LoggerInterface $logger */
                $logger = Injector::inst()->get(LoggerInterface::class);
                $logger->error($error['message']);
            }
        }
    }
}
