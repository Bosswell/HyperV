<?php


namespace App\Lock;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\RedisStore;

final class LockFactory
{
    private static ?RedisStore $redisStore = null;

    private static array $locks = [];

    public static function create(string $lockName): LockInterface
    {
        global $kernel;

        if (self::$redisStore === null) {
            self::$redisStore = new RedisStore(
                RedisAdapter::createConnection($kernel->getContainer()->getParameter('redis.default.dsn'))
            );
        }

        if (array_key_exists($lockName, self::$locks)) {
            return self::$locks[$lockName];
        }

        $lock = (new Factory(self::$redisStore))
            ->createLock($lockName);

        self::$locks[$lockName] = $lock;

        return $lock;
    }
}
