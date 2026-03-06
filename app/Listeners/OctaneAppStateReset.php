<?php
namespace App\Listeners;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OctaneAppStateReset
{
    /**
     * Summary of handle
     * no type hint RequestReceived $event as we can use diff events
     * @param \Laravel\Octane\Events\RequestReceived $event
     * @return void
     */
    public function handle($event): void
    {
        /**
         * $event->request->attributes->remove('oauth');
         * handled at Octane::prepareApplicationForNextRequest()
         * [GiveNewRequestInstanceToApplication::class]
         **/

        Model::clearBootedModels();
        Carbon::setTestNow();
    }
}
