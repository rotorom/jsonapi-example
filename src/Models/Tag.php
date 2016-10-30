<?php
declare(strict_types=1);
/*
 * Copyright 2015-2016 Xenofon Spafaridis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Phramework\Examples\JSONAPI\Models;

use Phramework\JSONAPI\Resource;
use Phramework\Phramework;
use Phramework\Database\Database;
use Phramework\JSONAPI\Fields;
use Phramework\JSONAPI\Filter;
use Phramework\JSONAPI\Page;
use Phramework\JSONAPI\Sort;
use Phramework\JSONAPI\Relationship;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class Tag extends \Phramework\Examples\JSONAPI\Model
{
    protected static $type      = 'tag';
    protected static $endpoint  = 'tag';
    protected static $table     = 'tag';

    /**
     * @param Page     $page
     * @param Filter   $filter
     * @param Sort     $sort
     * @param Fields   $fields
     * @param mixed ...$additionalParameters
     * @return Resource[]
     */
    public static function get(
        Page   $page = null,
        Filter $filter = null,
        Sort   $sort = null,
        Fields $fields = null,
        ...$additionalParameters
    ) {
        $query = static::handleGet(
            'SELECT 
              {{fields}}
            FROM "tag"
            WHERE "status" <> ?
              {{filter}}
              {{sort}}
              {{page}}',
            $page,
            $filter,
            $sort,
            $fields
        );

        $records = Database::executeAndFetchAll(
            $query,
            [
                '0'
            ]
        );

        array_walk(
            $records,
            [static::class, 'prepareRecord']
        );

        return static::collection($records, $fields);
    }

    /**
     * @param string $articleId
     * @return string[]
     */
    public static function getRelationshipArticle(
        string $articleId,
        Fields $fields = null,
        $flags = Resource::PARSE_DEFAULT
    ) :array {
        $ids = Database::executeAndFetchAllArray(
            'SELECT "article-tag"."tag_id"
            FROM "article-tag"
            JOIN "tag"
             ON "tag"."id" = "article-tag"."tag_id"
            WHERE
              "article-tag"."article_id" = ?
              AND "article-tag"."status" <> 0
              AND "tag"."status" <> 0',
            [$articleId]
        );

        return $ids;
    }

    /**
     * @return \stdClass
     */
    public static function getRelationships()
    {
        return (object) [
            'article' => new Relationship(
                Article::class,
                Relationship::TYPE_TO_MANY,
                null,
                [Article::class, 'getRelationshipTag']
            ),
        ];
    }
}
