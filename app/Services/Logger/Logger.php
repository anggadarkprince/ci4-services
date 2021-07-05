<?php

namespace App\Services\Logger;

use CodeIgniter\Database\Query;
use Config\Database;
use Config\Services;

class Logger
{
    /**
     * Set access log.
     *
     * @param null $data
     * @param Database|null $config
     */
    public static function access($data = null, Database $config = null)
    {
        $config = $config ?? new Database();

        $logGroup = $config->log ?? $config->defaultGroup;

        $db = Database::connect($logGroup, true);

        $db->table('logs')->insert($data ?? [
                'event_type' => 'access',
                'event_access' => uri_string(),
                'data' => json_encode([
                    'host' => site_url('/', false),
                    'path' => uri_string(true),
                    'query' => Services::uri()->getQuery(),
                    'ip' => Services::request()->getIPAddress(),
                    'platform' => Services::request()->getUserAgent()->getPlatform(),
                    'browser' => Services::request()->getUserAgent()->getBrowser(),
                    'is_mobile' => Services::request()->getUserAgent()->isMobile(),
                ]),
                'created_by' => Services::auth()->user('id', 0),
                'created_at' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Set query log.
     *
     * @param Query $query
     */
    public static function query(Query $query)
    {
        $queryStatement = $query->getQuery();

        if (preg_match('/(insert|update|delete)/i', $queryStatement)) {
            if (!preg_match('/(logs|sessions)/i', $queryStatement)) {
                $type = 'command';
                if (preg_match('/insert/i', $queryStatement)) {
                    $method = 'insert';
                } elseif (preg_match('/insert/i', $queryStatement)) {
                    $method = 'update';
                } elseif (preg_match('/insert/i', $queryStatement)) {
                    $method = 'delete';
                } else {
                    $type = 'query';
                    $method = 'select';
                }

                $config = new Database();

                $logGroup = $config->log ?? $config->defaultGroup;

                $db = Database::connect($logGroup, true);

                $db->table('logs')->insert([
                    'event_type' => 'query',
                    'event_access' => uri_string(true),
                    'data' => json_encode([
                        'type' => $type,
                        'method' => $method,
                        'query' => $queryStatement,
                    ]),
                    'created_by' => Services::auth()->user('id', 0),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }
}