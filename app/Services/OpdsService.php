<?php

namespace App\Services;

use App\Enums\BookFormatEnum;
use App\Enums\EntityEnum;
use App\Models\Book;
use App\Models\TagExtend;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\ArrayToXml\ArrayToXml;

class OpdsService
{
    public const FEED = [
        [
            'key' => 'authors',
            'model' => 'Author',
            'title' => 'Authors',
            'content' => 'Authors availables',
            'cover_thumbnail' => '',
            'route' => 'front.opds.authors',
        ],
        [
            'key' => 'series',
            'model' => 'Serie',
            'title' => 'Series',
            'content' => 'Series availables',
            'cover_thumbnail' => '',
            'route' => 'front.opds.series',
        ],
    ];

    public function __construct(
        public string $version,
        public EntityEnum $entity,
        public string $route,
        public Collection|Model $data,
    ) {
    }

    public function feed(): array
    {
        $id = strtolower(config('app.name'));
        $date = new DateTime();
        $date = $date->format('Y-m-d H:i:s');

        $entries = [];
        if ($this->data instanceof Collection) {
            foreach ($this->data as $key => $entry) {
                if (EntityEnum::book === $this->entity) {
                    /** @var Book $entry */
                    $templateEntry = $this->entryBook($entry);
                } else {
                    $templateEntry = $this->entry($entry);
                }

                array_push($entries, $templateEntry);
            }
        } elseif ($this->data instanceof Book) {
            $templateEntry = $this->entryBook($this->data);
            $entries = $templateEntry;
        }

        return $entries;
    }

    public function template(string $title = null)
    {
        $id = Str::slug(config('app.name'));
        $id .= ':'.Str::slug($this->entity->value);
        $id .= $title ? ':'.Str::slug($title) : null;

        $feed_title = config('app.name').' OPDS';
        $feed_title .= ': '.ucfirst(strtolower($this->entity->value));
        $feed_title .= null !== $title ? ': '.$title : null;

        $date = new DateTime();
        $date = $date->format('Y-m-d H:i:s');

        $feed_links = [
            'xmlns:app' => 'http://www.w3.org/2007/app',
            'xmlns:opds' => 'http://opds-spec.org/2010/catalog',
            'xmlns:opensearch' => 'http://a9.com/-/spec/opensearch/1.1/',
            'xmlns:odl' => 'http://opds-spec.org/odl',
            'xmlns:dcterms' => 'http://purl.org/dc/terms/',
            'xmlns' => 'http://www.w3.org/2005/Atom',
            'xmlns:thr' => 'http://purl.org/syndication/thread/1.0',
            'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
        ];

        $feed = (array) [
            'id' => $id,
            '__custom:link:1' => [
                '_attributes' => [
                    'rel' => 'start',
                    'href' => route('front.opds.feed', ['version' => $this->version]),
                    'type' => 'application/atom+xml;profile=opds-catalog;kind=navigation',
                    'title' => 'Home',
                ],
            ],
            '__custom:link:2' => [
                '_attributes' => [
                    'rel' => 'self',
                    'href' => $this->route,
                    'type' => 'application/atom+xml;profile=opds-catalog;kind=navigation',
                    'title' => 'self',
                ],
            ],
            '__custom:link:3' => [
                '_attributes' => [
                    'rel' => 'search',
                    'href' => route('front.opds.feed', ['version' => $this->version]),
                    'type' => 'application/atom+xml;profile=opds-catalog;kind=navigation',
                    'title' => 'Search here',
                ],
            ],
            'title' => $feed_title,
            'updated' => $date,
            'author' => [
                'name' => config('app.name'),
                'uri' => config('app.url'),
            ],
            'entry' => $this->feed(),
        ];

        return ArrayToXml::convert($feed, [
            'rootElementName' => 'feed',
            '_attributes' => $feed_links,
        ], true, 'UTF-8');
    }

    public function entry(object $entry): array
    {
        $app = strtolower(config('app.name'));
        $date = new DateTime();
        $date = $date->format('Y-m-d H:i:s');

        $title = $entry->title ?? "{$entry->lastname} {$entry->firstname}";
        $description = $entry->content_opds ?? $entry->content;
        $route = $entry->show_opds_link ?? $entry->route;

        return [
            'title' => $title,
            'updated' => $date,
            'id' => $app.':'.Str::slug($this->entity->value).':'.Str::slug($title),
            'content' => [
                '_attributes' => [
                    'type' => 'text',
                ],
                '_value' => (string) "{$title}, {$description}",
            ],
            '__custom:link:1' => [
                '_attributes' => [
                    'href' => $route,
                    'type' => 'application/atom+xml;profile=opds-catalog;kind=navigation',
                ],
            ],
            '__custom:link:2' => [
                '_attributes' => [
                    'href' => $entry->cover_thumbnail ?? null,
                    'type' => 'image/png',
                    'rel' => 'http://opds-spec.org/image/thumbnail',
                ],
            ],
        ];
    }

    public function entryBook(Book $book)
    {
        $app = strtolower(config('app.name'));
        $date = new DateTime();
        $date = $date->format('Y-m-d H:i:s');

        $id = $app.':books:';
        $id .= $book->serie ? Str::slug($book->serie->title).':' : null;
        $id .= $book->slug;

        $categories = [];
        $tags = $book->tags;

        /** @var TagExtend $tag */
        foreach ($tags as $key => $tag) {
            array_push($categories, [
                '_attributes' => [
                    'term' => $tag->name,
                    'label' => $tag->name,
                ],
            ]);
        }

        // $authors_xml = [];
        // foreach ($book->authors as $key => $author) {
        //     $authors_xml["__custom:author:{$key}"] = [
        //         'name' => $author->name,
        //     ];
        // }
        // $data = array_merge($base, $authors_xml);

        return [
            'title' => $book->title,
            'updated' => $date,
            'id' => $id,
            'content' => [
                '_attributes' => [
                    'type' => 'text/html',
                ],
                '_value' => $book->description,
            ],
            '__custom:link:1' => [
                '_attributes' => [
                    'href' => $book->show_opds_link,
                    'type' => 'application/atom+xml;profile=opds-catalog;kind=navigation',
                ],
            ],
            '__custom:link:2' => [
                '_attributes' => [
                    'href' => $book->cover_original,
                    'type' => 'image/png',
                    'rel' => 'http://opds-spec.org/image',
                ],
            ],
            '__custom:link:3' => [
                '_attributes' => [
                    'href' => $book->cover_thumbnail,
                    'type' => 'image/png',
                    'rel' => 'http://opds-spec.org/image/thumbnail',
                ],
            ],
            ...$this->formats($book),
            'category' => $categories,
            'author' => [
                'name' => $book->authors[0]->name,
                'uri' => $book->authors[0]->show_opds_link,
            ],
            'dcterms:issued' => $book->released_on,
            'published' => $book->released_on,
            'volume' => $book->volume,
            'dcterms:language' => $book->language->name,
        ];
    }

    public function formats(Book $book): array
    {
        $list = [];
        $i = 4;
        foreach (BookFormatEnum::toValues() as $format) {
            if ($book->files[$format]) {
                if (null !== $book->download_link[$format]) {
                    $list['__custom:link:4'] = [
                        '_attributes' => [
                            'href' => $book->download_link[$format],
                            'type' => 'application/epub+zip',
                            'rel' => 'http://opds-spec.org/acquisition',
                            'title' => 'EPUB',
                        ],
                    ];
                    ++$i;
                }
            }
        }

        return $list;
    }
}
