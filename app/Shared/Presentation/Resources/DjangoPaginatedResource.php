<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DjangoPaginatedResource extends ResourceCollection
{
    private string $resourceClass;

    private int $total;

    /**
     * @param  array{items: mixed, total: int}  $resource
     */
    public function __construct(array $resource, string $resourceClass)
    {
        parent::__construct($resource['items']);
        $this->total = $resource['total'];
        $this->resourceClass = $resourceClass;
    }

    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $items = $this->collection ?? collect();

        return [
            'count' => $this->total,
            'next' => null,
            'previous' => null,
            'results' => $items->map(function (mixed $item) use ($request): mixed {
                $class = $this->resourceClass;
                /** @var JsonResource $resource */
                $resource = new $class($item);

                return $resource->toArray($request);
            })->all(),
        ];
    }
}
