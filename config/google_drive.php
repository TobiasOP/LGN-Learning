<?php
// config/google_drive.php

class GoogleDriveAPI {
    private $apiKey;
    
    public function __construct() {
        // Ganti dengan API Key Google Cloud Console Anda
        // Cara mendapatkan:
        // 1. Buka Google Cloud Console
        // 2. Buat project baru
        // 3. Enable Google Drive API
        // 4. Buat API Key di Credentials
        $this->apiKey = '643630029178-5kgbsqlvakvrjbuup2ksk5i3tsmf09fj.apps.googleusercontent.com';
    }
    
    /**
     * Get embed URL for iframe player
     */
    public function getEmbedUrl($fileId) {
        return "https://drive.google.com/file/d/{$fileId}/preview";
    }
    
    /**
     * Get direct view URL
     */
    public function getViewUrl($fileId) {
        return "https://drive.google.com/file/d/{$fileId}/view";
    }
    
    /**
     * Get download URL
     */
    public function getDownloadUrl($fileId) {
        return "https://drive.google.com/uc?export=download&id={$fileId}";
    }
    
    /**
     * Get streaming URL with API key
     */
    public function getStreamUrl($fileId) {
        return "https://www.googleapis.com/drive/v3/files/{$fileId}?alt=media&key={$this->apiKey}";
    }
    
    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl($fileId, $size = 's220') {
        return "https://drive.google.com/thumbnail?id={$fileId}&sz={$size}";
    }
    
    /**
     * Get file metadata
     */
    public function getFileMetadata($fileId) {
        $url = "https://www.googleapis.com/drive/v3/files/{$fileId}";
        $url .= "?fields=id,name,mimeType,size,thumbnailLink,videoMediaMetadata";
        $url .= "&key={$this->apiKey}";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    /**
     * Validate file ID format
     */
    public function isValidFileId($fileId) {
        return preg_match('/^[a-zA-Z0-9_-]{20,}$/', $fileId);
    }
    
    /**
     * Extract file ID from various Google Drive URL formats
     */
    public function extractFileId($url) {
        $patterns = [
            '/\/file\/d\/([a-zA-Z0-9_-]+)/',
            '/id=([a-zA-Z0-9_-]+)/',
            '/\/d\/([a-zA-Z0-9_-]+)/',
            '/^([a-zA-Z0-9_-]{20,})$/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Check if file is accessible
     */
    public function isFileAccessible($fileId) {
        $metadata = $this->getFileMetadata($fileId);
        return $metadata !== null;
    }
}