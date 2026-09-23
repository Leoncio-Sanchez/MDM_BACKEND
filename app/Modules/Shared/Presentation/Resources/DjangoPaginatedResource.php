<?php
declare(strict_types=1);

namespace App\Modules\Shared\Presentation\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DjangoPaginatedResource extends ResourceCollection
{
    private string $resourceClass;
    private int $total;

    /**
     * @param array{items: mixed, total: int} $resource
     */
    public function __construct(array $resource, string $resourceClass)
    {
        parent::__construct($resource['items']);
        $this->total = $resource['total'];
        $this->resourceClass = $resourceClass;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'count' => $this->total,
            'next' => null,
            'previous' => null,
            'results' => $this->collection->map(function ($item) use ($request) {
                return (new $this->resourceClass($item))->toArray($request);
            }),
        ];
    }
}
