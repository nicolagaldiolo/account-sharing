<?php

namespace App\Http\Middleware;

use Closure;
use Stripe\Exception\SignatureVerificationException;
use Stripe\WebhookSignature;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyWebhookSignature
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function handle($request, Closure $next)
    {
        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('custom.stripe.webhook.secret'),
                config('cashier.webhook.tolerance')
            );
        } catch (SignatureVerificationException $exception) {
            // Add Secret Connect Verify
            try {
                WebhookSignature::verifyHeader(
                    $request->getContent(),
                    $request->header('Stripe-Signature'),
                    config('custom.stripe.webhook.secret_connect'),
                    config('cashier.webhook.tolerance')
                );
            } catch (SignatureVerificationException $exception) {
                throw new AccessDeniedHttpException($exception->getMessage(), $exception);
            }
        }

        return $next($request);
    }
}
