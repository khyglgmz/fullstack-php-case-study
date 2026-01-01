<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\GeocodeException;
use App\Exceptions\ValidationException;
use App\Http\Request;
use App\Http\Response;
use App\Services\GeocodeService;

class GeocodeController
{
    private GeocodeService $geocodeService;

    public function __construct()
    {
        $this->geocodeService = new GeocodeService();
    }

    public function geocodeAll(Request $request): void
    {
        try {
            $forceRetry = $request->getBodyParam('forceRetry', false);

            $locations = $this->geocodeService->geocodeAll((bool) $forceRetry);
            $summary = $this->geocodeService->getLocationsSummary();

            Response::json([
                'locations' => array_map(fn($loc) => $loc->toPublicArray(), $locations),
                'summary' => $summary
            ], 200);

        } catch (GeocodeException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), $e->getHttpStatusCode());
        } catch (ValidationException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), 400);
        }
    }

    public function list(Request $request): void
    {
        try {
            $status = $request->getQueryParam('status');

            $locations = $this->geocodeService->getLocations($status);
            $summary = $this->geocodeService->getLocationsSummary();

            Response::json([
                'locations' => array_map(fn($loc) => $loc->toPublicArray(), $locations),
                'summary' => $summary
            ], 200);

        } catch (ValidationException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), 400);
        }
    }

    public function retry(Request $request, array $params): void
    {
        try {
            $addressId = (int) $params['addressId'];

            $location = $this->geocodeService->retryAddress($addressId);
            $summary = $this->geocodeService->getLocationsSummary();

            Response::json([
                'location' => $location->toPublicArray(),
                'summary' => $summary
            ], 200);

        } catch (GeocodeException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), $e->getHttpStatusCode());
        } catch (ValidationException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), 400);
        }
    }

    public function initialize(Request $request): void
    {
        try {
            $locations = $this->geocodeService->initializeFromFile();
            $summary = $this->geocodeService->getLocationsSummary();

            Response::json([
                'message' => 'Addresses initialized from file',
                'locations' => array_map(fn($loc) => $loc->toPublicArray(), $locations),
                'summary' => $summary
            ], 200);

        } catch (GeocodeException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), $e->getHttpStatusCode());
        } catch (ValidationException $e) {
            Response::error($e->getErrorCode(), $e->getMessage(), 400);
        }
    }
}
