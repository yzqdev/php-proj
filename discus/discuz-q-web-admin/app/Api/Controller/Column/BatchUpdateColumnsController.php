<?php

/**
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Api\Controller\Column;

use App\Api\Serializer\ColumnSerializer;
use App\Commands\Column\EditColumn;
use Discuz\Api\Controller\AbstractListController;
use Discuz\Auth\AssertPermissionTrait;
use Discuz\Auth\Exception\PermissionDeniedException;
use Exception;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class BatchUpdateColumnsController extends AbstractListController
{
    use AssertPermissionTrait;

    /**
     * {@inheritdoc}
     */
    public $serializer = ColumnSerializer::class;

    /**
     * @var Dispatcher
     */
    protected $bus;

    /**
     * @param Dispatcher $bus
     */
    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $this->assertAdmin($request->getAttribute('actor'));
        $actor = $request->getAttribute('actor');
        $data = $request->getParsedBody()->get('data', []);
        $ip = ip($request->getServerParams());

        $meta = [];

        $categories = collect($data)
            ->unique('id')
            ->keyBy('id')
            ->map(function ($category, $id) use ($actor, $ip, &$meta) {
                try {
                    return $this->bus->dispatch(
                       new EditColumn($id, $actor, $category, $ip)
                    );
                } catch (ValidationException $e) {
                    $meta[] = ['id' => $id, 'message' => $e->errors()];
                } catch (Exception $e) {
                    $meta[] = [
                        'id' => $id,
                        'message' => Str::of(get_class($e))
                            ->afterLast('\\')
                            ->beforeLast('Exception')
                            ->snake()
                            ->__toString(),
                    ];
                }
            })
            ->filter();

        $document->setMeta($meta);

        return $categories;
    }
}
