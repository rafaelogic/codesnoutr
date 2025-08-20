<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Example service demonstrating enhanced snake_case variable exception handling
 */
class CurlService
{
    /**
     * Process a cURL request and extract information
     * Enhanced scanner now recognizes legitimate snake_case usage with PHP constants
     */
    public function processCurlRequest(string $url): array
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        
        $response = curl_exec($ch);
        
        // ✅ These snake_case variables should NOT be flagged - they're assigned from PHP constants
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $redirect_count = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
        
        curl_close($ch);
        
        return [
            'http_code' => $http_code,
            'header_size' => $header_size,
            'content_type' => $content_type,
            'total_time' => $total_time,
            'redirect_count' => $redirect_count,
            'response' => $response,
        ];
    }

    /**
     * Process file information
     * Enhanced scanner recognizes PHP function assignments
     */
    public function processFileInfo(string $filename): array
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException("File not found: {$filename}");
        }

        // ✅ These snake_case variables should NOT be flagged - they're assigned from PHP functions
        $file_size = filesize($filename);
        $mime_type = mime_content_type($filename);
        $is_readable = is_readable($filename);
        $is_writable = is_writable($filename);
        $file_perms = fileperms($filename);
        
        // ✅ These should NOT be flagged - they're accessing array keys with snake_case
        $path_info = pathinfo($filename);
        $file_extension = $path_info['extension'] ?? '';
        $base_name = $path_info['basename'] ?? '';
        
        return [
            'file_size' => $file_size,
            'mime_type' => $mime_type,
            'is_readable' => $is_readable,
            'is_writable' => $is_writable,
            'file_perms' => $file_perms,
            'file_extension' => $file_extension,
            'base_name' => $base_name,
        ];
    }

    /**
     * Process environment configuration
     * Enhanced scanner recognizes environment variable assignments
     */
    public function getEnvironmentConfig(): array
    {
        // ✅ These snake_case variables should NOT be flagged - they're assigned from env functions
        $database_url = env('DATABASE_URL');
        $api_key = getenv('API_KEY');
        $server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        
        return compact('database_url', 'api_key', 'server_name', 'document_root', 'request_uri');
    }

    /**
     * Process API response
     * Enhanced scanner recognizes JSON decode and API response patterns
     */
    public function processApiResponse(string $jsonResponse): array
    {
        // ✅ These should NOT be flagged - assigned from json_decode
        $decoded_data = json_decode($jsonResponse, true);
        
        if (!$decoded_data) {
            throw new \InvalidArgumentException('Invalid JSON response');
        }

        // ✅ These should NOT be flagged - they're accessing API response data with snake_case keys
        $user_id = $decoded_data['user_id'] ?? null;
        $access_token = $decoded_data['access_token'] ?? '';
        $refresh_token = $decoded_data['refresh_token'] ?? '';
        $expires_in = $decoded_data['expires_in'] ?? 3600;
        $token_type = $decoded_data['token_type'] ?? 'bearer';
        
        return [
            'user_id' => $user_id,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_in' => $expires_in,
            'token_type' => $token_type,
        ];
    }

    /**
     * Example of variables that SHOULD still be flagged
     * These don't meet any of the exception criteria
     */
    public function badNamingExample(): array
    {
        // ❌ These SHOULD be flagged - no legitimate reason for snake_case
        $user_name = 'John Doe';          // Should be $userName
        $some_variable = 123;             // Should be $someVariable
        $another_bad_name = 'test';       // Should be $anotherBadName
        
        return compact('user_name', 'some_variable', 'another_bad_name');
    }

    /**
     * Database-related context
     * Enhanced scanner recognizes database column name patterns
     */
    public function processDatabaseData(array $userData): array
    {
        // ✅ These should NOT be flagged - they match common database column patterns
        $first_name = $userData['first_name'] ?? '';
        $last_name = $userData['last_name'] ?? '';
        $email_verified_at = $userData['email_verified_at'] ?? null;
        $created_at = $userData['created_at'] ?? now();
        $updated_at = $userData['updated_at'] ?? now();
        $remember_token = $userData['remember_token'] ?? '';
        
        return [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email_verified_at' => $email_verified_at,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'remember_token' => $remember_token,
        ];
    }
}
