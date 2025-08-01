<?php
namespace App\Jobs\Middleware;
use Closure;
class IsAuthorized{
    /**
     * Process the queued job.
     * @param  \Closure(object): void  $next
     */
    public function handle(object $job, Closure $next): void {
        //check logged in
        //check valid org and account
        //check authorization of action
        //check balance, invoices ect

        //if valid
            $next($job);
        //else fail job
    }
}
