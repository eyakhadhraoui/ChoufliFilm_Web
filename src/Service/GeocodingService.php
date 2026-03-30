<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private HttpClientInterface $httpClient;
    private string $nominatimUrl = 'https://nominatim.openstreetmap.org/reverse';
    private ?string $googleApiKey;

    public function __construct(HttpClientInterface $httpClient = null, string $googleApiKey = null)
    {
        $this->httpClient = $httpClient ?? HttpClient::create();
        $this->googleApiKey = $googleApiKey;
    }

    /**
     * Get address from latitude and longitude using OpenStreetMap's Nominatim API
     * 
     * @param float $latitude Latitude coordinate
     * @param float $longitude Longitude coordinate
     * @param string $format Response format (json or xml)
     * @param int $zoom Level of detail (0-18)
     * @return array Address data
     * @throws \Exception
     */
    public function getAddressFromCoordinatesOSM(float $latitude, float $longitude, string $format = 'json', int $zoom = 18): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->nominatimUrl, [
                'query' => [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'format' => $format,
                    'zoom' => $zoom,
                    'addressdetails' => 1,
                ],
                'headers' => [
                    'User-Agent' => 'Symfony GeocodingService/1.0',
                    'Accept-Language' => 'en', // Change to your desired language
                ],
            ]);

            $content = $response->getContent();
            $data = json_decode($content, true);

            if (!$data) {
                throw new \Exception('Failed to parse response from Nominatim API');
            }

            return $data;
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            throw new \Exception('Error fetching address data: ' . $e->getMessage());
        }
    }

    /**
     * Get address from latitude and longitude using Google Maps Geocoding API
     * 
     * @param float $latitude Latitude coordinate
     * @param float $longitude Longitude coordinate
     * @return array Address data
     * @throws \Exception If API key is not provided or API request fails
     */
    public function getAddressFromCoordinatesGoogle(float $latitude, float $longitude): array
    {
        if (!$this->googleApiKey) {
            throw new \Exception('Google Maps API key is required');
        }

        try {
            $response = $this->httpClient->request('GET', 'https://maps.googleapis.com/maps/api/geocode/json', [
                'query' => [
                    'latlng' => $latitude . ',' . $longitude,
                    'key' => $this->googleApiKey,
                ],
            ]);

            $content = $response->getContent();
            $data = json_decode($content, true);

            if ($data['status'] !== 'OK') {
                throw new \Exception('Google Maps API error: ' . ($data['error_message'] ?? $data['status']));
            }

            return $data;
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            throw new \Exception('Error fetching address data: ' . $e->getMessage());
        }
    }

    /**
     * Format OpenStreetMap address data into a readable address string
     * 
     * @param array $osmData Response from OSM Nominatim API
     * @return string Formatted address string
     */
    public function formatOsmAddress(array $osmData): string
    {
        if (!isset($osmData['address'])) {
            return $osmData['display_name'] ?? 'Address not found';
        }

        $address = $osmData['address'];
        
        $parts = [];
        
        // Building number and road
        if (isset($address['house_number']) && isset($address['road'])) {
            $parts[] = $address['house_number'] . ' ' . $address['road'];
        } elseif (isset($address['road'])) {
            $parts[] = $address['road'];
        }
        
        // Suburb/neighborhood
        if (isset($address['suburb'])) {
            $parts[] = $address['suburb'];
        } elseif (isset($address['neighbourhood'])) {
            $parts[] = $address['neighbourhood'];
        }
        
        // City/town
        if (isset($address['city'])) {
            $parts[] = $address['city'];
        } elseif (isset($address['town'])) {
            $parts[] = $address['town'];
        } elseif (isset($address['village'])) {
            $parts[] = $address['village'];
        }
        
        // State/province and postal code
        $statePostal = [];
        if (isset($address['state'])) {
            $statePostal[] = $address['state'];
        }
        if (isset($address['postcode'])) {
            $statePostal[] = $address['postcode'];
        }
        if (!empty($statePostal)) {
            $parts[] = implode(', ', $statePostal);
        }
        
        // Country
        if (isset($address['country'])) {
            $parts[] = $address['country'];
        }
        
        return implode(', ', $parts);
    }

    /**
     * Format Google Maps address data into a readable address string
     * 
     * @param array $googleData Response from Google Maps Geocoding API
     * @return string Formatted address string
     */
    public function formatGoogleAddress(array $googleData): string
    {
        if (empty($googleData['results'])) {
            return 'Address not found';
        }
        
        return $googleData['results'][0]['formatted_address'] ?? 'Address not found';
    }

    /**
     * Get formatted address string from coordinates
     * Uses either OpenStreetMap or Google Maps API based on availability of API key
     * 
     * @param float $latitude Latitude coordinate
     * @param float $longitude Longitude coordinate
     * @param string $provider Force a specific provider ('google' or 'osm')
     * @return string Formatted address
     */
    public function getFormattedAddress(float $latitude, float $longitude, string $provider = null): string
    {
        try {
            // If provider is specified, use that
            if ($provider === 'google' && $this->googleApiKey) {
                $data = $this->getAddressFromCoordinatesGoogle($latitude, $longitude);
                return $this->formatGoogleAddress($data);
            } elseif ($provider === 'osm') {
                $data = $this->getAddressFromCoordinatesOSM($latitude, $longitude);
                return $this->formatOsmAddress($data);
            }
            
            // Otherwise use Google if API key is available, fallback to OSM
            if ($this->googleApiKey) {
                $data = $this->getAddressFromCoordinatesGoogle($latitude, $longitude);
                return $this->formatGoogleAddress($data);
            } else {
                $data = $this->getAddressFromCoordinatesOSM($latitude, $longitude);
                return $this->formatOsmAddress($data);
            }
        } catch (\Exception $e) {
            return 'Error retrieving address: ' . $e->getMessage();
        }
    }

    /**
     * Get structured address data from coordinates
     * 
     * @param float $latitude Latitude coordinate
     * @param float $longitude Longitude coordinate
     * @param string $provider Force a specific provider ('google' or 'osm')
     * @return array Structured address data
     */
    public function getStructuredAddress(float $latitude, float $longitude, string $provider = null): array
    {
        try {
            if ($provider === 'google' && $this->googleApiKey) {
                return $this->getAddressFromCoordinatesGoogle($latitude, $longitude);
            } elseif ($provider === 'osm') {
                return $this->getAddressFromCoordinatesOSM($latitude, $longitude);
            }
            
            if ($this->googleApiKey) {
                return $this->getAddressFromCoordinatesGoogle($latitude, $longitude);
            } else {
                return $this->getAddressFromCoordinatesOSM($latitude, $longitude);
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}