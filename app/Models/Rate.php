<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Rate extends Model
{
    // We don't care when this record was first created
    const CREATED_AT = null;

    // This is the last time we generated a rate from an API call
    const UPDATED_AT = 'date_time_checked';

    protected $table = 'rate';
    protected $fillable = [];

    /** @var null|bool */
    public $forceAliveOrDead;

    /**
     * For finding a record by composite key
     */
    public function scopeFromTo($query, $from, $to)
    {
        return $query->where([
            ['currency_from', '=', $from],
            ['currency_to',   '=', $to]
        ]);
    }

    /**
     * Lumen find by composite key for UPDATE
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        return $this->scopeFromTo(
            $query,
            $this->currency_from,
            $this->currency_to
        );
    }

    /**
     * Is this record still alive? I.e. has it expired
     * CACHE_TTL_SECONDS needs to be declared in .env
     * If     $this->forceAliveOrDead === true, then force alive
     * ElseIF $this->forceAliveOrDead === false, then dead (for testing)
     */
    public function getIsAlive($time = null)
    {
        if ($this->forceAliveOrDead !== null) {
            return $this->forceAliveOrDead;
        }

        if (!$time) {
            $time = time();
        }

        $dateTimeChecked = new \DateTime($this->date_time_checked);
        $ttl = (int) env('CACHE_TTL_SECONDS');

        return ($time - $dateTimeChecked->getTimestamp() <= $ttl);
    }
}