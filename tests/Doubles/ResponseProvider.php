<?php

declare(strict_types=1);

namespace Kudashevs\LaravelLastModified\Tests\Doubles;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

class ResponseProvider
{
    public function stubResponseWithAModel(): Response
    {
        return $this->stubResponse(
            new class {
                public function getData(): array
                {
                    $model = new class extends Model {
                        public function getAttributes(): array
                        {
                            return [
                                'created_at' => '2024-10-01 12:00:00',
                                'updated_at' => '2024-12-01 12:00:00',
                                'posted_at' => '2024-11-01 12:00:00',
                            ];
                        }
                    };

                    return [
                        'test' => $model,
                    ];
                }
            }
        );
    }

    public function stubResponseWithAStamplessModel(): Response
    {
        return $this->stubResponse(
            new class {
                public function getData(): array
                {
                    $model = new class extends Model {
                        public function getAttributes(): array
                        {
                            return [];
                        }
                    };

                    return [
                        'test' => $model,
                    ];
                }
            }
        );
    }

    public function stubResponseWithACollection(): Response
    {
        return $this->stubResponse(
            new class {
                public function getData(): array
                {
                    $model = new class extends Model {
                        public function getAttributes(): array
                        {
                            return [
                                'created_at' => '2023-10-01 12:00:00',
                                'updated_at' => '2023-12-01 12:00:00',
                                'posted_at' => '2023-11-01 12:00:00',
                            ];
                        }
                    };

                    return [
                        'test' => collect([$model]),
                    ];
                }
            }
        );
    }

    public function stubResponseWithAnEmptyCollection(): Response
    {
        return $this->stubResponse(
            new class {
                public function getData(): array
                {
                    return [
                        'test' => collect([]),
                    ];
                }
            }
        );
    }

    public function stubResponseWithAPaginator(): Response
    {
        return $this->stubResponse(
            new class {
                public function getData(): array
                {
                    $mock = namedMock('Illuminate\Pagination\LengthAwarePaginator');
                    $mock->shouldReceive('isNotEmpty')->andReturn(true);
                    $mock->shouldReceive('items')
                        ->andReturn([
                            new class extends Model {
                                public function getAttributes(): array
                                {
                                    return [
                                        'created_at' => '2022-10-01 12:00:00',
                                        'updated_at' => '2022-12-01 12:00:00',
                                        'posted_at' => '2022-11-01 12:00:00',
                                    ];
                                }
                            },
                        ]);

                    return [
                        'test' => $mock,
                    ];
                }
            }
        );
    }

    public function stubResponseWithAnEmptyPaginator(): Response
    {
        return $this->stubResponse(
            new class {
                public function getData(): array
                {
                    $mock = namedMock('Illuminate\Pagination\LengthAwarePaginator');
                    $mock->shouldReceive('isNotEmpty')->andReturn(false);
                    $mock->shouldReceive('items')
                        ->andReturn([]);

                    return [
                        'test' => $mock,
                    ];
                }
            }
        );
    }

    public function stubResponseFromCache(): Response
    {
        $response = new Response('', 200, []);
        $response->original = new class {
            public function getEngine(): object
            {
                return new class {
                    public function getCompiler(): object
                    {
                        return new class {
                            public function getCompiledPath(string $any): string
                            {
                                return __FILE__;
                            }
                        };
                    }
                };
            }

            public function getPath(): string
            {
                return __FILE__;
            }
        };

        return $response;
    }

    public function stubResponse(object $original): Response
    {
        $response = new Response('', 200, []);
        $response->original = $original;

        return $response;
    }

    public function stubResponseWithNothing(): Response
    {
        $response = new Response('', 200, []);
        $response->original = null;

        return $response;
    }
}
