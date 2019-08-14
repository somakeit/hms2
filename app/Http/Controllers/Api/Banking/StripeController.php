<?php

namespace App\Http\Controllers\Api\Banking;

use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response as IlluminateResponse;

class StripeController extends Controller
{
    /**
     * Make a new PaymentIntent.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function makeIntent(Request $request)
    {
        if (count($request->input()) == 0) {
            // nothing sent
            return response()->json([], IlluminateResponse::HTTP_BAD_REQUEST);
        }

        $validatedData = $request->validate([
            'amount' => 'required|integer',
            'type' => 'required|string',
        ]);

        $user = \Auth::user();

        if (is_null($user)) {
            // TODO: guest user, send bad request until we implement something here
            return response()->json([], IlluminateResponse::HTTP_BAD_REQUEST);
        }

        if ($validatedData['type'] == 'SNACKSPACE') {
            $descriptor = 'Snackspace';
        } else {
            $descriptor = 'Donation';
        }

        $intent = PaymentIntent::create([
            'amount' => $validatedData['amount'],
            'currency' => 'gbp',
            'receipt_email' => $user->getEmail(),
            'statement_descriptor' => $descriptor,
            'metadata' => [
                'user_id' => $user->getId(),
                'type' => $validatedData['type'],
            ],
        ]);

        // TODO: validate stripe API response
        // dump($intent);

        $response = [
            'intentId' => $intent->id,
            'clientSecret' => $intent->client_secret,
            'amount' => $intent->amount,
        ];

        return response()->json($response, IlluminateResponse::HTTP_CREATED);
    }

    /**
     * Update a PaymentIntent.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function updateIntent(Request $request)
    {
        if (count($request->input()) == 0) {
            // nothing sent
            return response()->json([], IlluminateResponse::HTTP_BAD_REQUEST);
        }

        $validatedData = $request->validate([
            'intentId' => 'required|string',
            'amount' => 'required|integer',
            'type' => 'required|string',
        ]);

        $user = \Auth::user();

        if (is_null($user)) {
            // TODO: guest user, send bad request until we implement something here
            return response()->json([], IlluminateResponse::HTTP_BAD_REQUEST);
        }

        $intent = PaymentIntent::update(
            $validatedData['intentId'],
            [
                'amount' => $validatedData['amount'],
            ]
        );

        // TODO: validate stripe API response
        // dump($intent);

        $response = [
            'intentId' => $intent->id,
            'amount' => $intent->amount,
        ];

        return response()->json($response);
    }

    /**
     * PaymentIntent confirmed.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function intentSuccess(Request $request)
    {
        if (count($request->input()) == 0) {
            // nothing sent
            return response()->json([], IlluminateResponse::HTTP_BAD_REQUEST);
        }

        $validatedData = $request->validate([
            'intentId' => 'required|string',
            'amount' => 'required|integer',
            'type' => 'required|string',
        ]);

        // As we are not recording anything until we see the charge.success web-hook nothing to do here

        return response(null, IlluminateResponse::HTTP_NO_CONTENT);
    }
}