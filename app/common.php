<?php
declare (strict_types = 1);

if (!function_exists('oss_upload_local_file')) {
    function oss_upload_local_file(string $filePath, ?string $ossKey = null): array
    {
        return OssHelper::uploadLocalFile($filePath, $ossKey);
    }
}

if (!function_exists('oss_upload_file')) {
    function oss_upload_file($file, string $folder = 'images', ?string $customName = null): array
    {
        return OssHelper::uploadUploadedFile($file, $folder, $customName);
    }
}

if (!function_exists('jwt_generate_token')) {
    function jwt_generate_token(array $claims = [], ?int $ttl = null): string
    {
        return JwtHelper::generate($claims, $ttl);
    }
}

if (!function_exists('jwt_parse_token')) {
    function jwt_parse_token(string $token, bool $verifyExpiration = true): array
    {
        return JwtHelper::decode($token, $verifyExpiration);
    }
}

if (!function_exists('snowflake_id')) {
    function snowflake_id(?int $workerId = null, ?int $datacenterId = null): string
    {
        return SnowflakeHelper::generate($workerId, $datacenterId);
    }
}

if (!function_exists('admin_password_hash')) {
    function admin_password_hash(string $password, string $salt): string
    {
        return hash('sha256', $password . $salt);
    }
}

if (!function_exists('admin_verify_password')) {
    function admin_verify_password(string $plainPassword, string $storedHash, string $salt = ''): bool
    {
        if ($storedHash === '') {
            return false;
        }

        if (strpos($storedHash, '$2y$') === 0 || strpos($storedHash, '$argon2') === 0) {
            return password_verify($plainPassword, $storedHash);
        }

        $candidates = array_unique([
            admin_password_hash($plainPassword, $salt),
            md5($plainPassword . $salt),
            md5(md5($plainPassword) . $salt),
            $plainPassword,
        ]);

        foreach ($candidates as $candidate) {
            if (hash_equals($storedHash, $candidate)) {
                return true;
            }
        }

        return false;
    }
}
