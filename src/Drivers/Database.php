<?php

namespace Hollyit\LaravelLock\Drivers;

use Exception;
use Hollyit\LaravelLock\Contracts\LockDriver;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Symfony\Component\Lock\Exception\InvalidArgumentException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\NotSupportedException;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\Store\ExpiringStoreTrait;
use Symfony\Component\Lock\StoreInterface;

class Database implements StoreInterface, LockDriver

{
    use ExpiringStoreTrait;

    protected $table;

    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $initialTtl;

    protected $gcProbability = 0.01;

    public function __construct($options, ConnectionInterface $connection)
    {
        $this->table = $options['table'];
        $this->connection = $connection;
        $this->initialTtl = config('lock.default_ttl', 300);
    }

    /**
     * Get a query builder for the cache table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table()
    {
        return $this->connection->table($this->table);
    }

    /**
     * @return \Symfony\Component\Lock\Store\FlockStore|\Symfony\Component\Lock\StoreInterface
     */
    public function getStore()
    {
        return $this;
    }

    /**
     * Stores the resource if it's not locked by someone else.
     *
     * @param  \Symfony\Component\Lock\Key  $key
     * @throws \Exception
     */
    public function save(Key $key)
    {
        $key->reduceLifetime($this->initialTtl);

        try {
            $this->table()
                ->insert([
                    'id'      => $this->getHashedKey($key),
                    'token'   => $this->getUniqueToken($key),
                    'expires' => time() + $this->initialTtl,
                ]);
        } catch (Exception $e) {
            // the lock is already acquired. It could be us. Let's try to put off.
            $this->putOffExpiration($key, $this->initialTtl);
        }
        if ($this->gcProbability > 0 && (1.0 === $this->gcProbability || (random_int(0,
                        PHP_INT_MAX) / PHP_INT_MAX) <= $this->gcProbability)) {
            $this->prune();
        }

        $this->checkNotExpired($key);
    }

    /**
     * Waits until a key becomes free, then stores the resource.
     *
     * If the store does not support this feature it should throw a NotSupportedException.
     *
     * @param  \Symfony\Component\Lock\Key  $key
     */
    public function waitAndSave(Key $key)
    {
        throw new NotSupportedException(sprintf('The store "%s" does not supports blocking locks.', __METHOD__));
    }

    /**
     * Extends the ttl of a resource.
     *
     * If the store does not support this feature it should throw a NotSupportedException.
     *
     * @param  \Symfony\Component\Lock\Key  $key
     * @param  float  $ttl  amount of seconds to keep the lock in the store
     *
     */
    public function putOffExpiration(Key $key, $ttl)
    {
        if ($ttl < 1) {
            throw new InvalidArgumentException(sprintf('%s() expects a TTL greater or equals to 1 second. Got %s.',
                __METHOD__, $ttl));
        }

        $key->reduceLifetime($ttl);
        $uniqueToken = $this->getUniqueToken($key);
        $rowCount = $this->table()
            ->where([
                'id' => $this->getHashedKey($key),
            ])
            ->where(function (Builder $query) use ($uniqueToken) {
                $query->where('token', $uniqueToken)
                    ->orWhere('expires', '<', time());
            })
            ->update([
                'expires' => time() + $this->initialTtl,
                'token'   => $uniqueToken,
            ]);

        // If this method is called twice in the same second, the row wouldn't be updated. We have to call exists to know if we are the owner
        if (! $rowCount && ! $this->exists($key)) {
            throw new LockConflictedException();
        }

        $this->checkNotExpired($key);
    }

    /**
     * Removes a resource from the storage.
     *
     * @param  \Symfony\Component\Lock\Key  $key
     */
    public function delete(Key $key)
    {

        $this->table()
            ->where([
                'id'    => $this->getHashedKey($key),
                'token' => $this->getUniqueToken($key),
            ])
            ->delete();
    }

    /**
     * Returns whether or not the resource exists in the storage.
     *
     * @param  \Symfony\Component\Lock\Key  $key
     * @return boolean
     */
    public function exists(Key $key)
    {


        return $this->table()
            ->where('id', $this->getHashedKey($key))
            ->where('token', $this->getUniqueToken($key))
            ->where('expires', '>', time())
            ->exists();
    }

    /**
     * Returns a hashed version of the key.
     *
     * @param  \Symfony\Component\Lock\Key  $key
     * @return string
     */
    private function getHashedKey(Key $key): string
    {
        return hash('sha256', (string)$key);
    }

    private function getUniqueToken(Key $key): string
    {
        if (! $key->hasState(__CLASS__)) {
            $token = base64_encode(random_bytes(32));
            $key->setState(__CLASS__, $token);
        }

        return $key->getState(__CLASS__);
    }

    /**
     * Cleans up the table by removing all expired locks.
     */
    private function prune(): void
    {
        $this->table()
            ->where('expires', '<', time())
            ->delete();
    }
}
