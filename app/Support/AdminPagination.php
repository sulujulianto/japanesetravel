<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;

final class AdminPagination
{
    /**
     * @template TValue
     *
     * @param  LengthAwarePaginator<int, TValue>  $paginator
     * @return array<string, mixed>
     */
    public static function serialize(LengthAwarePaginator $paginator, string $summary): array
    {
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $pageNumbers = array_values(array_unique(array_filter(
            [1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage],
            fn (int $page): bool => $page >= 1 && $page <= $lastPage
        )));
        sort($pageNumbers);

        return [
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
            'previousUrl' => self::relativeUrl($paginator->previousPageUrl()),
            'nextUrl' => self::relativeUrl($paginator->nextPageUrl()),
            'pages' => array_map(fn (int $page): array => [
                'page' => $page,
                'url' => self::relativeUrl($paginator->url($page)) ?? $paginator->url($page),
                'active' => $page === $currentPage,
            ], $pageNumbers),
            'summary' => $summary,
        ];
    }

    private static function relativeUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);
        if (! is_string($path)) {
            return $url;
        }

        return $path.(is_string($query) && $query !== '' ? '?'.$query : '');
    }
}
