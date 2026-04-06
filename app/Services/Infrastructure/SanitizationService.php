<?php

namespace App\Services\Infrastructure;

class SanitizationService
{
    /**
     * Sanitize HTML content (allow limited tags)
     */
    public function sanitizeHtml(string $input): string
    {
        // Allow only safe HTML tags
        $allowed = '<p><br><strong><em><ul><ol><li><b><i>';
        
        return strip_tags($input, $allowed);
    }
    
    /**
     * Sanitize plain text (escape all HTML)
     */
    public function sanitizeText(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize array of strings
     */
    public function sanitizeArray(array $data): array
    {
        return array_map(function($item) {
            if (is_string($item)) {
                return $this->sanitizeText($item);
            } elseif (is_array($item)) {
                return $this->sanitizeArray($item);
            }
            return $item;
        }, $data);
    }
    
    /**
     * Sanitize JSON field
     */
    public function sanitizeJson(string $json): string
    {
        $data = json_decode($json, true);
        
        if (!is_array($data)) {
            return '[]';
        }
        
        $sanitized = $this->sanitizeArray($data);
        
        return json_encode($sanitized);
    }
    
    /**
     * Remove potentially dangerous content
     */
    public function removeDangerousContent(string $input): string
    {
        // Remove script tags
        $input = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $input);
        
        // Remove event handlers
        $input = preg_replace('/\son\w+\s*=\s*["\'][^"\']*["\']/i', '', $input);
        
        // Remove javascript: protocol
        $input = preg_replace('/javascript:/i', '', $input);
        
        return $input;
    }
}
