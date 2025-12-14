<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    // Cache TTL constants (in seconds)
    public const TTL_SHORT = 300;        // 5 minutes

    public const TTL_MEDIUM = 900;       // 15 minutes

    public const TTL_LONG = 3600;        // 1 hour

    public const TTL_VERY_LONG = 7200;   // 2 hours

    // Cache key prefixes
    public const PREFIX_DASHBOARD = 'dashboard';

    public const PREFIX_PRODUCTS = 'products';

    public const PREFIX_CATEGORIES = 'categories';

    public const PREFIX_MENU = 'menu';

    public const PREFIX_STUDENT = 'student';

    public const PREFIX_STUDENT_SEARCH = 'student_search';

    /**
     * Get or set a cached value with store scoping
     */
    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Forget a cached value
     */
    public function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Flush cache by pattern (for Redis)
     */
    public function flushByPattern(string $pattern): void
    {
        $keys = $this->getKeysByPattern($pattern);

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get keys matching a pattern
     */
    protected function getKeysByPattern(string $pattern): array
    {
        $store = Cache::getStore();

        // For Redis, we can use keys command
        if (method_exists($store, 'connection')) {
            $connection = $store->connection();
            $prefix = config('cache.prefix', '');

            return $connection->keys($prefix.$pattern);
        }

        return [];
    }

    // ==========================================
    // Dashboard Cache Methods
    // ==========================================

    public function getDashboardStatsKey(int $storeId, string $dateRange): string
    {
        return self::PREFIX_DASHBOARD.":stats:{$storeId}:{$dateRange}";
    }

    public function getDashboardSalesChartKey(int $storeId, string $dateRange): string
    {
        return self::PREFIX_DASHBOARD.":sales_chart:{$storeId}:{$dateRange}";
    }

    public function getDashboardTopProductsKey(int $storeId, string $dateRange): string
    {
        return self::PREFIX_DASHBOARD.":top_products:{$storeId}:{$dateRange}";
    }

    public function getDashboardPaymentBreakdownKey(int $storeId, string $dateRange): string
    {
        return self::PREFIX_DASHBOARD.":payment_breakdown:{$storeId}:{$dateRange}";
    }

    public function getDashboardStudentStatsKey(int $storeId, string $dateRange): string
    {
        return self::PREFIX_DASHBOARD.":student_stats:{$storeId}:{$dateRange}";
    }

    public function invalidateDashboard(?int $storeId = null): void
    {
        if ($storeId) {
            $this->flushByPattern(self::PREFIX_DASHBOARD.":*:{$storeId}:*");
        } else {
            $this->flushByPattern(self::PREFIX_DASHBOARD.':*');
        }
    }

    // ==========================================
    // Product Cache Methods
    // ==========================================

    public function getProductsKey(int $storeId, ?int $categoryId = null): string
    {
        $suffix = $categoryId ? ":{$categoryId}" : ':all';

        return self::PREFIX_PRODUCTS.":{$storeId}{$suffix}";
    }

    public function invalidateProducts(?int $storeId = null): void
    {
        if ($storeId) {
            $this->flushByPattern(self::PREFIX_PRODUCTS.":{$storeId}:*");
        } else {
            $this->flushByPattern(self::PREFIX_PRODUCTS.':*');
        }
    }

    // ==========================================
    // Category Cache Methods
    // ==========================================

    public function getCategoriesTreeKey(int $storeId): string
    {
        return self::PREFIX_CATEGORIES.":tree:{$storeId}";
    }

    public function getCategoryChildrenKey(int $storeId, ?int $parentId): string
    {
        $suffix = $parentId ? ":{$parentId}" : ':root';

        return self::PREFIX_CATEGORIES.":children:{$storeId}{$suffix}";
    }

    public function invalidateCategories(?int $storeId = null): void
    {
        if ($storeId) {
            $this->flushByPattern(self::PREFIX_CATEGORIES.":*:{$storeId}*");
        } else {
            $this->flushByPattern(self::PREFIX_CATEGORIES.':*');
        }
    }

    // ==========================================
    // Menu Cache Methods
    // ==========================================

    public function getMenuKey(int $storeId, ?int $categoryId = null, ?string $search = null): string
    {
        $parts = [self::PREFIX_MENU, $storeId];

        if ($categoryId) {
            $parts[] = "cat:{$categoryId}";
        }

        if ($search) {
            $parts[] = 'q:'.md5($search);
        }

        return implode(':', $parts);
    }

    public function invalidateMenu(?int $storeId = null): void
    {
        if ($storeId) {
            $this->flushByPattern(self::PREFIX_MENU.":{$storeId}*");
        } else {
            $this->flushByPattern(self::PREFIX_MENU.':*');
        }
    }

    // ==========================================
    // Student Cache Methods
    // ==========================================

    public function getStudentKey(int $studentId): string
    {
        return self::PREFIX_STUDENT.":{$studentId}";
    }

    public function getStudentBalanceKey(int $studentId): string
    {
        return self::PREFIX_STUDENT.":balance:{$studentId}";
    }

    public function getStudentTransactionsKey(int $studentId): string
    {
        return self::PREFIX_STUDENT.":transactions:{$studentId}";
    }

    public function invalidateStudent(int $studentId): void
    {
        $this->forget($this->getStudentKey($studentId));
        $this->forget($this->getStudentBalanceKey($studentId));
        $this->forget($this->getStudentTransactionsKey($studentId));
    }

    public function getStudentSearchKey(int $storeId, string $query): string
    {
        return self::PREFIX_STUDENT_SEARCH.":{$storeId}:".md5($query);
    }

    public function invalidateStudentSearch(?int $storeId = null): void
    {
        if ($storeId) {
            $this->flushByPattern(self::PREFIX_STUDENT_SEARCH.":{$storeId}:*");
        } else {
            $this->flushByPattern(self::PREFIX_STUDENT_SEARCH.':*');
        }
    }

    // ==========================================
    // Helper Methods
    // ==========================================

    /**
     * Generate a date range hash for cache keys
     */
    public function getDateRangeHash(string $startDate, string $endDate): string
    {
        return md5("{$startDate}_{$endDate}");
    }
}
