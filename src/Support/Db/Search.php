<?php

namespace Mmb\Support\Db;

use App\Models\User;
use Spatie\Searchable\Search as BaseSearch;
use Spatie\Searchable\SearchAspect;
use Spatie\Searchable\SearchResultCollection;

/**
 * @deprecated
 */
class Search extends BaseSearch
{

    public function searchPage(string $query, int $page, ?User $user = null, int $perPage = 15): SearchResultCollection
    {
        // $searchResults = new SearchResultCollection();
        //
        // $offset = $page * $perPage;
        // foreach($this->getSearchAspects() as $aspect)
        // {
        //     $aspect->limit($perPage)->offset($offset);
        //     $searchResults->addResults($aspect->getType(), $aspect->getResults($query, $user));
        // }
        // collect($this->getSearchAspects())
        //     ->each(function (SearchAspect $aspect) use ($query, $user, $searchResults) {
        //         $searchResults->addResults($aspect->getType(), $aspect->getResults($query, $user));
        //     });
        //
        // return $searchResults;
    }

}
