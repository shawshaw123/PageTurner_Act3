<?php

namespace App\Traits;

trait Shardable
{
    /**
     * Get the shard connection based on model ID
     */
    public function getShardConnection(): string
    {
        // Simple modulo-based routing for 4 shards
        $shardId = $this->id % 4;
        return "mysql_shard_{$shardId}";
    }

    /**
     * Switch to shard connection
     */
    public function onShard(): self
    {
        return $this->setConnection($this->getShardConnection());
    }
}
